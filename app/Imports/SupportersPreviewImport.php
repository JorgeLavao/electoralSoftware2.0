<?php

namespace App\Imports;

use App\Models\ImportBatch;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Row;

class SupportersPreviewImport implements
    OnEachRow,
    WithHeadingRow,
    WithMultipleSheets,
    SkipsUnknownSheets,
    WithChunkReading
{
    public bool $missingUsuariosSheet = false;

    private int $batchId;
    private string $errorsCsvPath;
    private int $lastErrorsLimit;
    private string $docKey;
    private string $emailKey;

    private array $counts = ['valid' => 0, 'warning' => 0, 'invalid' => 0];
    private array $lastErrors = []; // últimos 20

    private $errorsHandle = null;
    private int $processedSinceFlush = 0;
    private int $flushEvery = 2000;

    public function __construct(
        int $batchId,
        string $errorsCsvPath,
        int $lastErrorsLimit = 20,
        string $docKey = '',
        string $emailKey = ''
    ) {
        $this->batchId = $batchId;
        $this->errorsCsvPath = $errorsCsvPath;
        $this->lastErrorsLimit = $lastErrorsLimit;

        $this->docKey = $docKey ?: "import:batch:{$batchId}:doc";
        $this->emailKey = $emailKey ?: "import:batch:{$batchId}:email";
    }

    public function sheets(): array
    {
        return ['Usuarios' => $this];
    }

    public function onUnknownSheet($sheetName)
    {
        $this->missingUsuariosSheet = true;
    }

    public function chunkSize(): int
    {
        return 2000;
    }

    public function onRow(Row $row)
    {
        $d = $this->cleanRow($row->toArray());

        // 1) Validación base (formato/campos)
        [$status, $messages] = $this->validateRow($d);

        // 2) Validación duplicados dentro del archivo (tipo+doc y email)
        [$status, $messages] = $this->applyDuplicateRules($d, $status, $messages);
        $this->counts[$status]++;
        if ($status === 'warning' || $status === 'invalid') {
            $issue = [
                'row' => $row->getIndex(),
                'tipo_de_documento' => $d['tipo_de_documento'] ?? '',
                'nro_documento' => (string)($d['numero_de_documento'] ?? ''),
                'estado' => $status,
                'mensaje' => $messages ? implode(' | ', $messages) : '—',
            ];
            $this->appendErrorToCsv($issue);
            $this->pushLastError($issue);
        }
        $this->processedSinceFlush++;
        if ($this->processedSinceFlush >= $this->flushEvery) {
            $this->flushProgressToDb();
        }
    }

    private function applyDuplicateRules(array $d, string $status, array $messages): array
    {
        $tipo = strtoupper(trim((string)($d['tipo_de_documento'] ?? '')));
        $doc  = trim((string)($d['numero_de_documento'] ?? ''));
        $email = mb_strtolower(trim((string)($d['correo_electronico'] ?? '')));
        // Duplicado Tipo+Documento
        if ($tipo !== '' && $doc !== '') {
            $value = $tipo . '|' . $doc;
            $added = Redis::sadd($this->docKey, $value);
            if ((int)$added === 0) {
                $messages[] = 'Documento duplicado (Tipo + Número ya existe en el archivo)';
                $status = 'invalid';
            }
        }
        // Duplicado Email
        if ($email !== '') {
            $added = Redis::sadd($this->emailKey, $email);
            if ((int)$added === 0) {
                $messages[] = 'Correo duplicado (ya existe en el archivo)';
                $status = 'invalid';
            }
        }
        return [$status, $messages];
    }

    private function pushLastError(array $issue): void
    {
        $this->lastErrors[] = $issue;
        if (count($this->lastErrors) > $this->lastErrorsLimit) {
            array_shift($this->lastErrors);
        }
    }

    private function appendErrorToCsv(array $issue): void
    {
        if ($this->errorsHandle === null) {
            Storage::disk('local')->makeDirectory(dirname($this->errorsCsvPath));
            $fullPath = Storage::disk('local')->path($this->errorsCsvPath);
            $isNew = !file_exists($fullPath);
            $this->errorsHandle = fopen($fullPath, 'a');
            if ($isNew) {
                fputcsv($this->errorsHandle, ['fila', 'tipo_documento', 'nro_documento', 'estado', 'mensaje']);
            }
        }
        fputcsv($this->errorsHandle, [
            $issue['row'] ?? null,
            $issue['tipo_de_documento'] ?? null,
            $issue['nro_documento'] ?? null,
            $issue['estado'] ?? null,
            $issue['mensaje'] ?? null,
        ]);
    }

    private function flushProgressToDb(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;
        $batch->processed_rows = (int)$batch->processed_rows + $this->processedSinceFlush;
        $batch->counts = $this->counts;
        $batch->last_errors = $this->lastErrors;
        $batch->save();
        $this->processedSinceFlush = 0;
    }

    public function __destruct()
    {
        $this->flushProgressToDb();
        if (is_resource($this->errorsHandle)) {
            fclose($this->errorsHandle);
        }
    }

    private function cleanRow(array $d): array
    {
        $fields = [
            'tipo_de_documento',
            'numero_de_documento',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'numero_de_celular',
            'correo_electronico',
        ];
        foreach ($fields as $f) {
            if (!isset($d[$f])) continue;
            if (is_string($d[$f])) {
                $d[$f] = trim(preg_replace('/\s+/', ' ', $d[$f]));
            }
        }
        if (isset($d['tipo_de_documento']) && is_string($d['tipo_de_documento'])) {
            $d['tipo_de_documento'] = strtoupper($d['tipo_de_documento']);
        }
        // email siempre en minúsculas (y ayuda duplicados)
        if (isset($d['correo_electronico']) && is_string($d['correo_electronico'])) {
            $d['correo_electronico'] = mb_strtolower(trim($d['correo_electronico']));
        }

        return $d;
    }

    private function validateRow(array $d): array
    {
        $errors = [];
        $warnings = [];

        // 1) Tipo de Documento
        $allowed = ['CC','TI','CE','PA','RC', 'NIT', 'PEP', 'PPT'];
        $tipo = strtoupper(trim((string)($d['tipo_de_documento'] ?? '')));
        if ($tipo === '') $errors[] = 'Tipo de documento es obligatorio';
        elseif (!in_array($tipo, $allowed, true)) $errors[] = 'Tipo de documento no válido';

        // 2) Número de Documento (mín 3, sin puntos ni espacios)
        $doc = trim((string)($d['numero_de_documento'] ?? ''));
        if ($doc === '') $errors[] = 'Número de documento es obligatorio';
        elseif (mb_strlen($doc) < 3) $errors[] = 'Documento debe tener mínimo 3 caracteres';
        elseif (preg_match('/[ .]/', $doc)) $errors[] = 'Documento no debe contener puntos ni espacios';

        // 3) Nombres y Apellidos
        $nameRegex = '/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/u';

        $pnom = trim((string)($d['primer_nombre'] ?? ''));
        if ($pnom === '') $errors[] = 'Primer nombre es obligatorio';
        elseif (mb_strlen($pnom) > 50) $errors[] = 'Primer nombre máximo 50 caracteres';
        elseif (!preg_match($nameRegex, $pnom)) $errors[] = 'Primer nombre solo permite letras';

        $snom = trim((string)($d['segundo_nombre'] ?? ''));
        if ($snom !== '') {
            if (mb_strlen($snom) > 50) $errors[] = 'Segundo nombre máximo 50 caracteres';
            elseif (!preg_match($nameRegex, $snom)) $errors[] = 'Segundo nombre solo permite letras';
        }

        $pape = trim((string)($d['primer_apellido'] ?? ''));
        if ($pape === '') $errors[] = 'Primer apellido es obligatorio';
        elseif (mb_strlen($pape) > 50) $errors[] = 'Primer apellido máximo 50 caracteres';
        elseif (!preg_match($nameRegex, $pape)) $errors[] = 'Primer apellido solo permite letras';

        $sape = trim((string)($d['segundo_apellido'] ?? ''));
        if ($sape !== '') {
            if (mb_strlen($sape) > 50) $errors[] = 'Segundo apellido máximo 50 caracteres';
            elseif (!preg_match($nameRegex, $sape)) $errors[] = 'Segundo apellido solo permite letras';
        }
        // 4) Celular (warning si != 10 dígitos)
        $telRaw = (string)($d['numero_de_celular'] ?? '');
        $tel = preg_replace('/\D+/', '', $telRaw);

        if (trim($telRaw) === '') $errors[] = 'Número de celular es obligatorio';
        elseif (!preg_match('/^\d+$/', $tel)) $errors[] = 'Celular debe ser numérico';
        elseif (strlen($tel) !== 10) $errors[] = 'Teléfono no es válido (debe tener 10 dígitos)';

        // 5) Email
        $email = mb_strtolower(trim((string)($d['correo_electronico'] ?? '')));
        if ($email === '') $errors[] = 'Correo electrónico es obligatorio';
        else {
            if (mb_strlen($email) > 100) $errors[] = 'Correo máximo 100 caracteres';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo no válido';
        }

        if (!empty($errors)) return ['invalid', $errors];
        if (!empty($warnings)) return ['warning', $warnings];
        return ['valid', []];
    }
}
