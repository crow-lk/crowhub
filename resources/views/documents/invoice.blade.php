<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1b1e27;
        }
        .page {
            padding: 42px;
        }
        .top {
            display: table;
            width: 100%;
            border-bottom: 2px solid #26303f;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .top > div {
            display: table-cell;
            vertical-align: top;
        }
        .title {
            text-align: right;
            font-size: 30px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .muted {
            color: #5f6673;
        }
        .section {
            margin-top: 20px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 8px;
        }
        .panel {
            border: 1px solid #d7dbe2;
            padding: 14px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th,
        td {
            border-bottom: 1px solid #d7dbe2;
            padding: 9px 10px;
            vertical-align: top;
        }
        th {
            color: #5f6673;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .7px;
        }
        .num {
            text-align: right;
            white-space: nowrap;
        }
        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 280px;
            border: 1px solid #d7dbe2;
            padding: 12px 14px;
        }
        .total-row {
            display: table;
            width: 100%;
            padding: 5px 0;
        }
        .total-row span {
            display: table-cell;
        }
        .total-row span:last-child {
            text-align: right;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            left: 42px;
            right: 42px;
            bottom: 28px;
            text-align: center;
            color: #5f6673;
            font-size: 11px;
        }
    </style>
</head>
<body>
@php
    $companyName = $company['name'] ?? config('app.name', 'Crow.lk');
    $bank = $company['bank'] ?? [];
    $lead = $invoice->client?->lead ?? $invoice->lead;
    $paidAmount = $invoice->paidAmount();
    $balanceDue = $invoice->balanceDue();
@endphp
<div class="page">
    <div class="top">
        <div>
            <strong>{{ $companyName }}</strong><br>
            @if(!empty($company['address']))<span class="muted">{{ $company['address'] }}</span><br>@endif
            @if(!empty($company['email']))<span class="muted">{{ $company['email'] }}</span><br>@endif
            @if(!empty($company['phone']))<span class="muted">{{ $company['phone'] }}</span><br>@endif
            @if(!empty($company['website']))<span class="muted">{{ $company['website'] }}</span>@endif
        </div>
        <div class="title">Invoice</div>
    </div>

    <div class="section">
        <div class="panel">
            <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
            <strong>Job:</strong> {{ $invoice->job?->name ?? '-' }}<br>
            @if($invoice->billing_month)
                <strong>Billing Month:</strong> {{ $invoice->billing_month->format('F Y') }}<br>
            @endif
            <strong>Issued:</strong> {{ $invoice->invoice_date?->format('d M Y') }}<br>
            <strong>Due:</strong> {{ $invoice->due_date?->format('d M Y') }}<br>
            <strong>Status:</strong> {{ str($invoice->status)->replace('_', ' ')->title() }}
            @if($invoice->paid_date)
                <br><strong>Paid:</strong> {{ $invoice->paid_date->format('d M Y') }}
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Bill To</div>
        <div class="panel">
            <strong>{{ $lead?->name ?? 'N/A' }}</strong><br>
            @if(!empty($lead?->company)){{ $lead->company }}<br>@endif
            @if(!empty($lead?->email)){{ $lead->email }}<br>@endif
            @if(!empty($lead?->phone)){{ $lead->phone }}@endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Invoice Items</div>
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
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="num">LKR {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="num">LKR {{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row"><span>Subtotal</span><span>LKR {{ number_format((float) $invoice->subtotal, 2) }}</span></div>
            @if((float) $invoice->discount > 0)
                <div class="total-row"><span>Discount</span><span>LKR {{ number_format((float) $invoice->discount, 2) }}</span></div>
            @endif
            @if((float) $invoice->tax > 0)
                <div class="total-row"><span>Tax</span><span>LKR {{ number_format((float) $invoice->tax, 2) }}</span></div>
            @endif
            <div class="total-row"><span>Total</span><span>LKR {{ number_format((float) $invoice->total, 2) }}</span></div>
            <div class="total-row"><span>Paid</span><span>LKR {{ number_format($paidAmount, 2) }}</span></div>
            <div class="total-row"><span>Balance</span><span>LKR {{ number_format($balanceDue, 2) }}</span></div>
        </div>
    </div>

    @if($invoice->payments->isNotEmpty())
        <div class="section">
            <div class="section-title">Payments</div>
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="num">Amount Paid</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoice->payments as $payment)
                    @php
                        $paymentAmount = (float) $payment->amount_paid > 0
                            ? (float) $payment->amount_paid
                            : (((float) $payment->amount_to_pay === 0.0 && (float) $payment->to_pay === 0.0) ? (float) $payment->amount : 0.0);
                    @endphp
                    <tr>
                        <td>{{ $payment->paid_date?->format('d M Y') }}</td>
                        <td>{{ $payment->method ?: '-' }}</td>
                        <td>{{ $payment->reference_number ?: '-' }}</td>
                        <td class="num">LKR {{ number_format($paymentAmount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Payment Details</div>
        <div class="panel">
            <strong>Account Number:</strong> {{ $bank['account_number'] ?? '007010350044' }}<br>
            <strong>Account Name:</strong> {{ $bank['account_name'] ?? 'Crow.lk Pvt Ltd' }}<br>
            <strong>Bank:</strong> {{ $bank['bank'] ?? 'HNB' }}<br>
            <strong>Branch:</strong> {{ $bank['branch'] ?? 'Pettah' }}<br>
            <strong>SWIFT Code:</strong> {{ $bank['swift_code'] ?? 'HBLILKLX' }}
        </div>
    </div>

    @if(!empty($invoice->notes))
        <div class="section">
            <div class="section-title">Notes</div>
            <div class="panel">{{ $invoice->notes }}</div>
        </div>
    @endif
</div>
<div class="footer">This invoice has been generated electronically and is valid without a signature.</div>
</body>
</html>
