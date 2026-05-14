<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Noticias</a> / {{ $isEdit ? 'Editar noticia' : 'Crear noticia' }}
    </div>

    <div class="">
        @if (session()->has('success'))
        <x-toast.success-toast :message="session('success')" />
        @endif
    </div>

    <form wire:submit.prevent="save" class="space-y-6" novalidate>
        <div class="group-form-v">
            <label for="title">Titulo de la Noticia<span class="text-red-500">*</span></label>
            <input
                type="text"
                id="title"
                wire:model="title"
                placeholder=""
                required>
            @error('title')
            <x-toast.error-toast :message="$message" />
            @enderror
        </div>

        <div class="group-form-v">
            <label for="description">Contenido de la Noticia<span class="text-red-500">*</span></label>
            <textarea
                id="description"
                wire:model="description"
                rows="8"
                placeholder=""
                required></textarea>
            @error('description')
            <x-toast.error-toast :message="$message" />
            @enderror
        </div>

        <div class="group-form-v max-w-xs">
            <label for="published_at">Fecha de Publicacion</label>
            <input type="date" id="published_at" wire:model="published_at">
            @error('published_at')
            <x-toast.error-toast :message="$message" />
            @enderror
        </div>

        <div class="group-form-v">
            <label for="image">Imagen de la Noticia</label>
            <input
                type="file"
                id="image"
                wire:model="image"
                accept="image/jpeg,image/png,image/webp,image/gif">
            <p class="text-sm text-gray-500">Solo imagenes JPG, PNG, WEBP o GIF. Tamano maximo: 5 MB.</p>

            @error('image')
            <x-toast.error-toast :message="$message" />
            @enderror

            <div wire:loading wire:target="image" class="text-sm text-gray-500">
                Cargando imagen...
            </div>

            @if ($image && ! $errors->has('image'))
                <img
                    src="{{ $image->temporaryUrl() }}"
                    alt="Vista previa de la imagen"
                    class="mt-3 h-40 w-full max-w-sm rounded-xl object-cover">
            @elseif ($current_image_path)
                <img
                    src="{{ asset('storage/' . $current_image_path) }}"
                    alt="Imagen actual de la noticia"
                    class="mt-3 h-40 w-full max-w-sm rounded-xl object-cover">
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="button btn-secondary">
                Volver
            </a>

            <button type="submit" class="btn-primary ml-auto">
                {{ $isEdit ? 'Actualizar noticia' : 'Guardar noticia' }}
                <x-icons.send-fill />
            </button>
        </div>
    </form>
</section>
