<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quote->quote_no }}</title>
    <style>
        :root {
            --ink: #1b1e27;
            --muted: #5f6673;
            --accent: #2b3752;
            --border: #d7dbe2;
            --paper: #ffffff;
            --bg: #f3f5f7;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }
        .page {
            max-width: 980px;
            margin: 32px auto;
            background: var(--paper);
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 30px rgba(27, 30, 39, 0.08);
            padding: 40px 52px 48px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 16px;
        }
        .logo-block {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .logo-block img {
            width: 72px;
            height: auto;
        }
        .company-name {
            letter-spacing: 0.4px;
        }
        .company-tagline {
            font-size: 11px;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: var(--muted);
            margin-top: 4px;
        }
        .company-contact {
            font-size: 12px;
            line-height: 1.5;
            text-align: right;
            color: var(--muted);
        }
        .title-block {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-end;
            margin-top: 28px;
        }
        .doc-title {
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: var(--accent);
        }
        .doc-meta {
            color: var(--muted);
            text-align: right;
            line-height: 1.6;
        }
        .doc-meta span {
            display: inline-block;
            min-width: 88px;
            color: var(--ink);
            font-weight: 600;
        }
        .section {
            margin-top: 28px;
        }
        .section-title {
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .panel {
            border: 1px solid var(--border);
            padding: 16px;
        }
        .client-name {
            font-weight: 700;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
        th {
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--muted);
            text-align: left;
        }
        td.num,
        th.num {
            text-align: right;
            white-space: nowrap;
        }
        .item-title {
            font-weight: 600;
        }
        .item-sub {
            color: var(--muted);
            margin-top: 4px;
        }
        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 320px;
            border: 1px solid var(--border);
            padding: 12px 16px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }
        .totals .row strong {
            color: var(--ink);
        }
        .footer {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            color: var(--muted);
        }
        .signature {
            margin-top: 32px;
            text-align: right;
            color: var(--muted);
        }
        .signature-line {
            margin-top: 32px;
            border-top: 1px solid var(--border);
            padding-top: 6px;
            display: inline-block;
            min-width: 200px;
            text-align: center;
            color: var(--ink);
        }
        @media print {
            body {
                background: #ffffff;
            }
            .page {
                margin: 0;
                box-shadow: none;
                border: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
@php
    $companyName = $company['name'] ?? config('app.name', 'Crow.lk');
    $issuedDate = $quote->created_at ?? now();
    $lead = $quote->lead;
@endphp
<div class="page">
    <header class="header">
        <div class="logo-block">
            <img src="{{ asset('images/crowlogo.png') }}" alt="{{ $companyName }} logo">
            <div>
                <div class="company-name h2">{{ $companyName }}</div>
                @if(!empty($company['tagline']))
                    <div class="company-tagline text-sm">{{ $company['tagline'] }}</div>
                @endif
            </div>
        </div>
        <div class="company-contact text-sm">
            @if(!empty($company['address']))
                <div>{{ $company['address'] }}</div>
            @endif
            @if(!empty($company['phone']))
                <div>Tel: {{ $company['phone'] }}</div>
            @endif
            @if(!empty($company['email']))
                <div>Email: {{ $company['email'] }}</div>
            @endif
            @if(!empty($company['website']))
                <div>{{ $company['website'] }}</div>
            @endif
            @if(!empty($company['tax_id']))
                <div>Tax ID: {{ $company['tax_id'] }}</div>
            @endif
        </div>
    </header>

    <section class="title-block">
        <div class="doc-title h1">Quotation</div>
        <div class="doc-meta text-sm">
            <div><span>Quote #</span>{{ $quote->quote_no }}</div>
            <div><span>Date</span>{{ $issuedDate?->format('d M Y') }}</div>
            @if($quote->valid_until)
                <div><span>Valid Until</span>{{ $quote->valid_until?->format('d M Y') }}</div>
            @endif
            <div><span>Status</span>{{ ucfirst($quote->status) }}</div>
        </div>
    </section>

    <section class="section">
        <div class="section-title h3">Prepared For</div>
        <div class="panel text">
            <div class="client-name h3">{{ $lead?->name ?? 'N/A' }}</div>
            @if(!empty($lead?->company))
                <div>{{ $lead->company }}</div>
            @endif
            @if(!empty($lead?->email))
                <div>{{ $lead->email }}</div>
            @endif
            @if(!empty($lead?->phone))
                <div>{{ $lead->phone }}</div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="section-title h3">Items</div>
        <table class="text">
            <thead>
            <tr>
                <th>Description</th>
                <th class="num text-sm">Qty</th>
                <th class="num text-sm">Unit Price</th>
                <th class="num text-sm">Line Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($quote->items as $item)
                <tr>
                    <td>
                        <div class="item-title h3">{{ $item->product_name }}</div>
                        @if(!empty($item->description))
                            <div class="item-sub text-sm">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="num">{{ $item->qty }}</td>
                    <td class="num">LKR {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="num">LKR {{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No items added yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="totals text">
            <div class="row"><span>Subtotal</span><strong>LKR {{ number_format((float) $quote->subtotal, 2) }}</strong></div>
            <div class="row"><span>Discount</span><strong>LKR {{ number_format((float) $quote->discount, 2) }}</strong></div>
            <div class="row"><span>Total</span><strong>LKR {{ number_format((float) $quote->total, 2) }}</strong></div>
        </div>
    </section>

    <div class="footer text-sm">
        <div class="text-sm">
            We appreciate the opportunity to work with you.
            @if(!empty($company['email']))
                Please reach us at {{ $company['email'] }} for any adjustments.
            @endif
        </div>
        <div class="signature text-sm">
            <div class="signature-line text-sm">Authorized Signature</div>
        </div>
    </div>
</div>
</body>
</html>
        .h1 { font-size: 16px; font-weight: 700; }
        .h2 { font-size: 14px; font-weight: 600; }
        .h3 { font-size: 13px; font-weight: 600; }
        .text { font-size: 12px; }
        .text-sm { font-size: 11px; }
        .note { font-size: 10px; }
