<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau commentaire sur votre demande d'activité</title>
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
        .comment-box {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #1f2936;
        }
        .comment-author {
            font-weight: 600;
            color: #1f2936;
            margin-bottom: 10px;
        }
        .comment-content {
            color: #374151;
            white-space: pre-wrap;
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
            <img src="https://app.aeropaperasse.fr/images/aeropaperasse-logo-white.png" alt="Logo" class="logo">
            <h1>Nouveau commentaire</h1>
        </div>
        
        <div class="content">
            <p>Bonjour,</p>
            
            <p>Un nouveau commentaire a été ajouté sur votre demande d'activité.</p>
            
            <div class="comment-box">
                <div class="comment-author">
                    {{ $comment_author->name }}
                    @if($comment_author->isAdmin())
                        <span style="color: #dc2626; font-size: 12px;">(Admin)</span>
                    @else
                        <span style="color: #6b7280; font-size: 12px;">({{ $comment_author->client->trade_name ?? '' }})</span>
                    @endif
                </div>
                <div class="comment-content">{{ $comment->content }}</div>
            </div>
            
            <center>
                <a href="{{ config('frontend.url') }}/activity-requests/{{ $activity_request->id }}" class="button">
                    Voir la demande d'activité
                </a>
            </center>
        </div>
        
        <div class="footer">
            <p>© 2025 Rem Distribution. Tous droits réservés.</p>
            <p>Email système - Ne pas répondre</p>
        </div>
    </div>
</body>
</html>
