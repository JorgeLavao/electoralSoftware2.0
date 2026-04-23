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
            <label for="title">Título de la Noticia<span class="text-red-500">*</span></label>
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
            <label for="published_at">Fecha de Publicación</label>
            <input type="date" id="published_at" wire:model="published_at">
            @error('published_at')
            <x-toast.error-toast :message="$message" />
            @enderror
        </div>

        <a href="{{ route('dashboard') }}" class="button btn-secondary">
            Volver
        </a>




        <button type="submit" class="btn-primary">
            {{ $isEdit ? 'Actualizar noticia' : 'Guardar noticia' }}
            <x-icons.send-fill />
        </button>
        </div>
        </div>
    </form>
    </div>
    </div>
</section>