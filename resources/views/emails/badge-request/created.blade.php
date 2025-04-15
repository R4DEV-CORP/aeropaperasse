<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre demande de badge a été soumise</title>
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
            <h1>Votre demande de badge a été soumise</h1>
        </div>
        
        <div class="content">
            <p><strong>Bonjour {{ $prenom }} {{ $nom }},</strong></p>
            
            <p>Nous vous confirmons que votre demande de badge a bien été soumise et est en cours de traitement.</p>
            
            <p>Vous recevrez des notifications par email à chaque étape importante du processus.</p>
            
           
            
            <p>Pour toute question concernant votre demande, n'hésitez pas à nous contacter.</p>
            
            <p>Cordialement,<br>L'équipe Rem Distribution</p>
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>