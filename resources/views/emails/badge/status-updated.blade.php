<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut du badge modifié</title>
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
        .status {
            font-weight: bold;
        }
        .status-active {
            color: #1f2936;
        }
        .status-expired {
            color: #dc2626;
        }
        .status-returned {
            color: #4b5563;
        }
        .status-suspended {
            color: #f59e0b;
        }
        .status-change {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
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
            <h1>Statut du badge modifié</h1>
        </div>
        
        <div class="content">
            <p><strong>Bonjour {{ $prenom }} {{ $nom }},</strong></p>
            
            <p>Nous vous informons que le statut de votre badge n°{{ $badge_number }} a été modifié.</p>
            
            <div class="status-change">
                <p>Statut précédent : <span class="status">{{ $previous_status }}</span></p>
                <p>↓</p>
                <p>Nouveau statut : 
                    <span class="status 
                        @if($current_status == 'active') status-active 
                        @elseif($current_status == 'expired') status-expired 
                        @elseif($current_status == 'returned') status-returned 
                        @elseif($current_status == 'suspended') status-suspended 
                        @endif">
                        {{ $current_status }}
                    </span>
                </p>
            </div>
            
            <div class="info-section">
                <div class="section-title">Informations du badge</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Numéro de badge :</span><br>
                        <span class="value">{{ $badge_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Titulaire :</span><br>
                        <span class="value">{{ $prenom }} {{ $nom }}</span>
                    </div>
                    @if(isset($expiry_date))
                    <div class="info-item">
                        <span class="label">Date d'expiration :</span><br>
                        <span class="value">{{ date('d/m/Y', strtotime($expiry_date)) }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($current_status == 'active')
                <p>Votre badge est maintenant actif et vous permet d'accéder aux zones autorisées conformément à vos droits d'accès.</p>
            @elseif($current_status == 'expired')
                <p>Votre badge a expiré et ne vous permet plus d'accéder aux zones autorisées. Si vous avez besoin de continuer à accéder à ces zones, veuillez effectuer une demande de renouvellement.</p>
            @elseif($current_status == 'suspended')
                <p>Votre badge a été temporairement suspendu et ne vous permet plus d'accéder aux zones autorisées. Pour plus d'informations sur les raisons de cette suspension, veuillez nous contacter.</p>
            @elseif($current_status == 'returned')
                <p>Ce badge a été enregistré comme restitué. Si vous n'avez pas restitué ce badge vous-même, veuillez nous contacter immédiatement.</p>
            @endif
            
            <p>Pour toute question concernant votre badge, n'hésitez pas à nous contacter.</p>
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>