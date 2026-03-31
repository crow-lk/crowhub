<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-content {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .order-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .order-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .order-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .order-number {
            font-size: 18px;
            color: #666;
            font-weight: 500;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-weight: 600;
            font-size: 18px;
            border-top: 2px solid #333;
            margin-top: 10px;
        }
        .email-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-content">
            <div class="order-header">
                <div class="order-icon">✅</div>
                <h1 class="order-title">Order Confirmed!</h1>
                <p class="order-number">Order #{{ $order_number ?? 'N/A' }}</p>
            </div>

            <p>Thank you for your purchase. Your order has been confirmed.</p>

            <div class="order-details">
                <div class="order-item">
                    <span>Order Number:</span>
                    <span>{{ $order_number ?? 'N/A' }}</span>
                </div>
                <div class="order-item">
                    <span>Date:</span>
                    <span>{{ $order_date ?? now()->format('Y-m-d') }}</span>
                </div>
                <div class="order-item">
                    <span>Items:</span>
                    <span>{{ $item_count ?? 1 }}</span>
                </div>
                <div class="order-item">
                    <span>Total Amount:</span>
                    <span>{{ $total ?? '$0.00' }}</span>
                </div>
            </div>

            <p>We'll notify you when your order is shipped.</p>

            <div class="email-footer">
                <p>This email was sent via CrowEmail API</p>
            </div>
        </div>
    </div>
</body>
</html>
