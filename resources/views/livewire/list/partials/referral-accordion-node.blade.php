@php
    $children = $node['children'] ?? [];
    $hasChildren = count($children) > 0;
    $directCount = (int) ($node['direct_count'] ?? 0);
    $level = (int) ($node['level'] ?? 0);
    $childrenPage = max(1, (int) ($node['children_page'] ?? 1));
    $childrenPerPage = max(1, (int) ($node['children_per_page'] ?? 10));
    $childrenLastPage = max(1, (int) ceil(max($directCount, 1) / $childrenPerPage));
    $childrenFrom = $directCount > 0 ? (($childrenPage - 1) * $childrenPerPage) + 1 : 0;
    $childrenTo = min($childrenPage * $childrenPerPage, $directCount);
    $role = $node['role'] ?? 'Simpatizante';
    $roleColor = $node['role_color'] ?? '#64748b';
    $chevronClass = $level > 0 && $hasChildren ? ' rotate-90' : '';
@endphp

<div class="referral-node max-w-full" x-data="{ open: true }">
    <button
        type="button"
        class="group flex w-full items-center gap-3 rounded-md border border-slate-200 bg-white px-3 py-3 text-left text-sm shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary/20"
        style="border-left: 5px solid {{ $roleColor }};"
        @if ($level === 0)
            @click="open = ! open"
        @elseif ($directCount > 0)
            wire:click.stop="toggleReferralBranch({{ (int) $node['id'] }})"
        @endif
        aria-label="Abrir o cerrar referidos de {{ $node['name'] }}">
        @if ($directCount > 0)
            <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-500 transition group-hover:bg-white{{ $chevronClass }}"
                @if ($level === 0)
                    :class="{ 'rotate-90': open }"
                @endif>
                <x-icons.right-fill />
            </span>
        @else
            <span class="h-8 w-8 shrink-0"></span>
        @endif

        <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $roleColor }};"></span>

        <div class="min-w-0 flex-1">
            <span class="block truncate font-medium text-slate-800">
                {{ $node['name'] }}
            </span>
        </div>

        <span class="hidden shrink-0 rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-600 sm:inline-flex">
            {{ $role }}
        </span>
    </button>

    @if ($hasChildren)
        <div
            class="mt-2 space-y-2 border-l border-slate-200 pl-3 sm:pl-5"
            @if ($level === 0) x-show="open" @endif>
            @if ($directCount > $childrenPerPage)
                <div class="flex w-full flex-wrap items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500">
                    <button
                        type="button"
                        class="rounded-md border border-slate-200 bg-white px-2 py-1 font-semibold text-slate-600 disabled:opacity-40"
                        wire:click.stop="setReferralBranchPage({{ (int) $node['id'] }}, {{ max(1, $childrenPage - 1) }})"
                        @disabled($childrenPage <= 1)>
                        Anterior
                    </button>
                    <span>{{ $childrenFrom }}-{{ $childrenTo }} de {{ $directCount }}</span>
                    <button
                        type="button"
                        class="rounded-md border border-slate-200 bg-white px-2 py-1 font-semibold text-slate-600 disabled:opacity-40"
                        wire:click.stop="setReferralBranchPage({{ (int) $node['id'] }}, {{ min($childrenLastPage, $childrenPage + 1) }})"
                        @disabled($childrenPage >= $childrenLastPage)>
                        Siguiente
                    </button>
                </div>
            @endif

            @foreach ($children as $child)
                @include('livewire.list.partials.referral-accordion-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
