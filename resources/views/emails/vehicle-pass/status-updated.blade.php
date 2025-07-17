<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de votre demande de laisser-passer véhicule</title>
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
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .status-box {
            background-color: #f3f4f6;
            border-left: 4px solid #1f2936;
            padding: 15px;
            margin: 20px 0;
        }
        .status-approved {
            background-color: #ecfdf5;
            border-left-color: #10b981;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
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
            <h1>Mise à jour de votre demande de laisser-passer véhicule</h1>
        </div>

        <div class="content">
            <p><strong>Bonjour {{ $vehiclePass->user->name }},</strong></p>

            <p>Nous vous informons que le statut de votre demande de laisser-passer véhicule a été mis à jour.</p>

            @if($current_status === 'approved')
                <div class="status-box status-approved">
                    <p><strong>✅ Félicitations ! Votre demande a été approuvée.</strong></p>
                    <p><strong>Statut actuel :</strong> {{ $status_label }}</p>
                    @if($approved_at)
                        <p><strong>Date d'approbation :</strong> {{ \Carbon\Carbon::parse($approved_at)->format('d/m/Y à H:i') }}</p>
                    @endif
                </div>
                <p>Votre laisser-passer véhicule est maintenant validé. Vous pouvez l'utiliser conformément aux conditions d'accès de l'aéroport.</p>
            @elseif($current_status === 'rejected')
                <div class="status-box status-rejected">
                    <p><strong>❌ Votre demande a été refusée.</strong></p>
                    <p><strong>Statut actuel :</strong> {{ $status_label }}</p>
                    @if($rejected_at)
                        <p><strong>Date de refus :</strong> {{ \Carbon\Carbon::parse($rejected_at)->format('d/m/Y à H:i') }}</p>
                    @endif
                </div>
                <p>Si vous souhaitez comprendre les raisons du refus ou soumettre une nouvelle demande, n'hésitez pas à nous contacter.</p>
            @else
                <div class="status-box">
                    <p><strong>📋 Le statut de votre demande a été mis à jour : {{ $status_label }}</strong></p>
                </div>
            @endif

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
            </div>

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
