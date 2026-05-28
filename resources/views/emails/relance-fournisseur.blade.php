<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #1E2A52; color: #fff; padding: 20px 30px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.8; }
        .body { padding: 30px; }
        .info-box { background: #f8fafc; border-radius: 6px; padding: 15px; margin: 20px 0; }
        .message-text { white-space: pre-line; margin: 15px 0; }
        .footer { background: #f8fafc; padding: 15px 30px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #e2e8f0; }
        .badge { display: inline-block; background: #f59e0b; color: #fff; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relance — {{ $order->getOrderNumber() }}</h1>
            <p>Universite Sorbonne Paris Nord — IUT de Villetaneuse</p>
        </div>

        <div class="body">
            <p class="message-text">{{ $messageBody }}</p>

            <div class="info-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 8px;"><span style="color: #888; font-size: 12px;">N° Commande</span><br><strong>{{ $order->getOrderNumber() }}</strong></td>
                        <td style="padding: 4px 8px;"><span style="color: #888; font-size: 12px;">Montant TTC</span><br><strong>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} €</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px;"><span style="color: #888; font-size: 12px;">Fournisseur</span><br><strong>{{ $order->supplier?->getCompanyName() ?? '-' }}</strong></td>
                        <td style="padding: 4px 8px;"><span style="color: #888; font-size: 12px;">Statut</span><br><span class="badge">Relance</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            IUT de Villetaneuse — Universite Sorbonne Paris Nord<br>
            Cet email a ete envoye automatiquement depuis le systeme de suivi des commandes.
        </div>
    </div>
</body>
</html>
