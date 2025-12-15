<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>AUAG Jewelry - Official Receipt</title>
    <style>
        /* 1. PAGE SETTINGS */
        @page {
            size: A4;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif; /* Crucial for Peso Sign */
            font-size: 13px;
            color: #333;
            background: #fff;
        }

        /* 2. CONTAINER */
        .page-container {
            /* width: 100%;  <-- REMOVED: This causes overflow with padding */
            padding: 15mm 20mm; 
            box-sizing: border-box;
        }

        /* 3. HELPER CLASSES (Floats) */
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        
        /* 4. TYPOGRAPHY */
        .text-bold { font-weight: bold; }
        .text-gold { color: #c5a059; }
        .serif { font-family: 'Times New Roman', serif; } 
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* 5. HEADER */
        .header {
            border-bottom: 2px solid #c5a059;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-section {
            width: 45%; /* Reduced slightly to prevent wrap */
            float: left;
        }
        .logo-section img {
            height: 80px;
            width: auto;
        }
        .receipt-details {
            width: 45%;
            float: right;
            text-align: right;
        }
        .receipt-title {
            font-size: 32px;
            color: #1a1a1a;
            margin: 0 0 5px 0;
            /* Serif font for title is fine, no special chars here */
        }
        .receipt-no {
            font-size: 14px;
            color: #777;
        }

        /* 6. INFO GRID */
        .info-grid {
            margin-bottom: 30px;
        }
        .info-column {
            float: left;
            width: 33.33%;
            padding-right: 10px;
        }
        .column-title {
            font-size: 11px;
            color: #c5a059;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
            width: 90%;
        }
        .info-content div {
            margin-bottom: 3px;
            line-height: 1.4;
        }

        /* 7. ITEMS TABLE */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            table-layout: fixed; /* Ensures columns stay stable */
        }
        .items-table th {
            background-color: #1a1a1a;
            color: #c5a059;
            text-transform: uppercase;
            font-size: 11px;
            padding: 12px 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .items-table tr:last-child td {
            border-bottom: 2px solid #c5a059;
        }

        /* 8. TOTALS */
        .totals-container {
            margin-top: 10px;
        }
        .notes-section {
            float: left;
            width: 55%;
            font-size: 12px;
            color: #666;
        }
        .notes-box {
            border: 1px dashed #ddd;
            padding: 10px;
            margin-top: 5px;
            min-height: 60px;
            background: #fafafa;
        }
        .calculations-section {
            float: right;
            width: 40%;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }
        .totals-table .total-row td {
            border-top: 2px solid #c5a059;
            padding-top: 10px;
            font-size: 16px;
            color: #c5a059;
            font-weight: bold;
        }

        /* 9. FOOTER */
        .footer {
            position: fixed;
            bottom: 20mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>

<div class="page-container">

    {{-- HEADER --}}
    <div class="header clearfix">
        <div class="logo-section">
            <img src="{{ public_path('no bg.png') }}" alt="Logo">
        </div>
        <div class="receipt-details">
            <h1 class="receipt-title serif">AUAG RECEIPT</h1>
            <div class="receipt-no">Transaction #: <strong>{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
            <div class="receipt-no">Date: {{ $transaction->created_at->format('F d, Y') }}</div>
        </div>
    </div>

    {{-- INFO GRID --}}
    <div class="info-grid clearfix">
        {{-- MERCHANT --}}
        <div class="info-column">
            <div class="column-title">Merchant</div>
            <div class="info-content">
                <div class="text-bold serif" style="font-size: 16px;">AUAG Jewelry</div>
                <div>Rizal, Tuy, Batangas</div>
                <div>Philippines</div>
                <div style="margin-top:5px;">+63 945-406-0982</div>
                <div style="font-size: 11px;">auagjewerlyaccessories@gmail.com</div>
            </div>
        </div>

        {{-- CUSTOMER --}}
        <div class="info-column">
            <div class="column-title">Customer</div>
            <div class="info-content">
                @php $c = $transaction->customer; @endphp
                <div class="text-bold serif" style="font-size: 16px;">{{ $c->name ?? 'Walk-in Customer' }}</div>
                <div>{{ $c->contact_no ?? 'N/A' }}</div>
                <div>{{ $c->email ?? '' }}</div>
            </div>
        </div>

        {{-- PAYMENT DETAILS --}}
        <div class="info-column">
            <div class="column-title">Payment Details</div>
            <div class="info-content">
                <div><strong>Status:</strong> <span style="color: green;">PAID</span></div>
                <div><strong>Method:</strong> {{ ucfirst($transaction->payment_method ?? 'Cash') }}</div>
                <div><strong>Cashier:</strong> {{ auth()->user()->name ?? 'Staff' }}</div>
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Item Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Unit Price</th>
                <th style="width: 20%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotal = 0; @endphp
            @foreach($transaction->items as $item)
                @php
                    $line = $item->line_total;
                    $subtotal += $line;
                @endphp
                <tr>
                    <td>
                        <span class="text-bold">{{ $item->product->name ?? 'Item #'.$item->product_id }}</span>
                        @if($item->product->material || $item->product->style)
                            <br><span style="font-size: 10px; color: #777;">
                                {{ $item->product->material ?? '' }} {{ $item->product->style ?? '' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    {{-- USE HTML ENTITY &#8369; FOR PESO SIGN --}}
                    <td class="text-right">&#8369;{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right text-bold">&#8369;{{ number_format($line, 2) }}</td>
                </tr>
            @endforeach
            
            @for($i = 0; $i < max(0, 5 - count($transaction->items)); $i++)
                <tr>
                    <td style="color: white;">.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- TOTALS & NOTES --}}
    <div class="totals-container clearfix">
        <div class="notes-section">
            <div style="font-weight: bold; margin-bottom: 5px;">Remarks:</div>
            <div class="notes-box">
                {{ $transaction->remarks ?? '' }}
            </div>
            <div style="margin-top: 10px; font-size: 10px; font-style: italic;">
                * Items purchased may be exchanged within 7 days.<br>
                * No cash refunds. Authenticity guaranteed.
            </div>
        </div>

        <div class="calculations-section">
            <table class="totals-table">
                @php
                    $discount = $transaction->discount ?? 0;
                    $tax      = $transaction->tax ?? 0;
                    $total    = $subtotal - $discount + $tax;
                    $paid     = $transaction->amount_paid ?? $total;
                    $change   = $paid - $total;
                @endphp
                
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">&#8369;{{ number_format($subtotal, 2) }}</td>
                </tr>
                
                @if($discount > 0)
                <tr>
                    <td style="color: red;">Discount</td>
                    <td class="text-right" style="color: red;">- &#8369;{{ number_format($discount, 2) }}</td>
                </tr>
                @endif

                <tr class="total-row">
                    <td>TOTAL DUE</td>
                    <td class="text-right">&#8369;{{ number_format($total, 2) }}</td>
                </tr>

                <tr>
                    <td style="padding-top: 10px;">Amount Paid</td>
                    <td class="text-right" style="padding-top: 10px;">&#8369;{{ number_format($paid, 2) }}</td>
                </tr>

                @if($change > 0)
                <tr>
                    <td>Change</td>
                    <td class="text-right">&#8369;{{ number_format($change, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div style="text-align: right; padding-right: 20px; margin-bottom: 20px;">
            <div class="signature-line" style="margin-left: auto; margin-right: 0;"></div>
            <div style="width: 200px; margin-left: auto; text-align: center;">Authorized Signature</div>
        </div>

        <p class="serif text-gold" style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Thank you for choosing AUAG Jewelry</p>
        <p>This document serves as your official proof of purchase.</p>
    </div>

</div>

</body>
</html>