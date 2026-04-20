<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body>
    <div class="dashboard">
        <div class="dashboard__container">
            <header class="dashboard__header">
                <div class="dashboard__header--logo">
                    <a href="{{ route('dashboard') }}">
                        <h1>
                            Smart<span class="text-primary">E</span>lect
                        </h1>
                    </a>
                </div>
                <div class="dashboard__header--profile">
                    <h4 class="dashboard__header--profile--name">Hola! {{ Auth::user()->first_name }}</h4>
                    @livewire('layout.notifications')
                    <div class="relative w-8 h-8">
                        <div class="container-profile">
                            <div class="profile-data">
                                <input type="checkbox" name="profile" id="view-profile" class="hide-input">
                                <label for="view-profile" class="cursor-pointer">
                                    <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/background=C4C4C4?name={{ Auth::user()->first_name }}?bold=true">
                                </label>
                                <div class="profile-data__display">
                                    <ul>
                                        <li>
                                            <a href="">
                                                <span class="iconify" data-icon="mingcute:user-3-fill"><x-icons.user-3-fill /></span>
                                                Mi Perfil
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                                @csrf
                                                <button type="submit" class="clear">
                                                    <x-icons.right-fill /> Salir
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @livewire('components.campaign-select', ['mode' => 'desktop'])
                </div>
            </header>

            <main class="dashboard__main">
                <aside class="dashboard__main__aside">
                    <div class="container-lateral-menu">
                        <input type="checkbox" name="one" id="lateral-menu" class="hide-input">
                        <label for="lateral-menu">
                            <span class="iconify icon-open" data-icon="mingcute:menu-fill" data-width="24" data-height="24">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <g fill="none">
                                        <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                        <path fill="currentColor" d="M20 17.5a1.5 1.5 0 0 1 .144 2.993L20 20.5H4a1.5 1.5 0 0 1-.144-2.993L4 17.5zm0-7a1.5 1.5 0 0 1 0 3H4a1.5 1.5 0 0 1 0-3zm0-7a1.5 1.5 0 0 1 0 3H4a1.5 1.5 0 1 1 0-3z" />
                                    </g>
                                </svg>
                            </span>
                            <span class="iconify icon-close" data-icon="mingcute:close-fill" data-width="24" data-height="24">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <g fill="none" fill-rule="evenodd">
                                        <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                        <path fill="currentColor" d="m12 14.122l5.303 5.303a1.5 1.5 0 0 0 2.122-2.122L14.12 12l5.304-5.303a1.5 1.5 0 1 0-2.122-2.121L12 9.879L6.697 4.576a1.5 1.5 0 1 0-2.122 2.12L9.88 12l-5.304 5.304a1.5 1.5 0 1 0 2.122 2.12z" />
                                    </g>
                                </svg>
                            </span>
                        </label>
                        <div>
                            <div class="dashboard__main__aside--data__info">
                                Campaña
                                <h4>{{ session('current_campaign')->candidate_name ?? 'Sin campañas' }}</h4>
                                <h5 class="text-gray-300">{{ session('current_campaign')->position ?? '-' }}</h5>
                            </div>
                            <nav>
                                <ul class="lateral-menu">
                                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'item__active' : '' }}">Noticias</a></li>
                                    <li><a href="{{ route('campaign.index') }}" class="{{ request()->routeIs('campaign.index') ? 'item__active' : '' }}">Gestionar Campañas</a></li>
                                    @if (session('current_campaign'))
                                    <li><a href="{{ route('supporter.index', session('current_campaign')->code) }}" class="{{ request()->routeIs('supporter.*') ? 'item__active' : '' }}">Simpatizantes</a></li>
                                    <li><a href="{{ route('list.index', session('current_campaign')->code) }}" class="{{ request()->routeIs('list.*') ? 'item__active' : '' }}">Listados</a></li>
                                    <li><a href="{{ route('campaign.add-supporter', session('current_campaign')->code) }}" class="{{ request()->routeIs('campaign.add-supporter') ? 'item__active' : '' }}">Referir Simpatizante <span class="iconify" data-icon="mingcute:right-fill"></span></a></li>
                                    @endif
                                </ul>
                            </nav>
                            @if (session('current_campaign'))
                            <div class="dashboard__main__aside--vote">
                                <a href="{{ route('point.index', session('current_campaign')->code) }}" class="item__accent"><span class="iconify" data-icon="mdi:vote"></span> Punto de Votación</a>
                            </div>
                            @endif
                            @livewire('components.campaign-select', ['mode' => 'mobile'])
                        </div>
                    </div>
                </aside>
                {{ $slot }}
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('alert', (...params) => {
                handleAlert(params)
            })

            Livewire.on('alert-confirm', (...params) => {
                handleConfirmAlert(params)
            })
        })
    </script>
    @stack('scripts')
</body>

</html>