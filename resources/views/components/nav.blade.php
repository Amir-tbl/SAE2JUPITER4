<nav class="navbar navbar-expand-lg" style="background-color: #e3f2fd;">
    <div class="container-fluid">

        {{-- Amélioration proposée:
          faire apparaître le bouton uniquement en fonction de la taille de la fenêtre pour différencier smartphone/pc apporte des problèmes :
          - notamment, si on rétrécie la fenêtre sur pc ça va faire apparaître le bouton comme si on était sur téléphone
          - mais aussi, les téléphones pliables ne pourrons donc pas voir le bouton pour utiliser l'objectif en grand écran
          - Cela peut aussi ne pas faire apparaître le bouton sur des tablettes qui ont pourtant une caméra
          Solutions:
          - Pour détecter réellement si un appareil a une caméra en JS et non juste si la taille d'écran correspond: navigator.mediaDevices.enumerateDevices().then(result=>console.log(result.filter(r=>r.kind=='videoinput'))) (source: https://www.reddit.com/r/learnjavascript/comments/qsehvi/is_there_a_way_to_detect_if_the_device_has_a/)
          - Même solution en php : ?
          - Plutôt détecter si un appareil est un téléphone ou une tablette (car les pc peuvent avoir une caméra webcam notamment): nécéssite l'installation d'un module: mobiledetect/mobiledetectlib (source: https://stackoverflow.com/questions/23779088/laravel-detect-mobile-tablet-and-load-correct-views) Attention toutefois il faut s'assurer de la légitimité du module et se demander si ce n'est pas de trop (github : https://github.com/serbanghita/Mobile-Detect)
        --}}
        <button class="btn btn-outline-success d-lg-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                 class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                <path
                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/>
                <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/>
                <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/>
                <path
                    d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z"/>
                <path d="M12 9h2V8h-2z"/>
            </svg>
            Scan
        </button>
        <a class="navbar-brand" href="/">Suivi des colis IUTV</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                <li class="nav-item">
                    <a class="nav-link" href="/">Dashboard</a>
                </li>
                <li class="nav-item">
                    <div class="d-flex">
                        <a class="nav-link pe-0" href="/orders">Commandes</a>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle ps-2" href="/orders" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false"></a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/orders">Voir les commandes</a></li>
                                <li><a class="dropdown-item" href="/orders?search=DEVIS">Voir les devis</a></li>
                                <li><a class="dropdown-item" href="/orders?search=BON_DE_COMMANDE">Voir les bons de
                                        commandes</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/suppliers">Fournisseurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about">À propos</a>
                </li>
            </ul>
            <div class="p-xl-2">
                <div class="btn-group">
                    <a class="btn btn-secondary" href="/account/profile" role="button" aria-expanded="false">Mon compte</a>
                    {{-- TODO Problème avec le dropdown qui fait qu'on doit appuyer deux fois sur le bouton pour ouvrir le menu --}}
                    <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Options du compte</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg-right">
                        <a class="dropdown-item" href="/account/profile">Mon profil</a>
                        <a class="dropdown-item" href="/account/profile">Mes paramètres</a>
                        <a class="dropdown-item" href="/account/profile">Gérer mon email</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="/logout">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
