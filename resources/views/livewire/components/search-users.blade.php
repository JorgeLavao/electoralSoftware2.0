<div class="container-v">
    <div class="space-y-6">
        <label>
            Administrador/Coordinador de la campaña
            <span class="text-red-500">*</span>
        </label>

        <div class="tom-bootstrap mt-0.5" wire:ignore>
            <select data-search-users multiple class="form-select clear"></select>
        </div>
    </div>
    @script
        <script>
            (function () {
                const select = $wire.$el.querySelector('[data-search-users]');
                if (!select) return;

                if (select.tomselect) {
                    select.tomselect.destroy();
                }
                select.tomselect = new TomSelect(select, {
                    maxItems: null,
                    plugins: ['remove_button'],
                    placeholder: 'Busca y selecciona usuarios…',
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    sortField: { field: 'text', direction: 'asc' },
                    options: @js($userOptions),
                    create: false,
                    load: function (query, callback) {
                        if (!query.length) return callback();
                        axios.get('/api/buscar-usuarios', {
                            params: { q: query }
                        })
                        .then(response => {
                            callback(response.data);
                        })
                        .catch(() => callback());
                    },
                    // Evento hacia Livewire (escúchalo en el componente padre)
                    onItemAdd: function (value) {
                        $wire.$dispatch('user-added', { userId: value });
                    },
                    onItemRemove: function (value) {
                        $wire.$dispatch('user-removed', { userId: value });
                    },
                });
                select.tomselect.setValue(@js($userIds));
            })();
        </script>
    @endscript
</div>

