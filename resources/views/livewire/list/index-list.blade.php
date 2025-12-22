<section class="dashboard__main__section">
   <div class="breadcrumbs">
        Listados
    </div>
    <article class="dashboard__main__section__article mb-24">
        @if (session()->has('success'))
            <div>
                <x-toast.success-toast :message="session('success')"/>
            </div>
        @endif
        <div class="relative">
            <div wire:loading  class="absolute inset-0 z-20 cursor-progress"></div>
            <div class="flex justify-end md:mb-4">
                <a href="{{ route('list.create', session('current_campaign')->code) }}" class="button btn-primary" wire:navigate> <x-icons.add-fill/> Agregar Listado </a>
            </div>
            <div class="bg-gray-50 container-v p-4 !rounded-lg">
                <h4>Buscar</h4>
                <div class="grop-columns-3 container-v">
                    <div class="group-form-v">
                        <label for="name">Por Nombre</label>
                        <input type="text" id="name" wire:model='searchName' placeholder="Digite el Nombre a Buscar" required>
                    </div>
                    <div class="group-form-v">
                        <label for="">Desde</label>
                        <input type="text" id="start_date" wire:model='start_date' placeholder="Seleccione la fecha de inicio" required x-data
                            x-ref="startDate" x-init="
                                $nextTick(() => {
                                    flatpickr($refs.startDate, {
                                    dateFormat: 'Y-m-d',
                                    locale: 'es',
                                    maxDate: 'today',
                                    onChange: function(selectedDates, dateStr, instance) {
                                        $wire.start_date = dateStr;
                                        const endDate = document.getElementById('end_date');
                                        if (endDate && endDate._flatpickr) {
                                            endDate._flatpickr.set('minDate', dateStr);
                                        }
                                    }
                                    });
                            })">
                    </div>
                    <div class="group-form-v">
                        <label for="">Hasta</label>
                        <input type="text" id="end_date"wire:model='end_date' placeholder="Seleccione la fecha de finalización" required x-data
                            x-ref="endDate" x-init="
                                $nextTick(() => {
                                    flatpickr($refs.endDate, {
                                        dateFormat: 'Y-m-d',
                                        minDate: $wire.start_date || '',
                                        maxDate: 'today',
                                        locale: 'es',
                                        onChange: function(selectedDates, dateStr, instance) {
                                            $wire.end_date = dateStr;
                                        }
                                    });
                                })">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="text-primary border-primary" wire:click="$refresh"> <x-icons.search/>Buscar</button>
                </div>
            </div>
            {{-- lists --}}
            <ul class="list-vertical wrap-primary">
                @foreach ($lists as $list)
                    <li class="!overflow-visible">
                        <div class="grid-9-3">
                            <div class="container-v">
                                <a href="{{ route('list.edit', [$campaign->code, $list->id]) }}">
                                    <h3>{{ $list->created_at }} - {{ $list->name }}</h3>
                                </a>
                            </div>
                            <div class="items-center flex !justify-end gap-2">
                                <div class="tag">{{ $list->foreign_users()->count() }} integrantes</div>
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" type="button" class="clear text-primary">
                                        <x-icons.more-vert/>
                                    </button>
                                    <div x-show="open" @click.outside="open = false"
                                        class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                                        <ul class="py-2">
                                            <li> <button type="button" class="clear text-primary"> <x-icons.file-download-line/> Excel </button> </li>
                                            @if (!$list->status)
                                                <li> <button type="button" class="clear text-primary" wire:click='activeList({{ $list->id }})'> <x-icons.eye/> Activar </button> </li>
                                            @else
                                                <li> <button type="button" class="clear text-primary" wire:click='inactiveList({{ $list->id }})'> <x-icons.eye-closed/> Inactivar </button> </li>
                                            @endif
                                            <li> <button type="button" class="clear text-primary" wire:click='confirmDelete({{ $list->id }})'> <x-icons.trash-outline/> Eliminar </button> </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="">
               {{ $lists->links() }}
            </div>
        </div>
    </article>
</section>
