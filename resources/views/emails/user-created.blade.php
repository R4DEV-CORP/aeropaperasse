<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur notre plateforme</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #1f2936;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 30px;
            color: #374151;
        }
        .info-box {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2936;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1f2936;
        }
        .label {
            font-weight: 600;
            color: #4b5563;
        }
        .value {
            color: #1f2937;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #1e3a8a;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://app.aeropaperasse.fr/images/aeropaperasse-logo-white.png" alt="Logo" class="logo">
            <h1>Bienvenue sur notre plateforme</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $user->name }},</p>

            <p>Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter à notre plateforme.</p>

            <div class="info-section">
                <div class="section-title">Vos identifiants</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Email :</span><br>
                        <span class="value">{{ $user->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Mot de passe :</span><br>
                        <span class="value">{{ $password }}</span>
                    </div>
                    <!-- <div class="info-item">
                        <span class="label">Rôle :</span><br>
                        <span class="value">{{ ucfirst($user->role) }}</span>
                    </div> -->
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Informations importantes</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Première connexion :</span><br>
                        <span class="value">Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe dès votre première connexion.</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Besoin d'aide ?</span><br>
                        <span class="value">Si vous avez des questions ou rencontrez des difficultés, n'hésitez pas à contacter notre équipe support.</span>
                    </div>
                </div>
            </div>

            <div class="button-container">
                <a href="https://app.aeropaperasse.fr/login" class="button">Se connecter à Aéropaperasse</a>
            </div>
        </div>

        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>
