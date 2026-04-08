<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="">Noticias</a> 
    </div>

    <div class="bg-midnight text-tahiti">

    <div class="container-v mb-4">
        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')"/>
        @endif
    </div>

    <div class="container-v mb-8">
        <div class="mb-5">
            <h2 class="text-xl font-semibold text-gray-00">
                {{ $isEdit ? 'Editar noticia' : 'Crear nueva noticia' }}
            </h2>
            <p class="text-sm text-gray-400 mt-1">
                Completa la información y guarda los cambios
            </p>
        </div>

        <form wire:submit.prevent="save" class="space-y-5">
            <div class="group-form-v">
                <label for="title">Título de la Noticia<span class="text-red-500">*</span></label>
                <input type="text" id="title" wire:model="title" 
                    placeholder="Digite el título" required>
                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="group-form-v">
                <label for="description">Descripción<span class="text-red-500">*</span></label>
                <textarea id="description" wire:model="description" rows="4" 
                    placeholder="Escriba el contenido de la noticia" required></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="group-form-v">
                <label for="published_at">Fecha de publicación</label>
                <input type="date" id="published_at" wire:model="published_at">
                @error('published_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 mt-4">
                @if($isEdit)
                    <button type="button" wire:click="resetFields" class="btn-secondary">
                        Cancelar
                    </button>
                @endif

                <button type="submit" class="btn-primary">
                    {{ $isEdit ? 'Actualizar noticia' : 'Guardar noticia' }}
                    <x-icons.send-fill/>
                </button>
            </div>
        </form>
    </div>
</div>

    <hr class="my-6">

    <div class="space-y-6">
    
    <!-- Título -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-pink-200">
            {{ $isEdit ? 'Editar noticia' : 'Noticias Publicadas' }}
        </h2>
    </div>

    <!-- Lista -->
    @forelse($news as $item)
        <div class="bg-white p-5 rounded-lg">

            <!-- Contenido -->
            <div>
                <h3 class="text-lg font-semibold text-[#f34e64]">
                    {{ $item->title }}
                </h3>

                <p class="text-gray-600 mt-2 leading-relaxed text-sm">
                    {{ $item->description }}
                </p>

                <div class="mt-4">
                    <span class="text-xs text-gray-400">
                        Publicado el {{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <!-- BOTONES ABAJO -->
            <div class="flex justify-end gap-2 mt-5">
                <button 
    wire:click="edit({{ $item->id }})"
    class=" btn-primary px-3 py-1.5 text-sm rounded-lg bg-primary text-white border-red-200 hover:text-white transition flex items-center gap-1"
>
    <!-- Ícono lápiz -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 14.828a4 4 0 01-1.414.943l-3.4 1.133 1.133-3.4A4 4 0 019 11z" />
    </svg>
    Editar
</button>

<button 
    wire:click="delete({{ $item->id }})"
    class="px-3 py-1.5 text-sm rounded-lg bg-primary text-white border-red-200 hover:text-white transition flex items-center gap-1"
>
    <!-- Ícono basura -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m4-6v6M5 7h14l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7z" />
    </svg>
    Eliminar
</button>
            </div>

        </div>
    @empty
        <div class="text-center text-gray-400 py-12 border border-dashed rounded-xl">
            No hay noticias registradas
        </div>
    @endforelse

</div>


</section>