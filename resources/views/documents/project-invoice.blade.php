<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_no }}</title>
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
            letter-spacing: 1.2px;
            font-weight: bold;
            color: var(--ink);
            margin-bottom: 8px;
            font-size: 13px;
        }
        .panel {
            border: 1px solid var(--border);
            padding: 16px;
        }
        .client-name {
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
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            color: var(--muted);
            padding-left: 70px;
            padding-right: 70px;
            padding-top: 10px;
            padding-bottom: 170px
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
<img src="images/bg.png" alt="Background" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1;">
@php
    $companyName = $company['name'] ?? config('app.name', 'Crow.lk');
    $bank = $company['bank'] ?? [];
    $project = $invoice->project;
    $lead = $project?->client?->lead ?? $project?->lead;
    $paidAmount = $invoice->paidAmount();
    $balanceDue = $invoice->balanceDue();
@endphp
<div class="page">
    <section class="title-block">
        <div class="doc-title">Invoice</div>
        <div class="doc-meta">
            <div><strong>Invoice No :</strong> {{ $invoice->invoice_no }}</div>
            <div><strong>Project :</strong> {{ $project?->name ?? '-' }}</div>
            <div><strong>Invoice Month :</strong> {{ $invoice->invoice_month?->format('F Y') ?? '-' }}</div>
            <div><strong>Issued :</strong> {{ $invoice->issued_date?->format('d M Y') ?? '-' }}</div>
            <div><strong>Due :</strong> {{ $invoice->due_date?->format('d M Y') ?? '-' }}</div>
            <div><strong>Status :</strong> {{ ucfirst($invoice->status) }}</div>
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
        <div class="section-title">Completed Tasks</div>
        <table>
            <thead>
            <tr>
                <th>Task</th>
                <th>Completed</th>
                <th class="num">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        {{ $item->description }}
                        @if($item->task?->github_task_url)
                            <br><span style="color: var(--muted); font-size: 11px;">{{ $item->task->github_task_url }}</span>
                        @endif
                    </td>
                    <td>{{ $item->task?->completed_at?->format('d M Y') ?? '-' }}</td>
                    <td class="num">LKR {{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <div class="col1">Total</div>
                <div class="col2">LKR {{ number_format((float) $invoice->amount, 2) }}</div>
            </div>
            <div class="totals-row">
                <div class="col1">Paid</div>
                <div class="col2">LKR {{ number_format($paidAmount, 2) }}</div>
            </div>
            <div class="totals-row">
                <div class="col1">Balance</div>
                <div class="col2">LKR {{ number_format($balanceDue, 2) }}</div>
            </div>
        </div>
    </section>

    @if($invoice->payments->isNotEmpty())
        <section class="section">
            <div class="section-title">Payments</div>
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th class="num">Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_date?->format('d M Y') }}</td>
                        <td>{{ $payment->method ?: '-' }}</td>
                        <td class="num">LKR {{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <section class="section">
        <div class="section-title">Payment Details</div>
        <div class="panel">
            <strong>Account Number:</strong> {{ $bank['account_number'] ?? '007010350044' }}<br>
            <strong>Account Name:</strong> {{ $bank['account_name'] ?? 'Crow.lk Pvt Ltd' }}<br>
            <strong>Bank:</strong> {{ $bank['bank'] ?? 'HNB' }}<br>
            <strong>Branch:</strong> {{ $bank['branch'] ?? 'Pettah' }}<br>
            <strong>SWIFT Code:</strong> {{ $bank['swift_code'] ?? 'HBLILKLX' }}
        </div>
    </section>

    @if(!empty($invoice->notes))
        <section class="section">
            <div class="section-title">Notes</div>
            <div style="color: var(--ink); line-height: 1.6; position: relative; padding-left: 20px;">
                <span style="position: absolute; left: 0; top: 8px; width: 6px; height: 6px; border-radius: 50%; background-color: var(--accent);"></span>
                {{ $invoice->notes }}
            </div>
        </section>
    @endif

    <div class="footer" style="position: fixed; bottom: -80px; left: 0; right: 0;">
        <div style="text-align: center; font-size: 13px; color: var(--ink); line-height: 1.6;">
            This invoice has been generated electronically and is valid without a signature.
        </div>
    </div>
</div>
</body>
</html>
