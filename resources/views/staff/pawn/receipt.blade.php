<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Pawn Ticket #{{ str_pad($pawn->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* 1. PDF SETUP */
        @page { margin: 20px; size: A4 portrait; }
        body {
            /* DejaVu Sans is REQUIRED for the Peso Sign (₱) to work */
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* 2. LAYOUT UTILITIES */
        .w-100 { width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-red { color: #cc0000; }
        .clearfix:after { content: ""; display: table; clear: both; }

        /* 3. TICKET CONTAINER (Double Border Frame) */
        .ticket-box {
            border: 3px double #000;
            padding: 15px;
            height: 480px; /* Approx half-sheet height */
            position: relative;
        }

        /* 4. HEADER */
        .header-table { width: 100%; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .company-name { font-size: 18px; font-weight: 900; text-transform: uppercase; margin: 0; }
        .company-info { font-size: 9px; line-height: 1.2; }
        .serial-box { 
            border: 1px solid #000; 
            padding: 5px; 
            text-align: center; 
            background: #f0f0f0;
        }
        .serial-num { 
            font-size: 18px; 
            font-weight: bold; 
            color: #d00; 
            font-family: 'Courier New', monospace; 
        }

        /* 5. DATES ROW */
        .dates-table { width: 100%; margin-bottom: 15px; }
        .dates-table td { padding: 2px 5px; vertical-align: top; }
        .date-label { font-size: 9px; color: #555; }
        .date-val { font-weight: bold; font-size: 12px; }

        /* 6. INPUT FIELDS (Underlines) */
        .field-value { 
            border-bottom: 1px solid #000; 
            font-weight: bold; 
            font-family: 'Courier New', monospace;
            padding: 0 5px;
            display: inline-block;
        }

        /* 7. MAIN GRID (Description vs Math) */
        .main-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 10px;
        }
        .main-grid th {
            background: #eee;
            border: 1px solid #000;
            padding: 5px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .main-grid td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        /* 8. COMPUTATION LINES */
        .math-row { margin-bottom: 5px; }
        .math-label { display: inline-block; width: 60%; font-size: 10px; }
        .math-val { display: inline-block; width: 35%; text-align: right; font-weight: bold; font-family: 'Courier New', monospace; }
        .math-total { border-top: 1px solid #000; padding-top: 2px; margin-top: 2px; }

        /* 9. FOOTER / SIGNATURES */
        .footer-table { width: 100%; margin-top: 20px; }
        .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; }
        
        /* 10. TERMS (Italicized as requested) */
        .terms-text { 
            font-size: 8px; 
            text-align: justify; 
            margin-top: 10px;
            font-style: italic; /* <--- THIS MAKES IT ITALIC */
            color: #444;
        }
    </style>
</head>
<body>

@php
    // --- Data Preparation Logic ---
    $loanDate     = $pawn->loan_date;
    $maturityDate = $pawn->due_date; 
    $redemptionExpiry = optional($pawn->due_date)->copy()->addMonths(3);

    // Financials
    $principal = (float) $pawn->price;
    $interest  = (float) ($pawn->interest_cost ?? 0)
    $net       = $principal - $interest;

    // Number to Words Converter
    $amountInWords = 'AMOUNT IN WORDS NOT AVAILABLE';
    if (class_exists(\NumberFormatter::class)) {
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $pesos = floor($principal);
        $cents = round(($principal - $pesos) * 100);
        
        $amountInWords = strtoupper($formatter->format($pesos)) . ' PESOS';
        if ($cents > 0) {
            $amountInWords .= ' AND ' . strtoupper($formatter->format($cents)) . ' CENTAVOS';
        }
        $amountInWords .= ' ONLY';
    }
@endphp

<div class="ticket-box">

    {{-- HEADER SECTION --}}
    <table class="header-table">
        <tr>
            <td width="75%" style="vertical-align: top;">
                <h1 class="company-name">AUAG PAWNSHOP & JEWELRY</h1>
                <div class="company-info">
                    123 Main Street, Jewelry District, City Proper<br>
                    Tel. # (02) 8765-4321 • TIN: 123-456-789-000<br>
                    <strong>NON-VAT REG.</strong> | Owned & Operated by: AUAG CORP<br>
                    Business Hours: 8:00am to 6:00pm Mon-Sat
                </div>
            </td>
            <td width="25%" style="vertical-align: top;">
                <div class="serial-box">
                    <div style="font-size: 10px; text-transform: uppercase;">Pawn Ticket No.</div>
                    <div class="serial-num">{{ str_pad($pawn->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DATES ROW --}}
    <table class="dates-table">
        <tr>
            <td width="33%">
                <div class="date-label">Date Loan Granted:</div>
                <div class="date-val">{{ $loanDate?->format('F d, Y') }}</div>
            </td>
            <td width="33%">
                <div class="date-label">Maturity Date:</div>
                <div class="date-val">{{ $maturityDate?->format('F d, Y') }}</div>
            </td>
            <td width="33%">
                <div class="date-label">Redemption Expiry:</div>
                <div class="date-val text-red">{{ $redemptionExpiry?->format('F d, Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- CUSTOMER & AMOUNT SENTENCE --}}
    <div style="margin-bottom: 10px; line-height: 1.8;">
        Received from <span class="field-value" style="width: 250px;">{{ $pawn->customer->name ?? 'Walk-in Customer' }}</span>
        the sum of PESOS:
        <br>
        <span class="field-value" style="width: 100%; font-size: 10px; text-align: center; background: #eee;">
            *** {{ $amountInWords }} ***
        </span>
        <br>
        ( <span style="font-family: 'DejaVu Sans'; font-weight: bold;">&#8369;</span> 
        <span class="field-value" style="width: 100px;">{{ number_format($principal, 2) }}</span> )
        as a loan secured by the article(s) described below.
    </div>

    {{-- MAIN GRID: DESCRIPTION & COMPUTATION --}}
    <table class="main-grid">
        <thead>
            <tr>
                <th width="65%">DESCRIPTION OF PAWN</th>
                <th width="35%">PRINCIPAL & COMPUTATION</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- DESCRIPTION CELL --}}
                <td style="height: 140px;">
                    <div style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">{{ $pawn->title }}</div>
                    <div style="white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 10px;">{{ $pawn->description }}</div>
                    <br><br>
                    <div style="font-size: 9px; color: #666; position: absolute; bottom: 5px;">
                        <em>Appraised by: {{ auth()->user()->name ?? 'Staff' }}</em>
                    </div>
                </td>

                {{-- COMPUTATION CELL --}}
                <td style="background-color: #fcfcfc;">
                    <div class="math-row">
                        <span class="math-label">Principal Loan</span>
                        {{-- USE &#8369; FOR PESO SIGN --}}
                        <span class="math-val">&#8369; {{ number_format($principal, 2) }}</span>
                    </div>
                    <div class="math-row">
                        <span class="math-label">Advance Interest</span>
                        <span class="math-val">{{ $interest > 0 ? number_format($interest, 2) : '-' }}</span>
                    </div>
                    
                    <div class="math-row math-total">
                        <span class="math-label text-bold">NET PROCEEDS</span>
                        <span class="math-val">&#8369; {{ number_format($net, 2) }}</span>
                    </div>

                    <div style="margin-top: 20px; font-size: 9px; border: 1px solid #ccc; padding: 5px; text-align: center;">
                        <strong>Interest Rate:</strong><br>
                        3% Per Month
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- FOOTER / SIGNATURES --}}
    <table class="footer-table">
        <tr>
            <td width="50%" align="center">
                <br><br>
                <div class="sig-line"></div>
                Signature or Thumbmark of Pawner
                <div style="font-size: 9px; margin-top: 2px;">
                    Contact: {{ $pawn->customer->contact_no ?? 'N/A' }}
                </div>
            </td>
            <td width="50%" align="center">
                <br><br>
                <div class="sig-line"></div>
                <strong>Authorized Representative</strong>
                <div style="font-size: 9px; margin-top: 2px;">AUAG Jewelry</div>
            </td>
        </tr>
    </table>

    {{-- TERMS & CONDITIONS (ITALICIZED) --}}
    <div class="terms-text">
        <strong>TERMS AND CONDITIONS:</strong><br>
        1. The pledged article(s) shall be returned to the pawner upon full payment of the principal loan, interest, and charges on or before the redemption expiry date.<br>
        2. Interest is computed monthly. Any fraction of a month is considered a full month.<br>
        3. Failure to redeem the pawn on or before the expiry date authorizes the pawnshop to dispose of the pledged article(s) according to law.<br>
        4. This ticket must be presented for redemption. Lost tickets must be reported immediately.<br>
        5. The pawner attests that the item(s) pledged are their personal property and are free from any liens or encumbrances.
    </div>

</div>

</body>
</html>