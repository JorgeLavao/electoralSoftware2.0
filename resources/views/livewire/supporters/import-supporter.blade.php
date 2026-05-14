<section class="dashboard__main__section">
    {{-- Navegación interna (Breadcrumbs) con soporte para SPA (wire:navigate) --}}
    <div class="breadcrumbs">
        <a href="{{ route('supporter.index', session('current_campaign')->code) }}" wire:navigate>Simpatizantes</a>
        / Importar
    </div>

    <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
        <h4></h4>
        {{-- Enlace directo para que el usuario no cometa errores de formato --}}
        <a href="{{ route('download.template.supporter') }}" class="button btn-secundary text-primary border-primary">
            <x-icons.file-download-line/> Plantilla
        </a>
    </div>

    <div class="container-v mt-4">
        
        {{-- PASO 1: CARGA DE ARCHIVO (Upload) --}}
        @if($step === 'upload')
            {{-- Instrucciones visuales para el usuario --}}
            <div class="steps">
                <div class="number"><h4>1</h4></div>
                <div>
                    <p class="base-bold text-grey-400">Descarga la plantilla</p>
                    <span>Columnas obligatorias: Tipo de Documento, Nro. Documento, Nombre, etc.</span>
                </div>
            </div>

            {{-- Zona de Drag & Drop gestionada con Alpine.js y Livewire --}}
            <div x-data="{ isDropping:false, isUploading:false, progress:0 }"
                x-on:livewire-upload-start="isUploading = true; progress = 0"
                x-on:livewire-upload-finish="isUploading = false; progress = 100"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress" class="mt-5">
                
                {{-- Input oculto pero vinculado al modelo 'file' de Livewire --}}
                <input type="file" class="hidden" x-ref="fileInput" wire:model="file" accept=".xlsx,.xls,.csv"/>

                {{-- Área interactiva de soltar archivo --}}
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
                        <div class="font-semibold text-gray-800">Arrastra y suelta tu archivo aquí</div>
                        <div class="text-xs text-gray-500 mt-2">Formatos: .xlsx, .xls, .csv — Máx 10MB</div>
                    </div>

                    {{-- Barra de progreso de subida (Visualización en tiempo real) --}}
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
                
                {{-- Manejo de errores de validación del archivo --}}
                @error('file')
                    <x-toast.error-toast :message="$message"/>
                @enderror
            </div>
        @endif

        {{-- PASO 2: PROCESAMIENTO (Batch Processing) --}}
        @if($step === 'processing')
            <h4>Procesando archivo</h4>
            {{-- wire:poll: Consulta al servidor cada 1.5s para actualizar el progreso del Batch --}}
            <div wire:poll.1500ms="refreshBatch" class="container-v">
                <div class="flex justify-between text-md text-gray-600 mb-1">
                    <span>Validando registros…</span>
                    <span>{{ $progress }}%</span>
                </div>
                {{-- Barra de progreso del procesamiento interno (Laravel Jobs/Batch) --}}
                <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-2 bg-primary transition-all rounded-full" style="width: {{ $progress }}%"></div>
                </div>

                <div class="flex gap-3 flex-wrap">
                    <span class="text-valid"><b>{{ $counts['valid'] ?? 0 }}</b> válidos</span>
                    <span class="text-amber-400"><b>{{ $counts['warning'] ?? 0 }}</b> advertencias</span>
                    <span class="text-error"><b>{{ $counts['invalid'] ?? 0 }}</b> inválidos</span>
                </div>
                
                {{-- Botón de emergencia para detener o volver --}}
                <button type="button" class="btn-secondary mt-4" wire:click="back">
                    <x-icons.close/> Cancelar
                </button>
            </div>
        @endif

        {{-- PASO 3: PREVISUALIZACIÓN Y CONFIRMACIÓN --}}
        @if($step === 'preview')
            <div class="container-v bg-white">
                {{-- Resumen final antes de insertar en BD --}}
                <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center mt-4">
                    <button type="button" class="btn-secondary" wire:click="back">
                        <x-icons.close/> Cancelar
                    </button>
                    
                    <div class="flex flex-col gap-3 w-full md:flex-row md:w-auto">
                        {{-- Opción de descargar los errores detectados para corregirlos --}}
                        @if(($counts['warning'] ?? 0) || ($counts['invalid'] ?? 0))
                            <button type="button" class="btn-secundary text-primary border-primary" wire:click="downloadErrors">
                                <x-icons.file-download-line/> Descargar errores (.csv)
                            </button>
                        @endif

                        {{-- ACCIÓN FINAL: Importar solo los registros que pasaron las reglas de validación --}}
                        @if(($counts['valid'] ?? 0) > 0)
                            <button class="btn-secundary text-valid border-valid" type="button"
                                wire:click="importValidSupporters" wire:loading.attr="disabled">
                                <span wire:loading.remove>Importar {{ number_format($counts['valid']) }} Simpatizantes</span>
                                <span wire:loading>Importando...</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Tabla de errores: Solo muestra los últimos 20 para no saturar el DOM --}}
                <div class="mt-4">
                    <table class="responsive w-full">
                        {{-- ... (Cabecera de tabla) ... --}}
                        <tbody>
                            @forelse($previewRows as $r)
                                @php
                                    $isWarning = ($r['estado'] ?? '') === 'warning';
                                    $rowClass = $isWarning ? 'bg-yellow-50' : 'bg-red-50';
                                    $textClass = $isWarning ? 'text-yellow-700' : 'text-red-700';
                                @endphp
                                <tr class="border-t {{ $rowClass }}">
                                    <td>{{ $r['row'] }}</td>
                                    <td>{{ $r['nro_documento'] }}</td>
                                    <td class="font-semibold {{ $textClass }}">{{ $isWarning ? 'Advertencia' : 'Error' }}</td>
                                    <td class="{{ $textClass }}">{{ $r['mensaje'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No se encontraron errores 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</section>
