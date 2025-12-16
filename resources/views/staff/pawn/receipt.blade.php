<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Pawn Receipt #{{ str_pad($pawn->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* PDF SETUP */
        @page { margin: 20px; size: A4 portrait; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }

        /* UTILITIES */
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-red { color: #cc0000; }
        .uppercase { text-transform: uppercase; }

        /* MAIN CONTAINER */
        .receipt-box {
            border: 3px double #000;
            padding: 20px;
            background-color: #fff;
            position: relative;
        }

        /* HEADER */
        .header-title { font-size: 20px; font-weight: 900; margin: 0; letter-spacing: 1px; }
        .header-sub { font-size: 10px; color: #444; margin-bottom: 10px; }
        
        /* TAG & SERIAL */
        .receipt-tag {
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 5px 10px;
            display: inline-block;
            margin-bottom: 5px;
            background: #eee;
        }
        .serial-num {
            font-size: 16px;
            font-weight: bold;
            color: #cc0000;
            font-family: 'Courier New', monospace;
        }

        /* INFO GRID */
        .info-table { width: 100%; margin-bottom: 20px; border-bottom: 1px dashed #aaa; padding-bottom: 15px; }
        .info-table td { vertical-align: top; padding: 2px 0; }
        .label { color: #555; font-size: 10px; width: 80px; display: inline-block; }

        /* ITEM TABLE */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        .item-table th {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .item-table td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
        }

        /* FINANCIALS */
        .money-row { margin-bottom: 5px; }
        .money-label { display: inline-block; width: 60%; font-size: 10px; }
        
        /* CRITICAL FIX: 
           Courier New is good for numbers, but bad for symbols. 
           We will apply DejaVu Sans specifically to the peso symbol inline below.
        */
        .money-val { 
            display: inline-block; 
            width: 35%; 
            text-align: right; 
            font-weight: bold; 
            font-family: 'Courier New', monospace; 
        }
        
        /* Helper class just for the symbol */
        .php-symbol {
            font-family: 'DejaVu Sans', sans-serif !important;
        }

        .total-box {
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 5px;
            font-size: 14px;
        }

        /* FOOTER / SIGNATURES */
        .footer-table { width: 100%; margin-top: 40px; }
        .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; margin-top: 40px; }

        /* TERMS */
        .terms {
            font-size: 9px;
            color: #555;
            margin-top: 30px;
            text-align: justify;
            font-style: italic;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>

@php
    $principal = $pawn->price;
    $interest  = $pawn->interest_cost ?? 0; 
    $service   = $pawn->service_charge ?? 0;
    $net       = $principal - $interest - $service;
    
    $loanDate = $pawn->created_at; 
    $dueDate  = $pawn->due_date;  
    $expiry   = $pawn->due_date;
@endphp

<div class="receipt-box">

    <table class="w-100">
        <tr>
            <td width="65%">
                <h1 class="header-title">AUAG JEWELRY | PAWNSHOP</h1>
                <div class="header-sub">
                    Rizal, Tuy, Batangas, Philippines<br>
                    Contact: +63 945-406-0982<br>
                    Owned & Operated by: AUAG Jewelry Corp.
                </div>
            </td>
            <td width="35%" class="text-right">
                <div class="receipt-tag">OFFICIAL RECEIPT</div>
                <br>
                <span style="font-size: 10px;">Ref No:</span>
                <span class="serial-num">{{ str_pad($pawn->id, 6, '0', STR_PAD_LEFT) }}</span>
                <br>
                <span style="font-size: 10px;">Date: {{ now()->format('M d, Y h:i A') }}</span>
            </td>
        </tr>
    </table>

    <hr style="margin: 15px 0; border: 0; border-bottom: 2px solid #000;">

    <table class="info-table">
        <tr>
            <td width="55%">
                <div class="text-bold uppercase" style="margin-bottom: 5px; font-size: 11px;">Customer Information</div>
                <div><span class="label">Name:</span> <strong>{{ $pawn->customer->name ?? 'Walk-in Customer' }}</strong></div>
                <div><span class="label">Contact:</span> {{ $pawn->customer->contact_no ?? 'N/A' }}</div>
                <div><span class="label">Email:</span> {{ $pawn->customer->email ?? 'N/A' }}</div>
            </td>
            <td width="45%">
                <div class="text-bold uppercase" style="margin-bottom: 5px; font-size: 11px;">Loan Details</div>
                <div><span class="label">Loan Date:</span> {{ $loanDate ? $loanDate->format('M d, Y') : '-' }}</div>
                <div><span class="label">Maturity:</span> {{ $dueDate ? $dueDate->format('M d, Y') : '-' }}</div>
                <div><span class="label">Expiry:</span> <span class="text-red">{{ $expiry ? $expiry->format('M d, Y') : '-' }}</span></div>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="65%">Description of Pawn</th>
                <th width="35%">Loan Computation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td height="150">
                    <div style="font-weight: bold; margin-bottom: 5px; font-size: 12px;">{{ $pawn->title }}</div>
                    <div style="white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 10px;">{{ $pawn->description }}</div>
                    
                    <div style="margin-top: 20px; font-size: 10px; color: #666;">
                        <em>Status: <strong class="uppercase">{{ $pawn->status }}</strong></em>
                    </div>
                </td>

                <td style="background-color: #fafafa;">
                    <div class="money-row">
                        <span class="money-label">Principal Amount</span>
                        <span class="money-val"><span class="php-symbol">&#8369;</span> {{ number_format($principal, 2) }}</span>
                    </div>
                    <div class="money-row">
                        <span class="money-label">Less: Interest</span>
                        <span class="money-val"><span class="php-symbol">&#8369;</span> {{ number_format($interest, 2) }}</span>
                    </div>
                    <div class="money-row">
                        <span class="money-label">Less: Service Charge</span>
                        <span class="money-val"><span class="php-symbol">&#8369;</span> {{ number_format($service, 2) }}</span>
                    </div>
                    
                    <div class="money-row total-box">
                        <span class="money-label text-bold">NET PROCEEDS</span>
                        <span class="money-val text-bold"><span class="php-symbol">&#8369;</span> {{ number_format($net, 2) }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td width="50%" class="text-center">
                <div class="sig-line"></div>
                <div style="font-size: 10px;">Customer's Signature</div>
            </td>
            <td width="50%" class="text-center">
                <div class="sig-line"></div>
                <div style="font-size: 10px;">Authorized Representative</div>
            </td>
        </tr>
    </table>

    <div class="terms">
        <strong>TERMS AND CONDITIONS:</strong><br>
        1. The pawner hereby accepts the pawnshop's appraisal as proper.<br>
        2. The pledged article(s) shall be returned to the pawner upon full payment of the loan and interest.<br>
        3. Interest rate is 3% per month. Penalty interest may apply for late renewals.<br>
        4. Unredeemed items after the expiry date may be sold at public auction.<br>
        5. This receipt acts as proof of transaction but the original Pawn Ticket is required for redemption.
    </div>

</div>

</body>
</html>