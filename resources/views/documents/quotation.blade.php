<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quote->quote_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
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
            height: 100%;
            margin: 0;
        }
        body {
            font-family: "Montserrat", Helvetica, Arial, sans-serif;
            background-image: url("images/newbg.png");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            font-size: 12px;
        }
        .page {
            margin: 50;
            padding-top: 60px;
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
            font-family: "Montserrat", Helvetica, Arial, sans-serif;
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
            text-align: right;
            font-weight: 700;
            letter-spacing: 1.8px;
            color: var(--accent);
            font-size: 36px;
            font-family: "Montserrat", Helvetica, Arial, sans-serif;
            padding-bottom: 30px;
        }
        .doc-meta {
            color: var(--muted);
            text-align: left;
            line-height: 1.6;
        }
        .doc-meta strong {
            color: var(--ink);
        }
        .section {
            margin-top: 20px;
        }
        .section-title {
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1.2px;
            color: var(--ink);
            margin-bottom: 8px;
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
        .item-title {
            color: var(--ink);
        }
        .item-sub {
            color: var(--muted);
            margin-top: 4px;
        }
        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 220px;
            border: 1px solid var(--border);
            padding: 12px 16px;
        }
        .totals-row {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .totals-row .col1,
        .totals-row .col2 {
            display: table-cell;
            padding: 6px 0;
        }
        .totals-row .col1 {
            color: var(--muted);
            text-align: left;
        }
        .totals-row .col2 {
            color: var(--ink);
            text-align: right;
        }
        .terms {
            margin-top: 20px;
        }
        .terms-title {
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--ink);
            margin-bottom: 8px;
            font-size: 13px;
        }
        .terms-content {
            font-size: 11px;
            line-height: 1;
            color: var(--muted);
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
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            color: var(--muted);
            padding-left: 60px;
            padding-right: 60px;
            padding-top: 10px;
            padding-bottom: 170px
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
    {{-- <header class="header">
        <div class="logo-block">
            <img src="images/crowlogo.png" alt="{{ $companyName }} logo">
        </div>
    </header> --}}

    <section class="title-block">
        <div class="doc-title">Quotation</div>
        <div class="doc-meta">
            <div><strong>Quote # : </strong>{{ $quote->quote_no }}</div>
            <div><strong>Date :</strong>{{ $issuedDate?->format('d M Y') }}</div>
            @if($quote->valid_until)
                <div><strong>Valid Until :</strong>{{ $quote->valid_until?->format('d M Y') }}</div>
            @endif
            <div><strong>Status : </strong>{{ ucfirst($quote->status) }}</div>
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
        <div class="section-title">Items</div>
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
            @forelse($quote->items as $item)
                <tr>
                    <td>
                        <div class="item-title">{{ $item->product_name }}</div>
                        @if(!empty($item->description))
                            <div class="item-sub">{{ $item->description }}</div>
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

        <div class="totals">
            <div class="totals-row">
                <div class="col1">Subtotal</div>
                <div class="col2">LKR {{ number_format((float) $quote->subtotal, 2) }}</div>
            </div>
            <div class="totals-row">
                <div class="col1">Discount</div>
                <div class="col2">LKR {{ number_format((float) $quote->discount, 2) }}</div>
            </div>
            <div class="totals-row">
                <div class="col1">Total</div>
                <div class="col2">LKR {{ number_format((float) $quote->total, 2) }}</div>
            </div>
        </div>
    </section>

    @if($quote->termsAndConditions->count() > 0)
    <section class="terms">
        <div class="terms-title">Terms & Conditions</div>
        <div class="terms-content">
            @foreach($quote->termsAndConditions as $index => $term)
                <p style="@if($term->parent_id) padding-left: 20px; @endif">{{ $term->number ? $term->number . '. ' : '' }}{{ strip_tags($term->content) }}</p>
            @endforeach
        </div>
    </section>
    @endif

    <div class="footer" style="position: fixed; bottom: 10px; left: 0; right: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 24px;">
            <div style="text-align: right;">
                <div style="border-top: 1px solid var(--border); padding-top: 6px; display: inline-block; min-width: 200px; text-align: center; color: var(--ink); font-size: 11px;">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
