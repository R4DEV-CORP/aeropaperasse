<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre badge a été remis</title>
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
        .highlight {
            color: #1f2936;
            font-weight: bold;
        }
        .info-box {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-box {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
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
            <h1>Votre badge a été remis</h1>
        </div>

        <div class="content">
            <p><strong>Bonjour {{ $name }},</strong></p>

            <div class="success-box">
                <p style="margin: 0;"><strong>✅ Confirmation de remise</strong></p>
            </div>

            <p>Nous vous confirmons que le badge d'accès (TCA) a été remis au collaborateur <span class="highlight">{{ $coworker->firstname }} {{ $coworker->lastname }}</span> le <span class="highlight">{{ $deliveredAt->format('d/m/Y à H:i') }}</span>.</p>

            <div class="info-box">
                <p style="margin-top: 0;"><strong>Informations du badge :</strong></p>
                <ul style="margin-bottom: 0;">
                    <li><strong>Collaborateur :</strong> {{ $coworker->firstname }} {{ $coworker->lastname }}</li>
                    <li><strong>Date de remise :</strong> {{ $deliveredAt->format('d/m/Y à H:i') }}</li>
                    <li><strong>Statut :</strong> Remis</li>
                </ul>
            </div>

            <p>Le badge est maintenant actif et le collaborateur peut l'utiliser pour accéder aux zones autorisées.</p>

            <p><strong>Rappel important :</strong></p>
            <ul>
                <li>Le badge est strictement personnel (ne jamais le prêter).</li>
                <li>En cas de perte, vol ou dysfonctionnement, prévenez-nous immédiatement.</li>
                <li>Il devra être restitué en cas de fin de mission, de changement de fonction ou sur demande.</li>
            </ul>

            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>

        </div>

        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>
