<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour du statut de votre demande d'activité</title>
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
        .status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 10px;
        }
        .status-approved {
            background-color: #10B981;
            color: white;
        }
        .status-rejected {
            background-color: #EF4444;
            color: white;
        }
        .status-pending {
            background-color: #F59E0B;
            color: white;
        }
        /* Suppression du statut 'reviewing' qui n'est pas utilisé */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://app.aeropaperasse.fr/images/aeropaperasse-logo-white.png" alt="Logo" class="logo">
            <h1>Mise à jour du statut de votre demande d'activité</h1>
        </div>
        
        <div class="content">
            <p>Bonjour,</p>
            
            <p>Le statut de votre demande d'activité a été mis à jour.</p>
            
            <div class="info-section">
                <div class="section-title">Statut de la demande</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Nouveau statut :</span><br>
                        @php
                            $statusClass = '';
                            $statusLabel = '';
                            
                            switch($activityRequest->status) {
                                case 'approved':
                                    $statusClass = 'status-approved';
                                    $statusLabel = 'Approuvée';
                                    break;
                                case 'rejected':
                                    $statusClass = 'status-rejected';
                                    $statusLabel = 'Rejetée';
                                    break;
                                case 'pending':
                                    $statusClass = 'status-pending';
                                    $statusLabel = 'En attente';
                                    break;
                                case 'reviewing':
                                    $statusClass = 'status-reviewing';
                                    $statusLabel = 'En cours d\'examen';
                                    break;
                                default:
                                    $statusClass = 'status-pending';
                                    $statusLabel = ucfirst($activityRequest->status);
                            }
                        @endphp
                        <span class="status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Informations de la demande</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">Raison sociale :</span><br>
                        <span class="value">{{ $client->company_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Nom commercial :</span><br>
                        <span class="value">{{ $client->trade_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Date de la demande :</span><br>
                        <span class="value">{{ $activityRequest->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <p>Pour plus de détails ou pour toute question concernant votre demande, n'hésitez pas à nous contacter.</p>
            
            @if($activityRequest->status === 'approved')
            <p><strong>Félicitations !</strong> Votre demande d'activité a été approuvée. Des instructions supplémentaires vous seront communiquées prochainement.</p>
            @elseif($activityRequest->status === 'rejected')
            <p><strong>Nous sommes désolés.</strong> Votre demande d'activité n'a pas pu être approuvée.</p>

            @if(! empty($activityRequest->reject_reason))
            <div class="info-section">
                <div class="section-title">Motif du rejet</div>
                <div class="info-box">
                    <p style="margin: 0; white-space: pre-line; color: #1f2937;">{{ $activityRequest->reject_reason }}</p>
                </div>
            </div>
            @endif

            <p>Pour toute question complémentaire concernant cette décision, n'hésitez pas à nous contacter.</p>
            @endif
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>