<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande d'activité</title>
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
        .document-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .document-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
        }
        .document-list li:before {
            content: "📎";
            margin-right: 8px;
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
            <h1>Nouvelle demande d'activité reçue</h1>
        </div>
        
        <div class="content">
            <p>Une nouvelle demande d'activité vient d'être soumise. Voici les détails :</p>
            
            <div class="info-section">
                <div class="section-title">Informations de l'entreprise</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Raison sociale :</span><br>
                        <span class="value">{{ $raison_sociale }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Nom commercial :</span><br>
                        <span class="value">{{ $nom_commercial }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">SIRET :</span><br>
                        <span class="value">{{ $activityRequest->siret }}</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Contact responsable</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Nom complet :</span><br>
                        <span class="value">{{ $responsable_prenom }} {{ $responsable_nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Fonction :</span><br>
                        <span class="value">{{ $activityRequest->responsable_fonction }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email :</span><br>
                        <span class="value">{{ $activityRequest->responsable_email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Téléphone :</span><br>
                        <span class="value">{{ $activityRequest->responsable_telephone }}</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Documents fournis</div>
                <div class="info-box">
                    <ul class="document-list">
                        <li>Extrait K-bis</li>
                        <li>Attestations clients</li>
                        <li>Formulaire sûreté</li>
                        @if($activityRequest->agrement_prefectoral_path)
                            <li>Agrément préfectoral</li>
                        @endif
                        @if($activityRequest->contrat_iata_path)
                            <li>Contrat IATA</li>
                        @endif
                        @if($activityRequest->cta_path)
                            <li>CTA</li>
                        @endif
                    </ul>
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