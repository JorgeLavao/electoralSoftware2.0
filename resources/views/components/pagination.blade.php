@props([
    'paginator',
    'livewire' => false,
])

@if ($paginator && $paginator->hasPages())
<div class="mt-6">
    {{ $paginator->links($livewire ? 'vendor.livewire.tailwind' : 'vendor.pagination.tailwind') }}
</div>
@endif
