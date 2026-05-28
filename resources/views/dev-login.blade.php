<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Login - Suivi IUT Villetaneuse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{asset('style.css')}}" rel="stylesheet" type="text/css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dev-login-container {
            max-width: 1200px;
            width: 100%;
            padding: 24px;
        }
        .dev-login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .dev-login-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--navy);
            margin: 0 0 8px 0;
        }
        .dev-login-subtitle {
            font-size: 14px;
            color: #666;
        }
        .dev-login-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        .dev-user-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .dev-user-card:hover {
            border-color: var(--navy);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        }
        .dev-user-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--taupe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            color: #fff;
            margin: 0 auto 12px;
        }
        .dev-user-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--navy);
            text-align: center;
            margin-bottom: 8px;
        }
        .dev-user-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }
        .dev-role-badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
        }
        .dev-user-card form {
            margin: 0;
        }
        .dev-user-card button {
            width: 100%;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            text-align: left;
        }
        /* Filter bar */
        .dev-filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            padding: 14px 18px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .dev-filter-bar input {
            flex: 1;
            min-width: 200px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px 14px 10px 38px;
            font-size: 14px;
            font-family: inherit;
            color: var(--ink);
            outline: none;
            transition: border-color 0.2s;
        }
        .dev-filter-bar input:focus { border-color: var(--navy); }
        .dev-search-wrapper {
            position: relative;
            flex: 1;
            min-width: 200px;
        }
        .dev-search-wrapper svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .dev-filter-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .dev-chip {
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid #e0e0e0;
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
        }
        .dev-chip:hover { border-color: var(--navy); color: var(--navy); }
        .dev-chip.active { background: var(--navy); color: #fff; border-color: var(--navy); }
        .dev-count {
            font-size: 13px;
            color: #888;
            margin-left: auto;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="dev-login-container">
        <div class="dev-login-header">
            <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16" style="color: var(--navy); margin-bottom: 12px;">
                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184l-7.25 2.9A.5.5 0 0 0 0 3.669v8.662a.5.5 0 0 0 .305.462l7.25 2.9a.5.5 0 0 0 .372 0l7.25-2.9a.5.5 0 0 0 .305-.462V3.669a.5.5 0 0 0-.305-.462z"/>
            </svg>
            <h1 class="dev-login-title">Environnement de Développement</h1>
            <p class="dev-login-subtitle">Sélectionnez un utilisateur pour vous connecter</p>
        </div>

        <!-- Filtre par rôle et recherche -->
        <div class="dev-filter-bar">
            <div class="dev-search-wrapper">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
                <input type="text" id="dev-search" placeholder="Rechercher par nom ou email...">
            </div>
            <div class="dev-filter-chips" id="dev-role-filters">
                <button class="dev-chip active" data-role="all">Tous</button>
                @php
                    $allRoles = $users->flatMap(fn($u) => $u->roles->pluck('name'))->unique()->sort()->values();
                @endphp
                @foreach($allRoles as $roleName)
                    <button class="dev-chip" data-role="{{ $roleName }}">{{ $roleName }}</button>
                @endforeach
            </div>
            <span class="dev-count" id="dev-count">{{ $users->count() }} comptes</span>
        </div>

        <div class="dev-login-grid" id="dev-grid">
            @foreach($users as $user)
                <form method="POST" action="/dev-login/{{ $user->getId() }}" class="dev-user-card" data-name="{{ strtolower($user->getFullName()) }}" data-email="{{ strtolower($user->getEmail()) }}" data-roles="{{ $user->roles->pluck('name')->implode(',') }}">
                    @csrf
                    <button type="submit">
                        <div class="dev-user-avatar">
                            {{ substr($user->getFirstName(), 0, 1) }}{{ substr($user->getLastName(), 0, 1) }}
                        </div>
                        <div class="dev-user-name">
                            {{ $user->getFullName() }}
                        </div>
                        <div class="dev-user-roles">
                            @php
                                $roleColors = [
                                    'Administrateur' => 'b-red',
                                    'SuperAdmin' => 'b-red',
                                    'Directeur' => 'b-blue',
                                    'Service Financier' => 'b-green',
                                    'CRIT' => 'b-grey',
                                ];
                            @endphp
                            @foreach($user->roles as $role)
                                @php
                                    $roleName = $role->getName();
                                    $colorClass = 'b-grey';

                                    foreach ($roleColors as $key => $color) {
                                        if (str_contains($roleName, $key)) {
                                            $colorClass = $color;
                                            break;
                                        }
                                    }

                                    if (str_contains($roleName, 'Département')) {
                                        $colorClass = 'b-blue';
                                    }
                                @endphp
                                <span class="dev-role-badge badge {{ $colorClass }}">
                                    {{ $roleName }}
                                </span>
                            @endforeach
                        </div>
                    </button>
                </form>
            @endforeach
        </div>
    </div>

    <script>
    // Filtrage par rôle et recherche texte
    document.addEventListener('DOMContentLoaded', function() {
        const search = document.getElementById('dev-search');
        const chips = document.querySelectorAll('.dev-chip');
        const cards = document.querySelectorAll('.dev-user-card');
        const count = document.getElementById('dev-count');
        let activeRole = 'all';

        function filter() {
            const q = search.value.toLowerCase().trim();
            let visible = 0;
            cards.forEach(card => {
                const name = card.dataset.name;
                const email = card.dataset.email;
                const roles = card.dataset.roles;
                const matchSearch = !q || name.includes(q) || email.includes(q);
                const matchRole = activeRole === 'all' || roles.split(',').includes(activeRole);
                const show = matchSearch && matchRole;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            count.textContent = visible + ' compte' + (visible > 1 ? 's' : '');
        }

        search.addEventListener('input', filter);

        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                activeRole = this.dataset.role;
                filter();
            });
        });
    });
    </script>
</body>
</html>
