<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification demande de badge</title>
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
        .status-change {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 15px;
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
            <img src="https://app.aeropaperasse.fr/images/aeropaperasse-logo-white.png" alt="Logo REM Distribution" class="logo">
            <h1>Notification - Demande de Badge</h1>
        </div>
        
        <div class="content">
            <p>Une demande de badge a été {{ $action }}.</p>
            
            @if(isset($previous_status) && isset($current_status))
            <div class="status-change">
                <p>Changement de statut :</p>
                <p><strong>Ancien statut :</strong> {{ $previous_status }}</p>
                <p><strong>Nouveau statut :</strong> {{ $current_status }}</p>
            </div>
            @endif
            
            <div class="info-section">
                <div class="section-title">Informations du demandeur</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Nom :</span><br>
                        <span class="value">{{ $badgeRequest->nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Prénom :</span><br>
                        <span class="value">{{ $badgeRequest->prenom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email :</span><br>
                        <span class="value">{{ $badgeRequest->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Téléphone :</span><br>
                        <span class="value">{{ $badgeRequest->telephone }}</span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>