<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $payment->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1b1e27;
            --muted: #5f6673;
            --accent: #26303f;
            --accent-light: #1a7f8a;
            --accent-dark: #0a3640;
            --border: #d7dbe2;
            --paper: #ffffff;
            --bg: #f4f2ee;
            --gold: #c9a227;
        }
        * {
            box-sizing: border-box;
        }
        html {
            height: 100%; /* Ensure the body takes the full height of the viewport */
            margin: 0; /* Remove default margin */
        }
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            background-image: url("images/crowld.png");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            font-size: 12px;
        }
        .page {
            margin: 40;
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 12px;
        }
        .logo-block {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-block img {
            margin-top: 20px;
            margin-left: 40px;
            width: 100px;
            height: auto;
        }
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        .company-name {
            letter-spacing: 0.4px;
            font-weight: 700;
            font-size: 22px;
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 50%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            gap: 20px;
            align-items: flex-end;
            margin-top: 24px;
        }
        .doc-title {
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: var(--accent);
            font-size: 20px;
            font-weight: 700;
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            padding-bottom: 10px;
        }
        .doc-meta {
            color: var(--muted);
            text-align: left;
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
            font-weight: 600;
            font-size: 13px;
        }
        .panel {
            border: 1px solid var(--border);
            padding: 16px;
        }
        .client-name {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 14px;
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
            font-size: 11px;
        }
        td.num,
        th.num {
            text-align: right;
            white-space: nowrap;
        }
        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 200px;
            border: 1px solid var(--border);
            padding: 12px 16px;
        }
        .totals .row {
            display: flex;
            text-align: right;
            justify-content: space-between;
            padding: 6px 0;
        }
        .totals .row strong {
            color: var(--ink);
        }
        .terms {
            margin-top: 28px;
        }
        .terms-title {
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--muted);
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
        }
        .terms-content {
            font-size: 11px;
            line-height: 1;
            color: var(--muted);
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            color: var(--muted);
            padding-left: 60px;
            padding-right: 60px;
            padding-top: 10px;
            padding-bottom: 60px
        }
        .signature {
            text-align: right;
            color: var(--muted);
        }
        .signature-line {
            border-top: 1px solid var(--border);
            padding-top: 6px;
            display: inline-block;
            min-width: 200px;
            text-align: center;
            color: var(--ink);
            font-size: 11px;
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
            <img src="images/crowlogo.png" alt="{{ $companyName }} logo">
        </div>
    </header>

    <section class="title-block">
        <div class="doc-title">Invoice</div>
        <div class="doc-meta">
            <div><strong>Invoice No:</strong>{{ $invoiceNo }}</div>
            <div><strong>Date:</strong>{{ $issuedDate?->format('d M Y') }}</div>
            @if($quote?->quote_no)
                <div><strong>Quote :</strong>{{ $quote->quote_no }}</div>
            @endif
            <div><strong>Type:</strong>{{ ucfirst($payment->type ?? 'Payment') }}</div>
            @if(!empty($payment->method))
                <div><strong>Method:</strong>{{ $payment->method }}</div>
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
            <div class="row"><span>Amount Paid :</span><strong> LKR {{ number_format((float) $payment->amount, 2) }}</strong></div>
        </div>
    </section>

    @if(!empty($payment->note))
        <section class="section">
            <div class="section-title">Notes</div>
            <div class="panel">{{ $payment->note }}</div>
        </section>
    @endif

    @if($payment->termsAndConditions->count() > 0)
    <section class="terms">
        <div class="terms-title">Terms & Conditions</div>
        <div class="terms-content">
            @foreach($payment->termsAndConditions as $index => $term)
                <p style="@if($term->parent_id) padding-left: 20px; @endif">{{ $term->number ? $term->number . '. ' : '' }}{{ strip_tags($term->content) }}</p>
            @endforeach
        </div>
    </section>
    @else
    @endif

    <div class="footer" style="position: fixed; bottom: 10px; left: 0; right: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 24px;">
            <div style="flex: 1;">
                Thank you for your business.
                @if(!empty($company['email']))
                    For questions, contact {{ $company['email'] }}.
                @endif
            </div>
            <div style="text-align: right;">
                <div style="border-top: 1px solid var(--border); padding-top: 6px; display: inline-block; min-width: 200px; text-align: center; color: var(--ink); font-size: 11px;">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
