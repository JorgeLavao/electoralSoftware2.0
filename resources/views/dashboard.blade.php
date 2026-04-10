<x-layouts.app :title="__('Dashboard')">

    <div
        x-data="{ modal: null }"
        class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">

        <div class="breadcrumbs text-gray-600">
            Noticias
        </div>

        <div class="flex flex-col gap-6">
            <!-- NOTICIA 1 -->
            <article
                @click="modal = 1"
                class="flex flex-col sm:flex-row items-start gap-4 cursor-pointer hover:bg-gray-50 transition rounded-xl p-3">
                <div class="shrink-0">
                    <img src="https://picsum.photos/320/220?random=1"
                        class="h-24 w-full sm:w-40 object-cover rounded-xl" />
                </div>

                <div class="flex-1">
                    <p class="text-sm text-gray-900">Septiembre 18 de 2025</p>
                    <h3 class="mt-1 text-xl font-bold text-gray-900">
                        Alianza política fortalece el trabajo territorial
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Nuevos sectores ciudadanos se integran a una agenda política enfocada en liderazgo, participación y cercanía con la comunidad.
                    </p>
                </div>
            </article>

            <!-- NOTICIA 2 -->
            <article
                @click="modal = 2"
                class="flex flex-col sm:flex-row items-start gap-4 cursor-pointer hover:bg-gray-50 transition rounded-xl p-3">
                <div class="shrink-0">
                    <img src="https://picsum.photos/320/220?random=2"
                        class="h-24 w-full sm:w-40 object-cover rounded-xl" />
                </div>

                <div class="flex-1">
                    <p class="text-sm text-gray-900">Agosto 30 de 2025</p>
                    <h3 class="mt-1 text-xl font-bold text-gray-900">
                        Encuentro político impulsa nuevas propuestas sociales
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Líderes y ciudadanos compartieron ideas orientadas al desarrollo local, la inclusión y el fortalecimiento institucional.
                    </p>
                </div>
            </article>

            <!-- NOTICIA 3 -->
            <article
                @click="modal = 3"
                class="flex flex-col sm:flex-row items-start gap-4 cursor-pointer hover:bg-gray-50 transition rounded-xl p-3">
                <div class="shrink-0">
                    <img src="https://picsum.photos/320/220?random=3"
                        class="h-24 w-full sm:w-40 object-cover rounded-xl" />
                </div>

                <div class="flex-1">
                    <p class="text-sm text-gray-900">Julio 12 de 2025</p>
                    <h3 class="mt-1 text-xl font-bold text-gray-900">
                        Juventudes participan en agenda de renovación política
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Espacios de diálogo con jóvenes buscan construir propuestas políticas innovadoras con visión de futuro y compromiso ciudadano.
                    </p>
                </div>
            </article>

            <!-- NOTICIA 4 -->
            <article
                @click="modal = 4"
                class="flex flex-col sm:flex-row items-start gap-4 cursor-pointer hover:bg-gray-50 transition rounded-xl p-3">
                <div class="shrink-0">
                    <img src="https://picsum.photos/320/220?random=4"
                        class="h-24 w-full sm:w-40 object-cover rounded-xl" />
                </div>

                <div class="flex-1">
                    <p class="text-sm text-gray-900">Junio 05 de 2025</p>
                    <h3 class="mt-1 text-xl font-bold text-gray-900">
                        Liderazgo político reafirma compromiso con el territorio
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        La organización política continúa promoviendo acciones cercanas a la ciudadanía para responder a sus principales necesidades.
                    </p>
                </div>
            </article>

        </div>


        <!-- MODAL -->
<div
    x-show="modal !== null"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    style="display: none;"
    @click="modal = null">

    <div class="absolute inset-0 bg-gray-800/55"></div>

    <div
        x-show="modal !== null"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-2xl"
        @click.stop>

        <button
            type="button"
            class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-xl bg-rose-500 text-white shadow-md transition hover:bg-rose-600"
            @click="modal = null">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <template x-if="modal === 1">
            <div>
                <img src="https://picsum.photos/320/220?random=1" class="mb-4 h-48 w-full rounded-xl object-cover">
                <p class="text-sm text-gray-500">Septiembre 18 de 2025</p>
                <h3 class="mt-1 text-xl font-bold text-gray-900">Alianza política fortalece el trabajo territorial</h3>
                <p class="mt-4 leading-relaxed text-gray-700">
                    Nuevos sectores ciudadanos se integran a una agenda política enfocada en liderazgo, participación y cercanía con la comunidad.
                </p>
            </div>
        </template>

    </div>
</div>

        


</div>

</x-layouts.app>
