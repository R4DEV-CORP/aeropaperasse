<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre demande de laisser-passer véhicule a été soumise</title>
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
            <h1>Votre demande de laisser-passer véhicule a été soumise</h1>
        </div>

        <div class="content">
            <p><strong>Bonjour {{ $vehiclePass->user->name }},</strong></p>

            <p>Nous vous confirmons que votre demande de laisser-passer véhicule a bien été soumise et est en cours de traitement.</p>

            <div class="info-box">
                <div class="info-item">
                    <span class="label">Immatriculation :</span><br>
                    <span class="value">{{ $immatriculation }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Aéroport :</span><br>
                    <span class="value">{{ $aeroport }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Entreprise :</span><br>
                    <span class="value">{{ $nom_entreprise }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Marque du véhicule :</span><br>
                    <span class="value">{{ $marque_vehicule }}</span>
                </div>
            </div>

            <p>Vous recevrez des notifications par email à chaque étape importante du processus.</p>

            <p>Pour toute question concernant votre demande, n'hésitez pas à nous contacter.</p>

            <p>Cordialement,<br>L'équipe Aéropaperasse</p>
        </div>

        <div class="footer">
            <p>© 2025 Groupe REM. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>
