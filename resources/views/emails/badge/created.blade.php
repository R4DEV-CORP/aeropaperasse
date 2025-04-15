<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau badge créé</title>
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
        .highlight {
            font-size: 24px;
            font-weight: bold;
            color: #1f2936;
            display: block;
            text-align: center;
            margin: 15px 0;
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
            <h1>Nouveau badge créé</h1>
        </div>
        
        <div class="content">
            <p><strong>Bonjour {{ $prenom }} {{ $nom }},</strong></p>
            
            <p>Nous vous informons qu'un badge a été créé avec succès dans le système pour vous.</p>
            
            <div class="info-section">
                <div class="section-title">Informations du badge</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Numéro de badge :</span><br>
                        <span class="value highlight">{{ $badge_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Titulaire :</span><br>
                        <span class="value">{{ $prenom }} {{ $nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Date d'expiration :</span><br>
                        <span class="value">{{ date('d/m/Y', strtotime($expiry_date)) }}</span>
                    </div>
                </div>
            </div>
            
            <p>Ce badge vous permettra d'accéder aux zones autorisées conformément à vos droits d'accès.</p>
            
            <p>Nous vous rappelons que ce badge est strictement personnel et ne doit être utilisé que par vous-même.</p>
            
            <p>Pour toute question concernant votre badge, n'hésitez pas à nous contacter.</p>
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>