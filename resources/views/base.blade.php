{{-- Layout principal de l'application : structure HTML, en-tête et chargement des assets --}}
@use(Database\Seeders\Status)
@use(Database\Seeders\PermissionValue)

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Suivi IUT Villetaneuse') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{asset('bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <script type="text/javascript" src="{{asset('bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <link href="{{asset('style.css')}}" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $user = Auth::user();
        $menuItems = [];
        if ($user->hasPermission(PermissionValue::ADMIN)) {
            $menuItems = [
                ['icon' => 'house', 'label' => 'Tableau de bord', 'url' => '/', 'match' => ['/', 'dashboard']],
                ['icon' => 'people', 'label' => 'Utilisateurs', 'url' => '/users', 'match' => ['users*']],
                ['icon' => 'list-task', 'label' => 'Toutes les commandes', 'url' => '/orders', 'match' => ['orders']],
                ['icon' => 'building', 'label' => 'Fournisseurs', 'url' => '/suppliers', 'match' => ['suppliers*']],
                ['icon' => 'journal-text', 'label' => 'Logs & Audit', 'url' => '/logs', 'match' => ['logs*']],
                ['icon' => 'bar-chart', 'label' => 'Statistiques', 'url' => '/stats', 'match' => ['stats*']],
                ['icon' => 'person', 'label' => 'Profil', 'url' => '/account/profile', 'match' => ['account/profile']],
            ];
        } elseif ($user->hasPermission(PermissionValue::GERER_PAIEMENT_FOURNISSEURS)) {
            $menuItems = [
                ['icon' => 'house', 'label' => 'Tableau de bord', 'url' => '/', 'match' => ['/', 'dashboard']],
                ['icon' => 'clipboard-check', 'label' => 'Commandes à valider', 'url' => '/orders/validation', 'match' => ['orders/validation']],
                ['icon' => 'list-task', 'label' => 'Suivi commandes', 'url' => '/orders/suivi', 'match' => ['orders/suivi']],
                ['icon' => 'building', 'label' => 'Fournisseurs', 'url' => '/suppliers', 'match' => ['suppliers*']],
                ['icon' => 'person', 'label' => 'Profil', 'url' => '/account/profile', 'match' => ['account/profile']],
            ];
        } elseif ($user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES)) {
            $menuItems = [
                ['icon' => 'house', 'label' => 'Tableau de bord', 'url' => '/', 'match' => ['/', 'dashboard']],
                ['icon' => 'pen', 'label' => 'BC à signer', 'url' => '/orders/signature', 'match' => ['orders/signature']],
                ['icon' => 'clock-history', 'label' => 'Historique signatures', 'url' => '/orders/historique-signatures', 'match' => ['orders/historique-signatures']],
                ['icon' => 'person', 'label' => 'Profil', 'url' => '/account/profile', 'match' => ['account/profile']],
            ];
        } elseif ($user->hasPermission(PermissionValue::GERER_COLIS_LIVRES)) {
            $menuItems = [
                ['icon' => 'house', 'label' => 'Tableau de bord', 'url' => '/', 'match' => ['/', 'dashboard']],
                ['icon' => 'box-seam', 'label' => 'Réception colis', 'url' => '/orders/reception', 'match' => ['orders/reception']],
                ['icon' => 'truck', 'label' => 'Distribution', 'url' => '/orders/distribution', 'match' => ['orders/distribution']],
                ['icon' => 'clock-history', 'label' => 'Historique', 'url' => '/orders/historique-crit', 'match' => ['orders/historique-crit']],
                ['icon' => 'person', 'label' => 'Profil', 'url' => '/account/profile', 'match' => ['account/profile']],
            ];
        } else {
            $menuItems = [
                ['icon' => 'house', 'label' => 'Tableau de bord', 'url' => '/', 'match' => ['/', 'dashboard']],
                ['icon' => 'plus-circle', 'label' => 'Créer un envoi', 'url' => '/orders/create/step1', 'match' => ['orders/create*']],
                ['icon' => 'clock-history', 'label' => 'Historique', 'url' => '/orders/historique-agent', 'match' => ['orders/historique-agent']],
                ['icon' => 'person', 'label' => 'Profil', 'url' => '/account/profile', 'match' => ['account/profile']],
            ];
        }
    @endphp
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184l-7.25 2.9A.5.5 0 0 0 0 3.669v8.662a.5.5 0 0 0 .305.462l7.25 2.9a.5.5 0 0 0 .372 0l7.25-2.9a.5.5 0 0 0 .305-.462V3.669a.5.5 0 0 0-.305-.462z"/>
                </svg>
                <span class="sidebar-title">Suivi Colis</span>
            </div>

            @php
                $iconPaths = [
                    'house' => 'M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z',
                    'plus-circle' => 'M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16|M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4',
                    'clock-history' => 'M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-1.398 1.8a7 7 0 0 0 .573-.756l.832.55a8 8 0 0 1-.652.861l-.753-.655m-1.103.692a7 7 0 0 0 .496-.44l.687.727a8 8 0 0 1-.964.857zm-.935.354a7 7 0 0 0 .354-.144l.404.915a8 8 0 0 1-.9.378zm-8.28-3.483A7 7 0 0 0 8 15a7 7 0 0 0 4.215-1.402L8 8.5V1a7 7 0 0 0-7 7 7 7 0 0 0 .967 3.583',
                    'person' => 'M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z',
                    'people' => 'M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4',
                    'list-task' => 'M2 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5zM3 3H2v1h1zm2 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 3m0 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 7m0 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-3-4a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V7a.5.5 0 0 0-.5-.5zm1 .5H2v1h1zm-1 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm1 .5H2v1h1z',
                    'building' => 'M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z|M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z',
                    'journal-text' => 'M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5|M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2|M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z',
                    'bar-chart' => 'M4 11H2v3h2zm5-4H7v7h2zm5-5h-2v12h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1z',
                    'clipboard-check' => 'M6.5 0A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0zm3 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z|M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1A2.5 2.5 0 0 1 9.5 5h-3A2.5 2.5 0 0 1 4 2.5zm6.854 7.354-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708',
                    'pen' => 'M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z',
                    'box-seam' => 'M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184l-7.25 2.9A.5.5 0 0 0 0 3.669v8.662a.5.5 0 0 0 .305.462l7.25 2.9a.5.5 0 0 0 .372 0l7.25-2.9a.5.5 0 0 0 .305-.462V3.669a.5.5 0 0 0-.305-.462z',
                    'truck' => 'M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2',
                ];
            @endphp
            <nav class="sidebar-nav">
                @foreach($menuItems as $item)
                <a href="{{ $item['url'] }}" class="sidebar-link {{ collect($item['match'])->contains(fn($pattern) => request()->is($pattern)) ? 'active' : '' }}">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        @foreach(explode('|', $iconPaths[$item['icon']] ?? '') as $path)
                        <path d="{{ $path }}"/>
                        @endforeach
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar">{{ substr(Auth::user()->getFirstName(), 0, 1) }}{{ substr(Auth::user()->getLastName(), 0, 1) }}</div>
                    <div>
                        <div class="sidebar-username">{{ Auth::user()->getFirstName() }} {{ Auth::user()->getLastName() }}</div>
                        <div class="sidebar-role">{{ Auth::user()->roles->first()?->getName() ?? 'Utilisateur' }}</div>
                    </div>
                </div>
                @if(app()->isLocal())
                <a href="/dev-login" class="sidebar-link">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                        <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                    </svg>
                    <span>Changer de compte</span>
                </a>
                @endif
                <a href="/logout" class="sidebar-link">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                    </svg>
                    <span>Déconnexion</span>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                    </svg>
                </button>
                <h1 class="topbar-title">@yield('page-title', 'Tableau de bord')</h1>
            </header>

            <div class="content-area">
                <x-alert />
                @yield('content')
            </div>
        </main>
    </div>

    <div id="modal-container"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const modalContainer = document.getElementById('modal-container');

        // Gestion de l'ouverture (GET)
        document.body.addEventListener('click', function (e) {

            const button = e.target.closest('.btn-load-modal');

            if (button && button.getAttribute('data-url')) {
                e.preventDefault();

                const url = button.getAttribute('data-url');

                const existingModalEl = modalContainer.querySelector('.modal');
                if (existingModalEl) {
                    const existingInstance = bootstrap.Modal.getInstance(existingModalEl);
                    if (existingInstance) {
                        existingInstance.dispose();
                    }
                }

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        modalContainer.innerHTML = html;

                        // Exécuter les scripts injectés
                        modalContainer.querySelectorAll('script').forEach(oldScript => {
                            const newScript = document.createElement('script');
                            newScript.textContent = oldScript.textContent;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });

                        const modalElement = modalContainer.querySelector('.modal');
                        const myModal = new bootstrap.Modal(modalElement);

                        // Initialiser le canvas signature après animation
                        if (modalContainer.querySelector('#signatureCanvas')) {
                            modalElement.addEventListener('shown.bs.modal', function () {
                                initSignatureCanvas();
                            }, { once: true });
                        }

                        myModal.show();
                    })
                    .catch(error => console.error('Erreur chargement modal:', error));
            }
        });

        // Gestion du POST (Formulaire)
        modalContainer.addEventListener('submit', function (e) {
            if (e.target && e.target.classList.contains('ajax-form')) {
                e.preventDefault();

                const form = e.target;
                const url = form.getAttribute('action');
                const formData = new FormData(form);

                const existingModalEl = modalContainer.querySelector('.modal');
                let existingInstance = null;
                if (existingModalEl) {
                    existingInstance = bootstrap.Modal.getInstance(existingModalEl);
                }

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json, text/html'
                    }
                })
                    .then(response => {
                        const contentType = response.headers.get("content-type");

                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json().then(data => {
                                return { type: 'json', data: data };
                            });
                        } else {
                            return response.text().then(html => {
                                return { type: 'html', html: html };
                            });
                        }
                    })
                    .then(result => {
                        if (result.type === 'json' && result.data.status === 'success') {
                            window.location.reload();
                            return;
                        }

                        if (result.type === 'html') {
                            const html = result.html;

                            if (html.includes('<html') || html.includes('<!DOCTYPE')) {
                                window.location.reload();
                                return;
                            }

                            if (existingInstance) existingInstance.dispose();
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                            document.body.classList.remove('modal-open');

                            modalContainer.innerHTML = html;

                            const newModalEl = modalContainer.querySelector('.modal');
                            if (newModalEl) {
                                const newModal = new bootstrap.Modal(newModalEl);
                                newModal.show();
                            }
                        }
                    })
                    .catch(error => console.error('Erreur AJAX:', error));

                // Gestion dynamique de la checkbox mail
                modalContainer.addEventListener('change', function (e) {

                    if (e.target && e.target.name === 'sendMail') {
                        const checkbox = e.target;

                        const parts = checkbox.id.split('-');
                        const orderId = parts[parts.length - 1];

                        const targetDiv = document.getElementById('mailOptionsDiv-' + orderId);

                        if (targetDiv) {
                            targetDiv.style.display = checkbox.checked ? "block" : "none";
                        }
                    }
                });
            }
        });

        // Boutons Valider/Refuser du modal ValidationSF
        modalContainer.addEventListener('click', function (e) {
            var btn = e.target.closest('#btnValiderSF');
            if (btn) {
                var bcFileInput = modalContainer.querySelector('#bcFileInput');
                var bcError = modalContainer.querySelector('#bcError');
                if (bcFileInput && !bcFileInput.files.length) {
                    if (bcError) bcError.style.display = 'block';
                    bcFileInput.classList.add('is-invalid');
                    bcFileInput.focus();
                    return;
                }
                if (bcError) bcError.style.display = 'none';
                if (bcFileInput) bcFileInput.classList.remove('is-invalid');
                var actionField = modalContainer.querySelector('#actionField');
                if (actionField) actionField.value = 'validate';
                var form = btn.closest('form');
                if (form) {
                    if (form.requestSubmit) form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                }
                return;
            }

            btn = e.target.closest('#btnRefuserSF');
            if (btn) {
                var commentField = modalContainer.querySelector('#commentField');
                var commentError = modalContainer.querySelector('#commentError');
                if (commentField && !commentField.value.trim()) {
                    if (commentError) commentError.style.display = 'block';
                    commentField.classList.add('is-invalid');
                    commentField.focus();
                    return;
                }
                if (commentError) commentError.style.display = 'none';
                if (commentField) commentField.classList.remove('is-invalid');
                var actionField = modalContainer.querySelector('#actionField');
                if (actionField) actionField.value = 'refuse';
                var form = btn.closest('form');
                if (form) {
                    if (form.requestSubmit) form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                }
                return;
            }
        });

        // Signature Canvas — initialisation et dessin
        var signatureDrawn = false;
        var signatureSaved = false;

        function initSignatureCanvas() {
            var canvas = modalContainer.querySelector('#signatureCanvas');
            if (!canvas) return;

            // Vérifier si signature déjà enregistrée
            var dataField = modalContainer.querySelector('#signatureData');
            if (dataField && dataField.value === 'saved') {
                signatureSaved = true;
            }

            var ctx = canvas.getContext('2d');
            var drawing = false;
            signatureDrawn = false;

            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * 2;
            canvas.height = rect.height * 2;
            ctx.scale(2, 2);

            ctx.strokeStyle = '#1E2A52';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            function getPos(e) {
                var r = canvas.getBoundingClientRect();
                var touch = e.touches ? e.touches[0] : e;
                return { x: touch.clientX - r.left, y: touch.clientY - r.top };
            }

            function startDraw(e) {
                e.preventDefault();
                drawing = true;
                var pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            }

            function draw(e) {
                if (!drawing) return;
                e.preventDefault();
                var pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                signatureDrawn = true;
            }

            function stopDraw() {
                drawing = false;
            }

            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDraw);
            canvas.addEventListener('mouseleave', stopDraw);
            canvas.addEventListener('touchstart', startDraw, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDraw);
        }

        // Boutons signature : effacer, enregistrer, modifier, annuler
        modalContainer.addEventListener('click', function (e) {
            // Effacer le canvas
            if (e.target.closest('#btnClearSignature')) {
                var canvas = modalContainer.querySelector('#signatureCanvas');
                if (canvas) {
                    var ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    signatureDrawn = false;
                }
                return;
            }

            // Enregistrer ma signature (POST AJAX vers le serveur)
            if (e.target.closest('#btnSaveSignature')) {
                var canvas = modalContainer.querySelector('#signatureCanvas');
                if (!canvas || !signatureDrawn) return;

                var base64 = canvas.toDataURL('image/png');
                var csrfToken = document.querySelector('input[name="_token"]')?.value;

                fetch('{{ route("user.save-signature") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ signature_data: base64 })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        signatureSaved = true;
                        // Afficher le bloc signature enregistrée
                        var savedBlock = modalContainer.querySelector('#savedSignatureBlock');
                        var drawBlock = modalContainer.querySelector('#drawSignatureBlock');
                        var img = modalContainer.querySelector('#savedSignatureImg');
                        var dataField = modalContainer.querySelector('#signatureData');

                        if (img) {
                            img.src = base64;
                            img.style.display = '';
                        }
                        if (savedBlock) savedBlock.style.display = '';
                        if (drawBlock) drawBlock.style.display = 'none';
                        if (dataField) dataField.value = 'saved';

                        var sigError = modalContainer.querySelector('#signatureError');
                        if (sigError) sigError.style.display = 'none';
                    }
                });
                return;
            }

            // Modifier ma signature
            if (e.target.closest('#btnModifierSignature')) {
                var savedBlock = modalContainer.querySelector('#savedSignatureBlock');
                var drawBlock = modalContainer.querySelector('#drawSignatureBlock');
                if (savedBlock) savedBlock.style.display = 'none';
                if (drawBlock) drawBlock.style.display = '';
                signatureSaved = false;
                signatureDrawn = false;
                var dataField = modalContainer.querySelector('#signatureData');
                if (dataField) dataField.value = '';

                // Réinitialiser le canvas
                var canvas = modalContainer.querySelector('#signatureCanvas');
                if (canvas) {
                    var rect = canvas.getBoundingClientRect();
                    canvas.width = rect.width * 2;
                    canvas.height = rect.height * 2;
                    var ctx = canvas.getContext('2d');
                    ctx.scale(2, 2);
                    ctx.strokeStyle = '#1E2A52';
                    ctx.lineWidth = 2.5;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                }
                return;
            }

            // Annuler modification (revenir à la signature enregistrée)
            if (e.target.closest('#btnCancelDrawSignature')) {
                var savedBlock = modalContainer.querySelector('#savedSignatureBlock');
                var drawBlock = modalContainer.querySelector('#drawSignatureBlock');
                if (savedBlock) savedBlock.style.display = '';
                if (drawBlock) drawBlock.style.display = 'none';
                signatureSaved = true;
                var dataField = modalContainer.querySelector('#signatureData');
                if (dataField) dataField.value = 'saved';
                return;
            }
        });

        // Boutons Signer/Refuser du modal Signature Directeur
        modalContainer.addEventListener('click', function (e) {
            var btn = e.target.closest('#btnSignerDir');
            if (btn) {
                // Vérifier qu'une signature est enregistrée
                var sigError = modalContainer.querySelector('#signatureError');
                if (!signatureSaved) {
                    if (sigError) sigError.style.display = 'block';
                    return;
                }
                if (sigError) sigError.style.display = 'none';

                var actionField = modalContainer.querySelector('#actionFieldDir');
                if (actionField) actionField.value = 'sign';
                var form = btn.closest('form');
                if (form) {
                    if (form.requestSubmit) form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                }
                return;
            }

            btn = e.target.closest('#btnRefuserDir');
            if (btn) {
                var commentField = modalContainer.querySelector('#commentFieldDir');
                var commentError = modalContainer.querySelector('#commentErrorDir');
                if (commentField && !commentField.value.trim()) {
                    if (commentError) commentError.style.display = 'block';
                    commentField.classList.add('is-invalid');
                    commentField.focus();
                    return;
                }
                if (commentError) commentError.style.display = 'none';
                if (commentField) commentField.classList.remove('is-invalid');
                var actionField = modalContainer.querySelector('#actionFieldDir');
                if (actionField) actionField.value = 'refuse';
                var form = btn.closest('form');
                if (form) {
                    if (form.requestSubmit) form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                }
                return;
            }
        });

        // Reset erreurs Directeur
        modalContainer.addEventListener('input', function (e) {
            if (e.target && e.target.id === 'commentFieldDir') {
                var commentError = modalContainer.querySelector('#commentErrorDir');
                if (commentError) commentError.style.display = 'none';
                e.target.classList.remove('is-invalid');
            }
        });

        // Reset erreurs ValidationSF
        modalContainer.addEventListener('change', function (e) {
            if (e.target && e.target.id === 'bcFileInput') {
                var bcError = modalContainer.querySelector('#bcError');
                if (bcError) bcError.style.display = 'none';
                e.target.classList.remove('is-invalid');
            }
        });
        modalContainer.addEventListener('input', function (e) {
            if (e.target && e.target.id === 'commentField') {
                var commentError = modalContainer.querySelector('#commentError');
                if (commentError) commentError.style.display = 'none';
                e.target.classList.remove('is-invalid');
            }
        });

        // Configuration : Mapping des Enums PHP vers JS
        const STATUS = {
            BROUILLON:                  "{{ Status::BROUILLON->value }}",
            DEVIS:                      "{{ Status::DEVIS->value }}",
            DEVIS_REFUSE:               "{{ Status::DEVIS_REFUSE->value }}",
            BON_DE_COMMANDE_NON_SIGNE:  "{{ Status::BON_DE_COMMANDE_NON_SIGNE->value }}",
            BON_DE_COMMANDE_REFUSE:     "{{ Status::BON_DE_COMMANDE_REFUSE->value }}",
            BON_DE_COMMANDE_SIGNE:      "{{ Status::BON_DE_COMMANDE_SIGNE->value }}",
            COMMANDE:                   "{{ Status::COMMANDE->value }}",
            COMMANDE_REFUSEE:           "{{ Status::COMMANDE_REFUSEE->value }}",
            COMMANDE_AVEC_REPONSE:      "{{ Status::COMMANDE_AVEC_REPONSE->value }}",
            PARTIELLEMENT_LIVRE:        "{{ Status::PARTIELLEMENT_LIVRE->value }}",
            SERVICE_FAIT:               "{{ Status::SERVICE_FAIT->value }}",
            LIVRE_ET_PAYE:              "{{ Status::LIVRE_ET_PAYE->value }}",
            ANNULE:                     "{{ Status::ANNULE->value }}",
        };

        // Logique d'automatisation
        modalContainer.addEventListener('change', function (e) {

            if (!e.target || !e.target.closest('form')) return;

            const target = e.target;

            if (!target.id.includes('-')) return;
            const parts = target.id.split('-');
            const orderId = parts[parts.length - 1];

            const statusSelect = document.getElementById(`statusSelectOrder-${orderId}`);
            const statusDesc = document.getElementById(`statusDescription-${orderId}`);
            const autoMsg = document.getElementById(`autoStatusMsg-${orderId}`);
            const checkboxSigned = document.getElementById(`checkboxSigned-${orderId}`);
            const fileInputPO = document.getElementById(`inputPurchaseOrder-${orderId}`);

            if (!statusSelect) return;

            const currentStatus = statusSelect.value;
            let newStatus = null;
            let reason = "";

            // CAS 1 & 2 : BON DE COMMANDE
            if (target.name === 'purchase_order' || target.name === 'signed') {

                const isSigned = checkboxSigned ? checkboxSigned.checked : false;
                const hasFile = fileInputPO && fileInputPO.files.length > 0;

                if (isSigned) {
                    const allowedStatuses = [
                        STATUS.BROUILLON,
                        STATUS.DEVIS,
                        STATUS.DEVIS_REFUSE,
                        STATUS.BON_DE_COMMANDE_NON_SIGNE,
                        STATUS.BON_DE_COMMANDE_REFUSE
                    ];

                    if (allowedStatuses.includes(currentStatus)) {
                        newStatus = STATUS.BON_DE_COMMANDE_SIGNE;
                        reason = "Statut suggéré suite à la validation du bon de commande signé.";
                    }
                }
                else if (hasFile) {
                    const allowedStatuses = [
                        STATUS.BROUILLON,
                        STATUS.DEVIS,
                        STATUS.DEVIS_REFUSE
                    ];

                    if (allowedStatuses.includes(currentStatus)) {
                        newStatus = STATUS.BON_DE_COMMANDE_NON_SIGNE;
                        reason = "Statut suggéré suite à l'ajout du bon de commande.";
                    }
                }
            }

            // CAS 3 : BON DE LIVRAISON
            if (target.name === 'delivery_note' && target.files.length > 0) {

                const excludedStatuses = [
                    STATUS.LIVRE_ET_PAYE,
                    STATUS.ANNULE,
                    STATUS.SERVICE_FAIT
                ];

                if (!excludedStatuses.includes(currentStatus)) {
                    newStatus = STATUS.SERVICE_FAIT;
                    reason = "Statut suggéré suite à l'ajout du bon de livraison.";
                }
            }

            // Application des changements
            if (newStatus && newStatus !== currentStatus) {
                statusSelect.value = newStatus;

                const selectedOption = statusSelect.querySelector(`option[value="${newStatus}"]`);
                if (selectedOption && statusDesc) {
                    statusDesc.innerText = selectedOption.getAttribute('data-description');
                }

                if (autoMsg) {
                    autoMsg.innerText = "✨ " + reason;
                    autoMsg.classList.remove('d-none');

                    statusSelect.classList.add('border-success', 'text-success', 'fw-bold');

                    setTimeout(() => {
                        statusSelect.classList.remove('border-success', 'text-success', 'fw-bold');
                    }, 3000);
                }
            }
        });
    });

    document.addEventListener('hidden.bs.modal', () => {
        window.location.href = window.location.href;
    });

    document.addEventListener('DOMContentLoaded', () => {
        const loginAlert = document.getElementById('login-alert');
        if (loginAlert) {
            setTimeout(() => {
            loginAlert.classList.add('d-none');
            }, 6000);
        }
    });

    // Sidebar toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

</script>
</body>
</html>
