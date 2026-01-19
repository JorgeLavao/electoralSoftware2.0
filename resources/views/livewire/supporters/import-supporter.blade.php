<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('supporter.index', session('current_campaign')->code) }}" wire:navigate>Simpatizantes</a>
        / Importar
    </div>
    <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
        <h4></h4>
        <a href="http://localhost/download/plantilla-simpatizantes" class="button btn-secundary text-primary border-primary">
            <x-icons.file-download-line/> Plantilla
        </a>
    </div>
    {{-- contenido --}}
    <div class="container-v mt-4">
        @if($step === 'upload')
            <div class="steps">
                <div class="number">
                    <h4>1</h4>
                </div>
                <div>
                    <p class="base-bold text-grey-400">Descarga la plantilla</p>
                    <span>Asegúrate de incluir las columnas obligatorias: Tipo de Documento, Nro. Documento, Primer Nombre, Primer Apellido, Nro. de contacto, Email.</span>
                </div>
            </div>
            <div class="steps">
                <div class="number">
                    <h4>2</h4>
                </div>
                <div>
                    <p class="base-bold text-grey-400">Sube tu archivo</p>
                    <span>Formatos soportados: .xlsx, .xls, .csv</span>
                </div>
            </div>
            <div x-data="{ isDropping:false, isUploading:false, progress:0 }"
                x-on:livewire-upload-start="isUploading = true; progress = 0"
                x-on:livewire-upload-finish="isUploading = false; progress = 100"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress" class="mt-5">
                <input type="file" class="hidden" x-ref="fileInput" wire:model="file" accept=".xlsx,.xls,.csv"/>
                {{-- drag and drop --}}
                <div class="rounded-2xl border-2 border-dashed p-8 cursor-pointer transition bg-white"
                    :class="isDropping ? 'border-primary bg-indigo-50' : 'border-gray-300 hover:border-gray-400'"
                    @click="$refs.fileInput.click()"
                    @dragover.prevent="isDropping = true"
                    @dragleave.prevent="isDropping = false"
                    @drop.prevent="
                        isDropping = false;
                        if ($event.dataTransfer.files.length) {
                            $refs.fileInput.files = $event.dataTransfer.files;
                            $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <div class="text-4xl">📄</div>
                        <div class="font-semibold text-gray-800">
                            Arrastra y suelta tu archivo aquí
                        </div>
                        <div class="text-sm text-gray-600">
                            o haz clic para seleccionarlo
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            Formatos: .xlsx, .xls, .csv — Máx 10MB
                        </div>
                    </div>
                    {{-- progress bar --}}
                    <div x-show="isUploading" x-cloak class="mt-6 w-full">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Subiendo archivo...</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                            <div class="h-2 bg-primary transition-all rounded-full" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>
                <div>
                    @error('file')
                        <x-toast.error-toast :message="$message"/>
                    @enderror
                </div>
            </div>
        @endif
        @if($step === 'processing')
            <h4>Procesando archivo</h4>
            <div wire:poll.1500ms="refreshBatch" class="container-v">
                <div class="flex justify-between text-md text-gray-600 mb-1">
                    <span>Validando registros…</span>
                    <span>{{ $progress }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-2 bg-primary transition-all rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <div class="container-v">
                    <div>
                        Procesadas: <b>{{ number_format($processedRows) }}</b>
                        @if($totalRows)
                            / <b>{{ number_format($totalRows) }}</b>
                        @endif
                    </div>

                    <div class="flex gap-3 flex-wrap">
                        <span class="text-valid"><b>{{ $counts['valid'] ?? 0 }}</b> válidos</span>
                        <span class="text-amber-400"><b>{{ $counts['warning'] ?? 0 }}</b> advertencias</span>
                        <span class="text-error"><b>{{ $counts['invalid'] ?? 0 }}</b> inválidos</span>
                    </div>

                    <div class="text-xs text-gray-500">
                        Mostrando en vivo los últimos 20 errores/advertencias (si aparecen).
                    </div>
                </div>
                @if(count($previewRows))
                    <div class="bg-white container-v my-4">
                        <table class="responsive w-full">
                            <thead>
                                <tr>
                                    <th>Fila</th>
                                    <th>Tipo Documento</th>
                                    <th>Nro. Documento</th>
                                    <th>Estado</th>
                                    <th class="md:w-[427px]">Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewRows as $r)
                                    @php
                                        $estadoLabel = $r['estado'] === 'warning' ? 'Advertencia' : 'Inválido';
                                        $estadoClass = $r['estado'] === 'warning' ? 'text-yellow-700' : 'text-red-700';
                                        $rowClass = $r['estado'] === 'invalid' ? 'bg-red-50' : 'bg-yellow-50';
                                    @endphp
                                    <tr class="border-t {{ $rowClass }}">
                                        <td>{{ $r['row'] ?? '-' }}</td>
                                        <td>{{ $r['tipo_de_documento'] ?? '-' }}</td>
                                        <td>{{ $r['nro_documento'] ?? '-' }}</td>
                                        <td class="p-3 font-semibold {{ $estadoClass }}">{{ $estadoLabel }}</td>
                                        <td class="p-3 {{ $estadoClass }}">{{ $r['mensaje'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <div class="justify-between flex w-full">
                    <button type="button" class="btn-secondary" wire:click="back">
                        <x-icons.close/> Cancelar
                    </button>
                </div>
            </div>
        @endif
        @if($step === 'preview')
            <div class="container-v bg-white">
                <div class="flex flex-col gap-2 p-4">
                    <div class="text-md text-gray-700">
                        Procesadas: <b>{{ number_format($processedRows) }}</b>
                        @if($totalRows)
                            / <b>{{ number_format($totalRows) }}</b>
                        @endif
                    </div>
                    <div class="flex gap-4 flex-wrap text-sm">
                        <span class="text-green-700">
                            Correctos: <b>{{ number_format($counts['valid'] ?? 0) }}</b>
                        </span>
                        <span class="text-yellow-700">
                            Advertencias: <b>{{ number_format($counts['warning'] ?? 0) }}</b>
                        </span>
                        <span class="text-red-700">
                            Errores: <b>{{ number_format($counts['invalid'] ?? 0) }}</b>
                        </span>
                    </div>
                    <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center mt-4">
                        <button type="button" class="btn-secondary" wire:click="back">
                            <x-icons.close/> Cancelar
                        </button>
                        <div class="flex flex-col gap-3 w-full md:flex-row md:w-auto">
                            @if(($counts['warning'] ?? 0) || ($counts['invalid'] ?? 0))
                                <button type="button" class="btn-secundary text-primary border-primary" wire:click="downloadErrors">
                                    <x-icons.file-download-line/> Descargar errores (.csv)
                                </button>
                            @endif
                            @if(number_format($counts['valid']) > 0)
                                <button class="btn-secundary text-valid border-valid" type="button">
                                    Importar {{ number_format($counts['valid']) }} Simpatizantes
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        En la tabla se muestran únicamente los <b>últimos 20 errores/advertencias</b> detectados.
                        Para ver el listado completo, usa “Descargar listado total de errores”.
                    </div>
                </div>
                 <div class="mt-4">
                    <table class="responsive w-full">
                        <thead>
                            <tr>
                                <th>Fila</th>
                                <th>Tipo Documento</th>
                                <th>Nro. Documento</th>
                                <th>Estado</th>
                                <th class="md:w-[427px]">Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!count($previewRows))
                                <tr class="border-t">
                                    <td class="p-3 text-center text-sm text-gray-500" colspan="5">
                                        No se encontraron errores ni advertencias 🎉
                                    </td>
                                </tr>
                            @else
                                @foreach($previewRows as $r)
                                    @php
                                        $estadoLabel = ($r['estado'] ?? '') === 'warning' ? 'Advertencia' : 'Error';
                                        $estadoClass = ($r['estado'] ?? '') === 'warning' ? 'text-yellow-700' : 'text-red-700';
                                        $rowClass = ($r['estado'] ?? '') === 'warning' ? 'bg-yellow-50' : 'bg-red-50';
                                    @endphp
                                    <tr class="border-t {{ $rowClass }}">
                                        <td class="p-3">{{ $r['row'] ?? '-' }}</td>
                                        <td class="p-3">{{ $r['tipo_de_documento'] ?? '-' }}</td>
                                        <td class="p-3">{{ $r['nro_documento'] ?? '-' }}</td>
                                        <td class="p-3 font-semibold {{ $estadoClass }}">{{ $estadoLabel }}</td>
                                        <td class="p-3 {{ $estadoClass }}">{{ $r['mensaje'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</section>
