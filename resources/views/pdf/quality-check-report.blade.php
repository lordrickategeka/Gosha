<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Quality Check - {{ $qualityCheck->workOrder->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 5px;
            border: 1px solid #000;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f0f0f0;
        }
        
        .section-title {
            background-color: #333;
            color: white;
            padding: 8px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .checklist-table th {
            background-color: #666;
            color: white;
            padding: 6px;
            text-align: left;
            border: 1px solid #000;
        }
        
        .checklist-table th.center {
            text-align: center;
            width: 12%;
        }
        
        .checklist-table td {
            padding: 6px;
            border: 1px solid #000;
        }
        
        .checklist-table td.center {
            text-align: center;
        }
        
        .checkmark {
            font-size: 14pt;
            font-weight: bold;
        }
        
        .ok { color: green; }
        .needs-attention { color: red; }
        .na { color: gray; }
        
        .notes-section {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            min-height: 80px;
        }
        
        .signature-section {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #000;
        }
        
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 300px;
            display: inline-block;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VEHICLE CHECK LIST</h1>
        <p>Work Order: {{ $qualityCheck->workOrder->order_number }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td>NAME</td>
            <td>{{ $qualityCheck->customer->name }}</td>
        </tr>
        <tr>
            <td>VEHICLE MODEL</td>
            <td>{{ $qualityCheck->vehicle->model }}</td>
        </tr>
        <tr>
            <td>VEHICLE NUMBER</td>
            <td>{{ $qualityCheck->vehicle->registration_number }}</td>
        </tr>
        <tr>
            <td>MILEAGE</td>
            <td>{{ number_format($qualityCheck->vehicle->current_mileage) }} km</td>
        </tr>
        <tr>
            <td>VIN/CHASSIS NO</td>
            <td>{{ $qualityCheck->vehicle->vin_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>DATE</td>
            <td>{{ $qualityCheck->inspection_date?->format('F d, Y') ?? now()->format('F d, Y') }}</td>
        </tr>
    </table>

    @foreach($itemsBySection as $section => $items)
        <div class="section-title">
            {{ strtoupper(\App\Models\QualityCheckTemplate::SECTIONS[$section] ?? $section) }}
        </div>

        <table class="checklist-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="center">OK</th>
                    <th class="center">Needs Attention</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td class="center">
                            @if($item->status === 'ok')
                                <span class="checkmark ok">✓</span>
                            @endif
                        </td>
                        <td class="center">
                            @if($item->status === 'needs_attention')
                                <span class="checkmark needs-attention">✗</span>
                            @endif
                        </td>
                        <td>{{ $item->remarks ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="section-title">F. GENERAL NOTES / OBSERVATIONS</div>
    <div class="notes-section">
        {{ $qualityCheck->general_notes ?? 'No additional notes.' }}
    </div>

    <div class="signature-section">
        <p><strong>Inspected By:</strong> {{ $qualityCheck->inspector?->name ?? '________________________' }}</p>
        <p style="margin-top: 10px;"><strong>Date:</strong> {{ $qualityCheck->completed_at?->format('F d, Y') ?? '________________________' }}</p>
        <p style="margin-top: 30px;">
            <strong>Signature:</strong> 
            <span class="signature-line"></span>
        </p>
    </div>
</body>
</html>
