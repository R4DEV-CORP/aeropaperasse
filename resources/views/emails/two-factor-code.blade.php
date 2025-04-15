<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification</title>
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
            text-align: center;
        }
        .code-box {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #1f2936;
            font-family: monospace;
        }
        .timer {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 8px 16px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 20px;
            font-size: 14px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 6px;
            font-size: 14px;
            color: #4b5563;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .code-box {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://app.aeropaperasse.fr/images/aeropaperasse-logo-white.png" alt="Logo" class="logo">
            <h1>Code de vérification</h1>
        </div>
        
        <div class="content">
            <p>Voici votre code de vérification :</p>
            
            <div class="code-box">
                {{ $code }}
            </div>

            <div class="timer">
                ⏰ Ce code expirera dans 10 minutes
            </div>

            <div class="info">
                Si vous n'avez pas demandé ce code, veuillez ignorer cet email et contacter le support immédiatement.
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>