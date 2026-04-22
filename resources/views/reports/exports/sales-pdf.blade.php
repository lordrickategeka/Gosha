<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0 0 8px 0; font-size: 20px; }
        .muted { color: #6b7280; }
        .meta { margin-bottom: 16px; }
        .stats { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .stats td { border: 1px solid #e5e7eb; padding: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <div class="meta muted">
        Generated: {{ now()->format('d M Y H:i') }}<br>
        Period: {{ $dateFrom }} to {{ $dateTo }}
    </div>

    <table class="stats">
        <tr>
            <td><strong>Total Invoiced</strong></td>
            <td>UGX {{ number_format($stats['total_invoiced']) }}</td>
            <td><strong>Total Collected</strong></td>
            <td>UGX {{ number_format($stats['total_collected']) }}</td>
        </tr>
        <tr>
            <td><strong>Pending Balance</strong></td>
            <td>UGX {{ number_format($stats['pending']) }}</td>
            <td><strong>Invoice Count</strong></td>
            <td>{{ number_format($stats['invoice_count']) }}</td>
        </tr>
    </table>

    @if($exportType === 'detailed')
        <h3>Invoice Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailedRows as $row)
                    <tr>
                        <td>{{ $row->invoice_number }}</td>
                        <td>{{ optional($row->issue_date)->format('d M Y') ?? optional($row->created_at)->format('d M Y') }}</td>
                        <td>{{ $row->customer?->name ?? 'N/A' }}</td>
                        <td>{{ $row->branch?->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($row->status) }}</td>
                        <td class="text-right">{{ number_format((float) $row->total, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $row->amount_paid, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $row->balance_due, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No invoice data for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
