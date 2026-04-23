<x-layouts.app :title="isset($news) ? __('Editar Noticia') : __('Crear Noticia')">
    @isset($news)
        <livewire:news-manager :news="$news" />
    @else
        <livewire:news-manager />
    @endisset
</x-layouts.app>
