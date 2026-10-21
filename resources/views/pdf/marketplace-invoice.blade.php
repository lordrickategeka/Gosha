<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; }
        .page { padding: 40px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; border-bottom: 2px solid #1a1a1a; padding-bottom: 16px; }
        .header h1 { font-size: 28px; font-weight: bold; letter-spacing: -0.5px; }
        .header .meta { text-align: right; font-size: 11px; color: #555; }
        .header .meta .po-number { font-size: 16px; font-weight: bold; color: #1a1a1a; }
        .header .meta .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 4px; }
        .status-unpaid  { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid    { background: #d1fae5; color: #065f46; }

        /* Parties */
        .parties { display: flex; gap: 40px; margin-bottom: 28px; }
        .party { flex: 1; }
        .party h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin-bottom: 6px; }
        .party p { margin-bottom: 2px; }
        .party .name { font-weight: bold; font-size: 13px; }

        /* Items table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; padding: 8px 10px; text-align: left; border-bottom: 1px solid #d1d5db; }
        thead th.text-right { text-align: right; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        tbody td.text-right { text-align: right; }
        tbody tr:last-child td { border-bottom: none; }

        /* Totals */
        .totals { float: right; width: 280px; margin-bottom: 32px; }
        .totals td { padding: 5px 10px; }
        .totals .label { color: #555; text-align: right; }
        .totals .value { text-align: right; font-weight: 500; }
        .totals .grand .label, .totals .grand .value { font-size: 14px; font-weight: bold; border-top: 1px solid #1a1a1a; padding-top: 8px; margin-top: 4px; }
        .clearfix::after { content: ''; display: block; clear: both; }

        /* Footer */
        .section { margin-bottom: 20px; }
        .section h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin-bottom: 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .section p { color: #374151; line-height: 1.5; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <p style="color:#555; margin-top:4px;">{{ $po->supplier?->name ?? config('app.name') }}</p>
        </div>
        <div class="meta">
            <div class="po-number">{{ $po->po_number }}</div>
            <div class="status-badge status-{{ $po->payment_status->value }}">{{ strtoupper($po->payment_status->value) }}</div>
            <br/>
            <div>Issued: {{ $po->created_at->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties">
        <div class="party">
            <h3>From (Supplier)</h3>
            <p class="name">{{ $po->supplier?->name }}</p>
            <p>{{ $po->supplier?->address }}</p>
            @if($po->supplier?->vat_registered)
                <p>VAT No: {{ $po->supplier->vat_number }}</p>
            @endif
        </div>
        <div class="party">
            <h3>Bill To (Buyer)</h3>
            <p class="name">{{ $po->buyer?->name }}</p>
            <p>{{ $po->buyer?->address }}</p>
        </div>
        <div class="party">
            <h3>Purchase Order</h3>
            <p class="name" style="font-family: monospace;">{{ $po->po_number }}</p>
            <p>Status: {{ $po->status->label() }}</p>
        </div>
    </div>

    {{-- Line items --}}
    <table>
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? $item->description }}</td>
                    <td class="text-right">{{ $item->qty_ordered }}</td>
                    <td class="text-right">{{ $po->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->tax_rate > 0 ? $item->tax_rate . '%' : '—' }}</td>
                    <td class="text-right">{{ $po->currency }} {{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">{{ $po->currency }} {{ number_format($po->subtotal, 2) }}</td>
            </tr>
            @if($po->tax_total > 0)
                <tr>
                    <td class="label">Tax:</td>
                    <td class="value">{{ $po->currency }} {{ number_format($po->tax_total, 2) }}</td>
                </tr>
            @endif
            <tr class="grand">
                <td class="label">Total:</td>
                <td class="value">{{ $po->currency }} {{ number_format($po->total, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Paid:</td>
                <td class="value">{{ $po->currency }} {{ number_format($po->amount_paid, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Balance due:</td>
                <td class="value">{{ $po->currency }} {{ number_format($po->total - $po->amount_paid, 2) }}</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>

    @if($po->notes)
        <div class="section">
            <h3>Notes</h3>
            <p>{{ $po->notes }}</p>
        </div>
    @endif

    <div class="footer">
        Thank you for your business.
    </div>
</div>
</body>
</html>
