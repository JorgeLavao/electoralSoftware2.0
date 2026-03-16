<?php

namespace App\Imports;

use Illuminate\Support\Facades\Storage;

class SupportersPreviewImport extends AbstractSupportersImport
{
    private string $errorsCsvPath;
    private int $lastErrorsLimit;

    private $errorsHandle = null;

    public function __construct(
        int $batchId,
        string $errorsCsvPath,
        int $lastErrorsLimit = 20,
        string $docKey = '',
        string $emailKey = ''
    ) {
        parent::__construct($batchId, $docKey, $emailKey);

        $this->errorsCsvPath = $errorsCsvPath;
        $this->lastErrorsLimit = $lastErrorsLimit;
    }

    protected function handleRowResult(
        int $rowIndex,
        array $data,
        string $status,
        array $messages
    ): void {
        if ($status !== 'warning' && $status !== 'invalid') {
            return;
        }

        $issue = [
            'row' => $rowIndex,
            'tipo_de_documento' => $data['tipo_de_documento'] ?? '',
            'nro_documento' => (string)($data['numero_de_documento'] ?? ''),
            'estado' => $status,
            'mensaje' => $messages ? implode(' | ', $messages) : '—',
        ];

        $this->appendErrorToCsv($issue);
        $this->pushLastError($issue);
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

    public function __destruct()
    {
        parent::__destruct();

        if (is_resource($this->errorsHandle)) {
            fclose($this->errorsHandle);
        }
    }
}
