<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Repair Receipt #{{ str_pad($repair->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* PDF SETUP */
        @page { margin: 20px; size: A4 portrait; }
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Required for Peso Sign */
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }
        
        /* UTILITIES */
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .clearfix:after { content: ""; display: table; clear: both; }

        /* MAIN BOX CONTAINER (Matches Pawn Ticket Style) */
        .receipt-box {
            border: 3px double #000;
            padding: 20px;
            background-color: #fff;
        }

        /* HEADER */
        .header-title { font-size: 20px; font-weight: 900; margin: 0; letter-spacing: 1px; }
        .header-sub { font-size: 10px; color: #444; margin-bottom: 10px; }
        .receipt-tag {
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 5px 10px;
            display: inline-block;
            margin-bottom: 10px;
            background: #eee;
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
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .item-table td {
            border: 1px solid #000;
            padding: 10px;
        }

        /* TOTALS */
        .total-section {
            width: 100%;
            text-align: right;
            margin-bottom: 30px;
        }
        .total-box {
            display: inline-block;
            border: 2px solid #000;
            padding: 10px 20px;
            text-align: right;
            background: #fcfcfc;
        }
        .total-label { font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .total-amount { font-size: 18px; font-weight: 900; color: #000; margin-top: 5px; }

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

<div class="receipt-box">

    <table class="w-100">
        <tr>
            <td width="60%">
                <h1 class="header-title">AUAG JEWELRY | PAWNSHOP</h1>
                <div class="header-sub">
                    Rizal, Tuy, Batangas, Philippines<br>
                    Contact: +63 945-406-0982<br>
                    Owned & Operated by: AUAG Jewelry Corp.
                </div>
            </td>
            <td width="40%" class="text-right">
                <div class="receipt-tag">REPAIR RECEIPT</div>
                <br>
                <strong>Receipt #: {{ str_pad($repair->id, 6, '0', STR_PAD_LEFT) }}</strong><br>
                <span style="font-size: 10px;">Date: {{ $repair->created_at?->format('F d, Y h:i A') }}</span>
            </td>
        </tr>
    </table>

    <hr style="margin: 15px 0; border: 0; border-bottom: 2px solid #000;">

    <table class="info-table">
        <tr>
            <td width="55%">
                <div class="text-bold uppercase" style="margin-bottom: 5px; font-size: 12px;">Customer Details</div>
                <div><span class="label">Name:</span> <strong>{{ $repair->customer?->name ?? 'Walk-in Customer' }}</strong></div>
                <div><span class="label">Contact:</span> {{ $repair->customer?->contact_no ?? 'N/A' }}</div>
                <div><span class="label">Email:</span> {{ $repair->customer?->email ?? 'N/A' }}</div>
            </td>
            <td width="45%">
                <div class="text-bold uppercase" style="margin-bottom: 5px; font-size: 12px;">Job Information</div>
                <div><span class="label">Status:</span> <strong>{{ ucfirst($repair->status) }}</strong></div>
                <div><span class="label">Start Date:</span> {{ $repair->created_at?->format('M d, Y') }}</div>
                <div><span class="label">Est. Completion:</span> {{ $repair->due_date ? $repair->due_date->format('M d, Y') : 'To Be Determined' }}</div>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="75%">Description of Repair / Service Rendered</th>
                <th width="25%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td height="180" style="vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 8px; font-size: 12px;">Repair Service</div>
                    <div style="white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 11px;">{{ $repair->description }}</div>
                    
                    <div style="margin-top: 20px; font-size: 10px; color: #666; padding: 5px; background: #fafafa; border: 1px dashed #ccc;">
                        <strong>Technician Notes:</strong><br>
                        {{ $repair->notes ?? 'No additional notes provided.' }}
                    </div>
                </td>
                <td style="vertical-align: top; text-align: right;">
                    <span style="font-family: 'DejaVu Sans'; font-weight: bold; font-size: 12px;">
                        &#8369; {{ number_format($repair->price, 2) }}
                     </span>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-box">
            <div class="total-label">Total Amount Due</div>
            <span style="font-family: 'DejaVu Sans'; font-weight: bold; font-size: 12px;">
            &#8369;  {{ number_format($repair->price, 2) }}
                     </span>
        </div>
    </div>

    <table class="footer-table">
        <tr>
            <td width="50%" class="text-center">
                <div class="sig-line"></div>
                <div style="font-size: 10px;">Customer's Signature / Acceptance</div>
            </td>
            <td width="50%" class="text-center">
                <div class="sig-line"></div>
                <div style="font-size: 10px;">Authorized Staff / Technician</div>
            </td>
        </tr>
    </table>

    <div class="terms">
        <strong>TERMS AND CONDITIONS:</strong><br>
        1. <strong>Claiming:</strong> This receipt must be presented when claiming the item. If lost, a valid ID and affidavit may be required.<br>
        2. <strong>Warranty:</strong> Repairs carry a 7-day service warranty from the date of release, covering only the specific work done.<br>
        3. <strong>Forfeiture:</strong> Items not claimed within 90 days after notification of completion shall be considered abandoned and may be disposed of by AUAG Jewelry to recoup repair costs.<br>
        4. <strong>Liability:</strong> The shop is not liable for damages caused by unforeseen events (fire, theft, force majeure) beyond our control.
    </div>

</div>

</body>
</html>