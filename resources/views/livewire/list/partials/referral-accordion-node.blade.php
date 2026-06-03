@php
    $children = $node['children'] ?? [];
    $hasChildren = count($children) > 0;
    $directCount = (int) ($node['direct_count'] ?? 0);
    $level = (int) ($node['level'] ?? 0);
    $levelColor = $node['level_color'] ?? '#64748b';
    $roleColor = $node['role_color'] ?? '#64748b';
    $size = max(18, 30 - min($level, 5));
    $childrenCount = count($children);
    $childrenPage = max(1, (int) ($node['children_page'] ?? 1));
    $childrenPerPage = max(1, (int) ($node['children_per_page'] ?? 10));
    $childrenLastPage = max(1, (int) ceil(max($directCount, 1) / $childrenPerPage));
    $childrenFrom = $directCount > 0 ? (($childrenPage - 1) * $childrenPerPage) + 1 : 0;
    $childrenTo = min($childrenPage * $childrenPerPage, $directCount);
    $childrenGridStyle = 'display:grid; grid-template-columns:minmax(0, max-content); gap:.75rem; align-items:start;';
@endphp

<div class="referral-node relative max-w-full">
    @if ($level > 0)
        <div class="absolute -left-5 top-5 h-px w-5 bg-slate-300 sm:-left-7 sm:w-7"></div>
    @endif

    <details
        class="group max-w-full"
        @if ($level === 0 || $hasChildren) open @endif
        x-data="{ showInfo: false }"
        @toggle="
            if ($event.target.open) {
                const node = $event.target.closest('.referral-node');
                const siblings = node?.closest('[data-referral-page-item]')?.parentElement ?? node?.parentElement;
                siblings?.querySelectorAll(':scope > .referral-node > details, :scope > [data-referral-page-item] > .referral-node > details').forEach((details) => {
                    if (details !== $event.target) {
                        details.open = false;
                    }
                });
            }
        ">
        <summary
            class="inline-flex cursor-pointer list-none items-center gap-2 rounded-full px-1 py-1 hover:bg-white/70"
            @click="@if ($level === 0) $event.preventDefault(); @endif showInfo = ! showInfo"
            @if ($level > 0 && $directCount > 0)
                wire:click.stop="toggleReferralBranch({{ (int) $node['id'] }})"
            @endif>
            <span class="relative inline-flex">
                <span
                    class="peer inline-flex items-center justify-center rounded-full border-4 shadow-sm transition hover:scale-110 hover:shadow-md"
                    style="width: {{ $size }}px; height: {{ $size }}px; background-color: {{ $roleColor }}; border-color: {{ $levelColor }};">
                    @if ($directCount > 0)
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                    @endif
                </span>

                @if ($directCount > 0)
                    <span class="absolute -right-2 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-slate-700 ring-1 ring-slate-300">
                        {{ $directCount }}
                    </span>
                @endif

            </span>

            @if ($directCount > 0)
                <span class="text-xs font-semibold text-slate-500">
                    <span class="group-open:hidden">+</span>
                    <span class="hidden group-open:inline">-</span>
                </span>
            @endif
        </summary>

        <div
            x-cloak
            x-show="showInfo"
            class="mt-2 max-w-[calc(100vw-3rem)] rounded-lg border border-slate-200 bg-white p-3 text-left text-xs text-slate-600 shadow-sm sm:max-w-sm">
            <strong class="block text-sm text-slate-900">{{ $node['name'] }}</strong>
            <span class="mt-1 block">
                @if (! empty($node['parent_name']))
                    {{ $node['parent_name'] }} refirio a {{ $node['name'] }}
                @else
                    Persona raiz de la busqueda
                @endif
            </span>
            <span class="mt-2 block">Rol: <strong>{{ $node['role'] ?? 'Simpatizante' }}</strong></span>
            <span class="block">Nivel: {{ $level }}</span>
            <span class="block">Referidos directos: {{ $directCount }}</span>
            <span class="block">Documento: {{ $node['document'] }}</span>
            <span class="block">Celular: {{ $node['phone'] }}</span>
            <span class="block">Ingreso: {{ $node['joined_at'] }}</span>
        </div>

        @if ($hasChildren)
            <div class="relative ml-3 mt-3 max-w-full overflow-x-auto border-l-2 border-slate-300 pl-5 pb-2 sm:ml-4 sm:pl-7">
                @if ($directCount > $childrenPerPage)
                    <div class="mb-3 flex min-w-max items-center gap-2 text-xs text-slate-500">
                        <button
                            type="button"
                            class="rounded-md border border-slate-200 bg-white px-2 py-1 font-semibold text-slate-600 disabled:opacity-40"
                            wire:click.stop="setReferralBranchPage({{ (int) $node['id'] }}, {{ max(1, $childrenPage - 1) }})"
                            @disabled($childrenPage <= 1)>
                            Anterior
                        </button>
                        <span>
                            {{ $childrenFrom }}-{{ $childrenTo }} de {{ $directCount }}
                        </span>
                        <button
                            type="button"
                            class="rounded-md border border-slate-200 bg-white px-2 py-1 font-semibold text-slate-600 disabled:opacity-40"
                            wire:click.stop="setReferralBranchPage({{ (int) $node['id'] }}, {{ min($childrenLastPage, $childrenPage + 1) }})"
                            @disabled($childrenPage >= $childrenLastPage)>
                            Siguiente
                        </button>
                    </div>
                @endif

                <div class="referral-children w-max max-w-full pr-2" style="{{ $childrenGridStyle }}">
                    @foreach ($children as $child)
                        <div data-referral-page-item>
                            @include('livewire.list.partials.referral-accordion-node', ['node' => $child])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </details>
</div>
