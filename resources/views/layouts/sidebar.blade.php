<div class="sg-sidebar">
    <div class="sg-brand">
        <h4>
            <i class="bi bi-shield-check"></i>
            SupplyGuard
        </h4>

        <small>Platform Intelijen Risiko</small>
    </div>

    @auth
        <div class="px-3 pt-3">
            <div
                class="rounded-3 p-3"
                style="background: rgba(255,255,255,0.08);"
            >
                <div class="d-flex align-items-center gap-2">
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                        style="width: 40px; height: 40px;"
                    >
                        {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
                    </div>

                    <div class="overflow-hidden">
                        <div class="fw-semibold text-white text-truncate">
                            {{ Auth::user()->name ?? 'Pengguna' }}
                        </div>

                        <small class="text-light opacity-75">
                            {{
                                Auth::user()->role === 'admin'
                                    ? 'Administrator'
                                    : 'Pengguna'
                            }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <div class="sg-menu">
        <div class="sg-menu-title">
            Dasbor Utama
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
        >
            <i class="bi bi-speedometer2"></i>
            Dasbor
        </a>

        <a
            href="{{ route('countries.index') }}"
            class="{{ request()->routeIs('countries.*') ? 'active' : '' }}"
        >
            <i class="bi bi-globe2"></i>
            Negara Global
        </a>

        <a
            href="{{ route('risk.index') }}"
            class="{{ request()->routeIs('risk.*') ? 'active' : '' }}"
        >
            <i class="bi bi-activity"></i>
            Penilaian Risiko
        </a>

        <a
            href="{{ route('weather.index') }}"
            class="{{ request()->routeIs('weather.*') ? 'active' : '' }}"
        >
            <i class="bi bi-cloud-sun"></i>
            Pemantauan Cuaca
        </a>

        <a
            href="{{ route('currency.index') }}"
            class="{{ request()->routeIs('currency.*') ? 'active' : '' }}"
        >
            <i class="bi bi-currency-exchange"></i>
            Dampak Mata Uang
        </a>

        <a
            href="{{ route('news.index') }}"
            class="{{ request()->routeIs('news.*') ? 'active' : '' }}"
        >
            <i class="bi bi-newspaper"></i>
            Intelijen Berita
        </a>

        <a
            href="{{ route('ports.index') }}"
            class="{{ request()->routeIs('ports.*') ? 'active' : '' }}"
        >
            <i class="bi bi-geo-alt"></i>
            Lokasi Pelabuhan
        </a>

        <a
            href="{{ route('visualization.index') }}"
            class="{{ request()->routeIs('visualization.*') ? 'active' : '' }}"
        >
            <i class="bi bi-bar-chart-line"></i>
            Visualisasi Data
        </a>

        <a
            href="{{ route('comparison.index') }}"
            class="{{ request()->routeIs('comparison.*') ? 'active' : '' }}"
        >
            <i class="bi bi-columns-gap"></i>
            Perbandingan Negara
        </a>

        <a
            href="{{ route('watchlist.index') }}"
            class="{{ request()->routeIs('watchlist.*') ? 'active' : '' }}"
        >
            <i class="bi bi-star"></i>
            Daftar Pemantauan Favorit
        </a>

        @auth
            @if(Auth::user()->role === 'admin')
                <div class="sg-menu-title">
                    Manajemen Admin
                </div>

                <a
                    href="{{ route('admin.users.index') }}"
                    class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-people"></i>
                    Pengguna
                </a>

                <a
                    href="{{ route('admin.ports.index') }}"
                    class="{{ request()->routeIs('admin.ports.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-pin-map"></i>
                    Kelola Pelabuhan
                </a>

                <a
                    href="{{ route('admin.articles.index') }}"
                    class="{{ request()->routeIs('admin.articles.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-journal-text"></i>
                    Artikel
                </a>

                <a
                    href="{{ route('admin.words.index') }}"
                    class="{{ request()->routeIs('admin.words.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-chat-square-text"></i>
                    Kata Sentimen
                </a>

                <a
                    href="{{ route('admin.api-logs.index') }}"
                    class="{{ request()->routeIs('admin.api-logs.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-hdd-network"></i>
                    Log API
                </a>
            @endif
        @endauth
    </div>
</div>