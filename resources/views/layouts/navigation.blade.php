<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Linkerkant: Logo + Navigatie -->
            <div class="flex items-center space-x-4">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links (verborgen op mobiel) -->
                <div class="hidden sm:flex space-x-4">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    @auth
                        <x-nav-link :href="route('simulatiedashboard')" :active="request()->routeIs('simulatiedashboard')">
                            Simulatie Dashboard
                        </x-nav-link>

                        <x-nav-link :href="route('module.index')" :active="request()->routeIs('module.index')">
                            Module Dashboard
                        </x-nav-link>

                        <x-nav-link :href="route('conditions')" :active="request()->routeIs('conditions')">
                            Conditions Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('events')" :active="request()->routeIs('events')">
                            Events Dasbhoard
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Rechterkant: Hamburger (mobiel) + Profiel (desktop) -->
            <div class="flex items-center">
                <!-- Hamburger menu (alleen zichtbaar op mobiel) -->
                <div class="sm:hidden">
                    <button @click="open = !open" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-200 hover:text-gray-500 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Profiel Dropdown (desktop) -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>

        <!-- Mobiel menu (onderaan, togglebaar) -->
        <div :class="{ 'block': open, 'hidden': !open }" class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-responsive-nav-link>

                @auth
                    <x-responsive-nav-link :href="route('simulatiedashboard')" :active="request()->routeIs('simulatiedashboard')">
                        Simulatie Dashboard
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('module.index')" :active="request()->routeIs('module.index')">
                        Module Dashboard
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('conditions')" :active="request()->routeIs('conditions')">
                        Conditions Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('events')" :active="request()->routeIs('events')">
                        Events Dashboard
                    </x-responsive-nav-link>
                @endauth
            </div>

            <!-- Mobiel: Profielinstellingen -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
