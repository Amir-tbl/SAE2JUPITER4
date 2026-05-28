<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1E2A52; padding: 40px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #1E2A52; padding-bottom: 15px; }
        .header-left h1 { font-size: 18px; color: #1E2A52; margin-bottom: 4px; }
        .header-left p { color: #666; font-size: 10px; }
        .header-right { text-align: right; }
        .header-right .bc-number { font-size: 16px; font-weight: bold; color: #1E2A52; }
        .header-right .bc-date { font-size: 10px; color: #666; margin-top: 2px; }

        .badge-signe { display: inline-block; background: #10b981; color: #fff; padding: 3px 12px; border-radius: 10px; font-size: 10px; font-weight: bold; letter-spacing: 0.5px; }

        .section { margin-bottom: 20px; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #667eea; margin-bottom: 8px; letter-spacing: 0.5px; }

        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-grid td { padding: 6px 10px; vertical-align: top; }
        .info-grid .label { color: #888; font-size: 9px; text-transform: uppercase; }
        .info-grid .value { font-weight: bold; font-size: 11px; }

        .description-box { background: #fffbea; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; }
        .description-box p { font-size: 10px; }

        .articles-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .articles-table th { background: #f1f5f9; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #555; border-bottom: 2px solid #e2e8f0; }
        .articles-table td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        .articles-table .text-end { text-align: right; }
        .articles-table tfoot td { font-weight: bold; border-top: 2px solid #1E2A52; padding-top: 8px; }

        .totals-row { display: flex; justify-content: flex-end; margin-bottom: 20px; }
        .totals-table { border-collapse: collapse; }
        .totals-table td { padding: 3px 12px; font-size: 10px; }
        .totals-table .total-ttc { font-size: 14px; font-weight: bold; color: #1E2A52; }

        .signature-block { margin-top: 30px; border: 2px solid #c7d2fe; border-radius: 10px; padding: 20px; background: #f8f9ff; page-break-inside: avoid; }
        .signature-block .sig-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #667eea; margin-bottom: 12px; }
        .signature-content { display: flex; align-items: flex-end; }
        .signature-image { max-height: 80px; max-width: 220px; }
        .signature-info { margin-top: 10px; }
        .signature-info .name { font-size: 13px; font-weight: bold; color: #1E2A52; }
        .signature-info .role { font-size: 10px; color: #666; }
        .signature-info .date { font-size: 10px; color: #888; margin-top: 4px; }

        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #aaa; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>

    {{-- En-tête --}}
    <table style="width: 100%; margin-bottom: 25px; border-bottom: 3px solid #1E2A52; padding-bottom: 12px;">
        <tr>
            <td style="vertical-align: top;">
                <div style="font-size: 18px; font-weight: bold; color: #1E2A52;">BON DE COMMANDE</div>
                <div style="font-size: 10px; color: #666;">Université Sorbonne Paris Nord — IUT de Villetaneuse</div>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <div style="font-size: 16px; font-weight: bold; color: #1E2A52;">#{{ $order->getOrderNumber() }}</div>
                <div style="font-size: 10px; color: #666;">{{ $order->created_at->format('d/m/Y') }}</div>
                <div style="margin-top: 6px;"><span class="badge-signe">SIGNÉ</span></div>
            </td>
        </tr>
    </table>

    {{-- Infos commande --}}
    <div class="section">
        <div class="section-title">Informations de la commande</div>
        <table class="info-grid">
            <tr>
                <td>
                    <div class="label">Demandeur</div>
                    <div class="value">{{ $order->author?->getFirstname() }} {{ $order->author?->getLastname() }}</div>
                </td>
                <td>
                    <div class="label">Département</div>
                    <div class="value">{{ $order->department?->getName() ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Fournisseur</div>
                    <div class="value">{{ $order->supplier?->getCompanyName() ?? '-' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Date de commande</div>
                    <div class="value">{{ $order->created_at->format('d/m/Y') }}</div>
                </td>
                <td>
                    <div class="label">Livraison souhaitée</div>
                    <div class="value">{{ $order->desired_delivery_date ? \Carbon\Carbon::parse($order->desired_delivery_date)->format('d/m/Y') : '-' }}</div>
                </td>
                <td>
                    <div class="label">Lieu de livraison</div>
                    <div class="value">{{ $order->delivery_location ?? '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Description --}}
    @if($order->getDescription())
    <div class="description-box">
        <div style="font-size: 9px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Description</div>
        <p>{{ $order->getDescription() }}</p>
    </div>
    @endif

    {{-- Articles --}}
    @if($order->articles->isNotEmpty())
    <div class="section">
        <div class="section-title">Articles commandés</div>
        <table class="articles-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="text-end">Qté</th>
                    <th class="text-end">PU HT</th>
                    <th class="text-end">TVA</th>
                    <th class="text-end">Total TTC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->articles as $article)
                <tr>
                    <td>{{ $article->designation }}</td>
                    <td class="text-end">{{ $article->quantity }}</td>
                    <td class="text-end">{{ number_format($article->unit_price, 2, ',', ' ') }} €</td>
                    <td class="text-end">{{ $article->vat_rate }}%</td>
                    <td class="text-end">{{ number_format($article->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 100%; margin-top: 5px;">
            <tr>
                <td style="font-size: 10px; color: #888;">
                    HT : {{ number_format($order->total_ht ?? 0, 2, ',', ' ') }} € | TVA : {{ number_format($order->total_vat ?? 0, 2, ',', ' ') }} €
                </td>
                <td style="text-align: right; font-size: 14px; font-weight: bold; color: #1E2A52;">
                    Total TTC : {{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} €
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Bloc Signature --}}
    <div class="signature-block">
        <div class="sig-title">Signature du Directeur</div>
        <table style="width: 100%;">
            <tr>
                <td style="width: 240px; vertical-align: bottom;">
                    @if($signatureImageBase64)
                        <img src="data:image/png;base64,{{ $signatureImageBase64 }}" class="signature-image" alt="Signature">
                    @endif
                </td>
                <td style="vertical-align: bottom; padding-left: 20px;">
                    <div class="signature-info">
                        <div class="name">{{ $signer->getFirstName() }} {{ $signer->getLastName() }}</div>
                        <div class="role">Directeur de l'IUT de Villetaneuse</div>
                        <div class="date">Signé le {{ $signedAt->format('d/m/Y à H:i') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Document généré automatiquement — Université Sorbonne Paris Nord — IUT de Villetaneuse — {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
