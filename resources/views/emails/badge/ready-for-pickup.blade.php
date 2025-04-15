<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre badge est prêt à être remis</title>
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
        .schedule {
            margin: 10px 0;
            border-collapse: collapse;
            width: 100%;
        }
        .schedule tr td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .schedule tr:last-child td {
            border-bottom: none;
        }
        .day {
            font-weight: bold;
        }
        .hours {
            text-align: right;
        }
        .closed {
            color: #9ca3af;
        }
        .open {
            font-weight: bold;
        }
        .warning {
            border-left: 4px solid #f59e0b;
            padding-left: 15px;
            margin: 20px 0;
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
            <h1>Votre badge est prêt à être remis</h1>
        </div>
        
        <div class="content">
            <p><strong>Bonjour {{ $nom }},</strong></p>
            
            <p>Votre badge est maintenant <span class="highlight">prêt à être remis</span>. Vous pouvez venir le récupérer au BLS :</p>
            
            <div class="info-box">
                <p><strong>Adresse du BLS:</strong> 5740 Rue de l'Archet Bâtiment, 95700 Roissy-en-France</p>
                
                <p><strong>Horaires du BLS:</strong></p>
                
                <table class="schedule">
                    <tr>
                        <td class="day open">lundi</td>
                        <td class="hours open">08:30-12:30</td>
                    </tr>
                    <tr>
                        <td class="day open">mardi</td>
                        <td class="hours open">08:30-12:30</td>
                    </tr>
                    <tr>
                        <td class="day">mercredi</td>
                        <td class="hours">08:30-12:30</td>
                    </tr>
                    <tr>
                        <td class="day">jeudi</td>
                        <td class="hours">08:30-12:30</td>
                    </tr>
                    <tr>
                        <td class="day">vendredi</td>
                        <td class="hours">08:30-12:30</td>
                    </tr>
                    <tr>
                        <td class="day closed">samedi</td>
                        <td class="hours closed">Fermé</td>
                    </tr>
                    <tr>
                        <td class="day closed">dimanche</td>
                        <td class="hours closed">Fermé</td>
                    </tr>
                </table>
            </div>
            
            <p>Merci de vous munir d'une pièce d'identité lors du retrait.</p>
            
            <div class="warning">
                <p>Attention, un badge est personnel et ne peut être remis uniquement à la personne concernée.</p>
            </div>
            
            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            
           
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>