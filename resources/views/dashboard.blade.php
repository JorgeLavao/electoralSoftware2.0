<x-layouts.app :title="__('Dashboard')">
    <div
        x-data="{
            modal: false,
            selectedNews: null,
            newsItems: {{ Js::from(
                $news->getCollection()->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : "https://picsum.photos/320/220?random={$item->id}",
                    'published_at' => optional($item->published_at)->format('Y-m-d'),
                    'published_at_label' => \Illuminate\Support\Str::ucfirst(optional($item->published_at)->translatedFormat('F d \\d\\e Y')),
                    'author_name' => $item->user?->name ?? '',
                ])->values()
            ) }},
            openNews(id) {
                this.selectedNews = this.newsItems.find(item => item.id === id) ?? null;
                this.modal = this.selectedNews !== null;
            }
        }"
        class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">

        <div class="breadcrumbs text-gray-600">
            Noticias
        </div>

        <ul class="flex items-center">
            @can('create', \App\Models\News::class)
            <li class="ml-auto">
                <a href="{{ route('news.manager') }}" class="button btn-primary">
                    Crear Noticia
                </a>
            </li>
            @endcan
        </ul>

        <div class="flex flex-col gap-6 bg-white">
            @forelse ($news as $item)
            <article
                @click="openNews({{ $item->id }})"
                class="flex cursor-pointer flex-col items-start gap-4 rounded-xl p-3 transition hover:bg-gray-50 sm:flex-row">
                <div class="shrink-0">
                    <img
                        src="{{ $item->image_path ? asset('storage/' . $item->image_path) : "https://picsum.photos/320/220?random={$item->id}" }}"
                        alt="{{ $item->title }}"
                        class="h-24 w-full rounded-xl object-cover sm:w-40" />
                </div>

                <div class="flex-1">
                    <p class="text-sm text-gray-900">
                        {{ \Illuminate\Support\Str::ucfirst(optional($item->published_at)->translatedFormat('F d \\d\\e Y')) }}
                    </p>

                    <h3 class="mt-1 text-xl font-bold text-gray-900">
                        {{ $item->title }}
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        {{ \Illuminate\Support\Str::limit($item->description, 180) }}
                    </p>
                    @can('update', $item)
                    <div class="mt-4 flex justify-end">
                        <a
                            href="{{ route('news.edit', $item) }}"
                            @click.stop
                            class="button btn-primary">
                            Editar Noticia
                        </a>
                    </div>
                    @endcan
                </div>
            </article>
            @empty
            <div class="rounded-xl border border-dashed py-12 text-center text-gray-400">
                No hay noticias registradas.
            </div>
            @endforelse
        </div>

        <x-pagination :paginator="$news" />

        <div
            x-show="modal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            style="display: none;"
            @click="modal = false; selectedNews = null">

            <div class="absolute inset-0 bg-gray-800/55"></div>

            <div
                x-show="modal"
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
                    @click="modal = false; selectedNews = null">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <template x-if="selectedNews">
                    <div>
                        <img
                            :src="selectedNews.image_url"
                            :alt="selectedNews.title"
                            class="mb-4 h-48 w-full rounded-xl object-cover">

                        <p class="text-sm text-gray-500" x-text="selectedNews.published_at_label"></p>
                        <h3 class="mt-1 text-xl font-bold text-gray-900" x-text="selectedNews.title"></h3>
                        <p class="mt-4 leading-relaxed text-gray-700" x-text="selectedNews.description"></p>

                        <div class="mt-6 flex items-center justify-end gap-x-2">

                            <span class="whitespace-nowrap text-sm font-medium text-gray-700" x-text="selectedNews.author_name"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-layouts.app>
