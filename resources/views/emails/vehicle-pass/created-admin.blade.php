<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande de laisser-passer véhicule reçue</title>
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
            max-width: 150px;
            margin-bottom: 20px;
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
            background-color: #1f2936;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 600;
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
            <img src="https://app.aeropaperasse.fr/images/aeropaperasse-logo-white.png" alt="Logo Aéropaperasse" class="logo">
            <h1>Nouvelle demande de laisser-passer véhicule reçue</h1>
        </div>

        <div class="content">
            <p>Une nouvelle demande de laisser-passer véhicule vient d'être soumise. Voici les détails :</p>

            <div class="info-section">
                <div class="section-title">Informations du véhicule</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Immatriculation :</span><br>
                        <span class="value">{{ $immatriculation }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Marque :</span><br>
                        <span class="value">{{ $marque_vehicule }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Aéroport :</span><br>
                        <span class="value">{{ $aeroport }}</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Entreprise</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Nom de l'entreprise :</span><br>
                        <span class="value">{{ $nom_entreprise }}</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Demandeur</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Nom :</span><br>
                        <span class="value">{{ $user_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email :</span><br>
                        <span class="value">{{ $user_email }}</span>
                    </div>
                </div>
            </div>

            <p>Vous pouvez consulter et traiter cette demande dans l'interface d'administration.</p>
        </div>

        <div class="footer">
            <p>© 2025 Groupe REM. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>
