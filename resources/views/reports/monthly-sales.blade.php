<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Sales Report</title>
    <style>
        /* ---- Page ---- */
        @page { margin: 28px 28px 40px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .muted { color: #6B7280; }

        /* ---- Brand (Yellow-600) ---- */
        :root {
            --yellow-600: #D97706; /* Tailwind amber-600 (close) */
            --yellow-100: #FEF3C7;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        /* ---- Header ---- */
        .header {
            border: 1px solid var(--gray-200);
            border-left: 6px solid var(--yellow-600);
            padding: 14px 14px 12px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            background: #fff;
        }
        .header-top { display: table; width: 100%; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; width: 38%; }

        .title { font-size: 18px; font-weight: 700; margin: 0 0 3px 0; color: var(--gray-900); }
        .subtitle { margin: 0; font-size: 11px; color: var(--gray-700); }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: var(--yellow-100);
            color: var(--yellow-600);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            border: 1px solid #FDE68A;
        }

        /* ---- KPI Cards ---- */
        .kpi-grid {
            display: table;
            width: 100%;
            border-spacing: 10px;
            margin: 10px -10px 0 -10px;
        }
        .kpi {
            display: table-cell;
            padding: 10px 12px;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            background: var(--gray-50);
            vertical-align: top;
        }
        .kpi-label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #6B7280; margin: 0 0 6px 0; }
        .kpi-value { font-size: 16px; font-weight: 800; margin: 0; color: var(--gray-900); }
        .kpi-note { margin: 6px 0 0 0; font-size: 10px; color: #6B7280; }

        /* ---- Section ---- */
        .section { margin-top: 16px; }
        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 8px 0;
            padding-left: 8px;
            border-left: 4px solid var(--yellow-600);
        }

        /* ---- Tables ---- */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #FFFBEB; /* soft yellow background */
            color: var(--gray-900);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 9px 8px;
            border: 1px solid var(--gray-200);
        }
        tbody td {
            padding: 8px;
            border: 1px solid var(--gray-200);
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: var(--gray-50); }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid var(--gray-200);
            background: #fff;
            font-size: 10px;
            color: var(--gray-700);
        }

        /* ---- Footer ---- */
        .footer {
            position: fixed;
            bottom: 14px;
            left: 28px;
            right: 28px;
            font-size: 10px;
            color: #6B7280;
            border-top: 1px solid var(--gray-200);
            padding-top: 6px;
        }
        .footer-left { float: left; }
        .footer-right { float: right; }

        /* DomPDF page numbers */
        .pagenum:before { content: counter(page); }
        .pagecount:before { content: counter(pages); }

        /* ---- Small helpers ---- */
        .mt-6 { margin-top: 6px; }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="header-top">
        <div class="header-left">
            <p class="title">Monthly Sales Report</p>
            <p class="subtitle">
                Period:
                <span class="pill">{{ $start->format('M d, Y') }}</span>
                <span class="muted">to</span>
                <span class="pill">{{ $end->format('M d, Y') }}</span>
            </p>
            <p class="subtitle mt-6">
                Generated: <span class="nowrap">{{ $generatedAt->format('M d, Y h:i A') }}</span>
                <span class="muted">•</span>
                By: <strong>{{ $generatedBy }}</strong>
            </p>
        </div>
        <div class="header-right">
            <span class="badge">Official Report</span>
            <p class="subtitle mt-6 muted">Currency: PHP (₱)</p>
        </div>
    </div>

    {{-- KPI ROW --}}
    <div class="kpi-grid">
        <div class="kpi">
            <p class="kpi-label">Total Orders</p>
            <p class="kpi-value">{{ number_format((int) ($summary->total_orders ?? 0)) }}</p>
            <p class="kpi-note">Count of Buy transactions</p>
        </div>

        <div class="kpi">
            <p class="kpi-label">Items Sold</p>
            <p class="kpi-value">{{ number_format((int) ($summary->total_qty ?? 0)) }}</p>
            <p class="kpi-note">Sum of quantities</p>
        </div>

        <div class="kpi">
            <p class="kpi-label">Gross Sales</p>
            <p class="kpi-value">₱{{ number_format((float) ($summary->gross_sales ?? 0), 2) }}</p>
            <p class="kpi-note">Sum of line totals</p>
        </div>
    </div>
</div>

{{-- TOP PRODUCTS --}}
<div class="section">
    <p class="section-title">Top Products</p>
    <table>
        <thead>
        <tr>
            <th style="width: 58%;">Product</th>
            <th class="right" style="width: 14%;">Qty</th>
            <th class="right" style="width: 28%;">Sales</th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $row)
            <tr>
                <td>
                    <strong>{{ $row->product_name }}</strong>
                    <div class="muted" style="font-size:10px;">Product ID: #{{ $row->product_id }}</div>
                </td>
                <td class="right">{{ number_format((int) $row->qty_sold) }}</td>
                <td class="right"><strong>₱{{ number_format((float) $row->total_sales, 2) }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No product sales for this month.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- STAFF PERFORMANCE --}}
<div class="section">
    <p class="section-title">Sales by Staff</p>
    <table>
        <thead>
        <tr>
            <th style="width: 58%;">Staff</th>
            <th class="right" style="width: 14%;">Orders</th>
            <th class="right" style="width: 28%;">Sales</th>
        </tr>
        </thead>
        <tbody>
        @forelse($staff as $row)
            <tr>
                <td><strong>{{ $row->staff_name }}</strong></td>
                <td class="right">{{ number_format((int) $row->orders_count) }}</td>
                <td class="right"><strong>₱{{ number_format((float) $row->staff_sales, 2) }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No staff sales for this month.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- FOOTER --}}
<div class="footer">
    <div class="footer-left">
        Generated by Admin Analytics • {{ config('app.name') }}
    </div>
    <div class="footer-right">
        Page <span class="pagenum"></span> of <span class="pagecount"></span>
    </div>
    <div style="clear: both;"></div>
</div>

</body>
</html>
