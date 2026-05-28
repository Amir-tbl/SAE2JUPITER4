<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Suivi IUT Villetaneuse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }
        .login-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            padding: 40px 36px;
            border: 1px solid rgba(0,0,0,0.08);
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo {
            width: 56px;
            height: 56px;
            margin: 0 auto 16px;
            background: var(--navy);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-logo svg {
            width: 32px;
            height: 32px;
            color: #fff;
        }
        .login-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--navy);
            margin: 0 0 6px 0;
            letter-spacing: 0.4px;
        }
        .login-subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E6E7EA;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--navy);
        }
        .form-input.error {
            border-color: var(--badge-red);
        }
        .error-message {
            font-size: 13px;
            color: var(--badge-red);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .login-button {
            width: 100%;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 0 rgba(0,0,0,0.18);
        }
        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 0 rgba(0,0,0,0.18);
        }
        .login-button:active {
            transform: translateY(1px);
            box-shadow: 0 1px 0 rgba(0,0,0,0.18);
        }
        .login-footer {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #E6E7EA;
            text-align: center;
        }
        .dev-link {
            font-size: 12px;
            color: #8A8F9B;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .dev-link:hover {
            color: var(--navy);
        }
        .dev-link svg {
            width: 14px;
            height: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <svg fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184l-7.25 2.9A.5.5 0 0 0 0 3.669v8.662a.5.5 0 0 0 .305.462l7.25 2.9a.5.5 0 0 0 .372 0l7.25-2.9a.5.5 0 0 0 .305-.462V3.669a.5.5 0 0 0-.305-.462z"/>
                    </svg>
                </div>
                <h1 class="login-title">Suivi Colis IUT</h1>
                <p class="login-subtitle">Connectez-vous à votre compte</p>
            </div>

            <form method="POST" action="/login" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input @error('email') error @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="votre.email@univ-paris13.fr"
                    >
                    @error('email')
                        <p class="error-message">
                            <svg fill="currentColor" viewBox="0 0 16 16" style="width: 14px; height: 14px;">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        required
                        placeholder="Entrez votre mot de passe"
                    >
                </div>

                <button type="submit" class="login-button">
                    Se connecter
                </button>
            </form>

            @if(app()->isLocal())
            <div class="login-footer">
                <a href="/dev-login" class="dev-link">
                    <svg fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zm-6 8A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm6 0A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm6 0a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5z"/>
                    </svg>
                    Connexion rapide (dev)
                </a>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
