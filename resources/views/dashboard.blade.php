<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <h2 class="text-2xl font-bold text-gray-800">Últimas Noticias</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <img 
                    src="https://picsum.photos/600/400" 
                    alt="Imagen de noticia" 
                    class="h-48 w-full object-cover"
                />
                <div class="p-4">
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">
                        Título
                    </h3>
                    <p class="text-sm text-gray-600">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at porttitor sem.  Aliquam erat volutpat. Donec sedsas ipsum interdum.
                    </p>
                </div>
            </div>

            </div>
    </div>
</x-layouts.app>