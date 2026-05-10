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
        .header .meta .quo-number { font-size: 16px; font-weight: bold; color: #1a1a1a; }
        .header .meta .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 4px; }
        .status-draft    { background: #e5e7eb; color: #374151; }
        .status-sent     { background: #dbeafe; color: #1e40af; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-expired  { background: #fef3c7; color: #92400e; }

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
        .type-badge { font-size: 10px; padding: 1px 6px; border-radius: 3px; }
        .badge-labor { background: #dbeafe; color: #1e40af; }
        .badge-part  { background: #ede9fe; color: #4c1d95; }

        /* Totals */
        .totals { float: right; width: 280px; margin-bottom: 32px; }
        .totals table { }
        .totals td { padding: 5px 10px; }
        .totals .label { color: #555; text-align: right; }
        .totals .value { text-align: right; font-weight: 500; }
        .totals .grand .label, .totals .grand .value { font-size: 14px; font-weight: bold; border-top: 1px solid #1a1a1a; padding-top: 8px; margin-top: 4px; }
        .clearfix::after { content: ''; display: block; clear: both; }

        /* Notes / Terms */
        .section { margin-bottom: 20px; }
        .section h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin-bottom: 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .section p { color: #374151; line-height: 1.5; }

        /* Footer */
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <h1>QUOTATION</h1>
            <p style="color:#555; margin-top:4px;">{{ $quotation->workOrder->branch->name ?? config('app.name') }}</p>
        </div>
        <div class="meta">
            <div class="quo-number">{{ $quotation->quotation_number }}</div>
            <div class="status-badge status-{{ $quotation->status }}">{{ strtoupper($quotation->status) }}</div>
            <br/>
            <div>Issued: {{ $quotation->created_at->format('d M Y') }}</div>
            @if($quotation->valid_until)
                <div>Valid until: {{ $quotation->valid_until->format('d M Y') }}</div>
            @endif
            @if($quotation->version > 1)
                <div style="margin-top:4px;">Version {{ $quotation->version }}</div>
            @endif
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties">
        <div class="party">
            <h3>Bill To</h3>
            <p class="name">{{ $quotation->customer->name }}</p>
            <p>{{ $quotation->customer->phone }}</p>
            @if($quotation->customer->email)
                <p>{{ $quotation->customer->email }}</p>
            @endif
        </div>
        <div class="party">
            <h3>Vehicle</h3>
            <p class="name">{{ $quotation->workOrder->vehicle->registration_number }}</p>
            <p>{{ $quotation->workOrder->vehicle->year }} {{ $quotation->workOrder->vehicle->make }} {{ $quotation->workOrder->vehicle->model }}</p>
        </div>
        <div class="party">
            <h3>Work Order</h3>
            <p class="name" style="font-family: monospace;">{{ $quotation->workOrder->order_number }}</p>
            <p>Prepared by: {{ $quotation->createdBy?->name ?? '—' }}</p>
        </div>
    </div>

    {{-- Line items --}}
    <table>
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th>Type</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items->sortBy('sort_order') as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>
                        <span class="type-badge badge-{{ $item->item_type }}">{{ ucfirst($item->item_type) }}</span>
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">UGX {{ number_format($item->unit_price) }}</td>
                    <td class="text-right">{{ $item->discount > 0 ? 'UGX ' . number_format($item->discount) : '—' }}</td>
                    <td class="text-right">{{ $item->vat_applicable ? $item->vat_rate . '%' : '—' }}</td>
                    <td class="text-right">UGX {{ number_format($item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">UGX {{ number_format($quotation->subtotal) }}</td>
            </tr>
            @if($quotation->vat_amount > 0)
                <tr>
                    <td class="label">VAT ({{ $quotation->vat_rate }}%):</td>
                    <td class="value">UGX {{ number_format($quotation->vat_amount) }}</td>
                </tr>
            @endif
            <tr class="grand">
                <td class="label">Total:</td>
                <td class="value">UGX {{ number_format($quotation->total) }}</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>

    {{-- Notes --}}
    @if($quotation->notes)
        <div class="section">
            <h3>Notes</h3>
            <p>{{ $quotation->notes }}</p>
        </div>
    @endif

    {{-- Terms --}}
    @if($quotation->terms_and_conditions)
        <div class="section">
            <h3>Terms & Conditions</h3>
            <p>{{ $quotation->terms_and_conditions }}</p>
        </div>
    @endif

    <div class="footer">
        This quotation is valid until {{ $quotation->valid_until?->format('d M Y') ?? 'further notice' }}.
        Thank you for your business.
    </div>
</div>
</body>
</html>
