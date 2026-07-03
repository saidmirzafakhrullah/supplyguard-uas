<div class="sg-sidebar">
    <div class="sg-brand">
        <h4><i class="bi bi-shield-check"></i> SupplyGuard</h4>
        <small>Risk Intelligence Platform</small>
    </div>

    <div class="sg-menu">
        <div class="sg-menu-title">Main Dashboard</div>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('countries.index') }}" class="{{ request()->routeIs('countries.*') ? 'active' : '' }}">
            <i class="bi bi-globe2"></i> Global Country
        </a>

        <a href="{{ route('risk.index') }}" class="{{ request()->routeIs('risk.*') ? 'active' : '' }}">
            <i class="bi bi-activity"></i> Risk Scoring
        </a>

        <a href="{{ route('weather.index') }}" class="{{ request()->routeIs('weather.*') ? 'active' : '' }}">
            <i class="bi bi-cloud-sun"></i> Weather Monitoring
        </a>

        <a href="{{ route('currency.index') }}" class="{{ request()->routeIs('currency.*') ? 'active' : '' }}">
            <i class="bi bi-currency-exchange"></i> Currency Impact
        </a>

        <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}">
            <i class="bi bi-newspaper"></i> News Intelligence
        </a>

        <a href="{{ route('ports.index') }}" class="{{ request()->routeIs('ports.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i> Port Location
        </a>

        <a href="{{ route('visualization.index') }}" class="{{ request()->routeIs('visualization.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Data Visualization
        </a>

        <a href="{{ route('comparison.index') }}" class="{{ request()->routeIs('comparison.*') ? 'active' : '' }}">
            <i class="bi bi-columns-gap"></i> Country Comparison
        </a>

        <a href="{{ route('watchlist.index') }}" class="{{ request()->routeIs('watchlist.*') ? 'active' : '' }}">
            <i class="bi bi-star"></i> Favorite Monitoring List
        </a>

        <div class="sg-menu-title">Admin Management</div>

        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Users
        </a>

        <a href="{{ route('admin.ports.index') }}" class="{{ request()->routeIs('admin.ports.*') ? 'active' : '' }}">
            <i class="bi bi-pin-map"></i> Manage Ports
        </a>

        <a href="{{ route('admin.articles.index') }}" class="{{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Articles
        </a>

        <a href="{{ route('admin.words.index') }}" class="{{ request()->routeIs('admin.words.*') ? 'active' : '' }}">
            <i class="bi bi-chat-square-text"></i> Sentiment Words
        </a>

        <a href="{{ route('admin.api-logs.index') }}" class="{{ request()->routeIs('admin.api-logs.*') ? 'active' : '' }}">
            <i class="bi bi-hdd-network"></i> API Logs
        </a>
    </div>
</div>