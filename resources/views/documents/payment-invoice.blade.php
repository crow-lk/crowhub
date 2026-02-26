<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $payment->id }}</title>
    <style>
        :root {
            --ink: #1b1e27;
            --muted: #5f6673;
            --accent: #0f4c5c;
            --border: #d7dbe2;
            --paper: #ffffff;
            --bg: #f4f2ee;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: "Palatino Linotype", "Book Antiqua", Palatino, "Times New Roman", serif;
        }
        .page {
            max-width: 960px;
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
            font-size: 22px;
            font-weight: 700;
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
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: var(--accent);
        }
        .doc-meta {
            font-size: 12px;
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
            font-size: 12px;
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
            font-size: 13px;
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
            font-size: 11px;
            color: var(--muted);
            text-align: left;
        }
        td.num,
        th.num {
            text-align: right;
            white-space: nowrap;
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
            font-size: 13px;
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
            font-size: 12px;
            color: var(--muted);
        }
        .signature {
            margin-top: 32px;
            text-align: right;
            font-size: 12px;
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
    $invoiceNo = 'INV-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT);
    $issuedDate = $payment->paid_date ?? $payment->created_at;
    $lead = $payment->lead;
    $quote = $payment->quote;
@endphp
<div class="page">
    <header class="header">
        <div class="logo-block">
            <img src="{{ asset('images/crowlogo.png') }}" alt="{{ $companyName }} logo">
            <div>
                <div class="company-name">{{ $companyName }}</div>
                @if(!empty($company['tagline']))
                    <div class="company-tagline">{{ $company['tagline'] }}</div>
                @endif
            </div>
        </div>
        <div class="company-contact">
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
        <div class="doc-title">Invoice</div>
        <div class="doc-meta">
            <div><span>Invoice #</span>{{ $invoiceNo }}</div>
            <div><span>Date</span>{{ $issuedDate?->format('d M Y') }}</div>
            @if($quote?->quote_no)
                <div><span>Quote #</span>{{ $quote->quote_no }}</div>
            @endif
            <div><span>Type</span>{{ ucfirst($payment->type ?? 'Payment') }}</div>
            @if(!empty($payment->method))
                <div><span>Method</span>{{ $payment->method }}</div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="section-title">Bill To</div>
        <div class="panel">
            <div class="client-name">{{ $lead?->name ?? 'N/A' }}</div>
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
        <div class="section-title">Payment Summary</div>
        <table>
            <thead>
            <tr>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Amount</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Payment for {{ $quote?->quote_no ? 'Quote #' . $quote->quote_no : 'project services' }}
                </td>
                <td class="num">1</td>
                <td class="num">LKR {{ number_format((float) $payment->amount, 2) }}</td>
                <td class="num">LKR {{ number_format((float) $payment->amount, 2) }}</td>
            </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span>Amount Paid</span><strong>LKR {{ number_format((float) $payment->amount, 2) }}</strong></div>
        </div>
    </section>

    @if(!empty($payment->note))
        <section class="section">
            <div class="section-title">Notes</div>
            <div class="panel">{{ $payment->note }}</div>
        </section>
    @endif

    <div class="footer">
        <div>
            Thank you for your business.
            @if(!empty($company['email']))
                For questions, contact {{ $company['email'] }}.
            @endif
        </div>
        <div class="signature">
            <div class="signature-line">Authorized Signature</div>
        </div>
    </div>
</div>
</body>
</html>
