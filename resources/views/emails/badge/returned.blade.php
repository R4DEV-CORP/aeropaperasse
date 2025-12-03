<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge restitué</title>
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
        .success {
            color: #1f2936;
            font-weight: bold;
        }
        .document-link {
            display: inline-block;
            margin-top: 10px;
            color: #1f2936;
            text-decoration: underline;
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
            <h1>Badge restitué</h1>
        </div>
        
        <div class="content">
            <p><strong>Bonjour {{ $name }},</strong></p>
            
            <p>Nous confirmons que votre badge a été <span class="success">restitué avec succès</span> le {{ date('d/m/Y', strtotime($returned_at)) }}.</p>
            
            <div class="info-section">
                <div class="section-title">Détails de la restitution</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Titulaire :</span><br>
                        <span class="value">{{ $coworker_firstname }} {{ $coworker_lastname }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Date de restitution :</span><br>
                        <span class="value">{{ date('d/m/Y', strtotime($returned_at)) }}</span>
                    </div>
                </div>
            </div>
            
            <p>Vous n'avez plus aucune responsabilité concernant ce badge. Nous vous remercions de l'avoir restitué en temps et en heure.</p>
            
            <p>Si vous avez besoin d'un nouveau badge à l'avenir, n'hésitez pas à effectuer une nouvelle demande.</p>
            
            <p>Pour toute question complémentaire, n'hésitez pas à nous contacter.</p>
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>