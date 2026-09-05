<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $receipt['title'] }}</title>

<style>
*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:notosans,notosansbengali,sans-serif;
    font-size:10px;
    color:#000;
    background:#fff;
}

.header{
    width:100%;
    text-align:center;
    padding-bottom:8px;
    margin-bottom:10px;
    border-bottom:1px solid #000;
}

.logo{
    height:44px;
    max-width:110px;
    margin-bottom:4px;
}

.organization{
    font-family:notosans,notosansbengali,sans-serif;
    font-size:15px;
    font-weight:bold;
    color:#000;
}

.receipt-title{
    margin-top:2px;
    font-size:11px;
    font-weight:bold;
    color:#000;
    text-transform:uppercase;
}

.status{
    display:inline-block;
    margin-top:4px;
    padding:2px 10px;
    border:1px solid #000;
    font-size:8px;
    font-weight:bold;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

.top-meta{
    width:100%;
    border-collapse:collapse;
    margin-bottom:8px;
}

.top-meta td{
    padding:2px 0;
    border:none;
    font-size:9px;
    color:#000;
    vertical-align:top;
}

.top-meta .label{
    color:#444;
    width:40%;
}

.section-title{
    font-size:9px;
    font-weight:bold;
    text-transform:uppercase;
    color:#000;
    border-bottom:1px solid #000;
    padding-bottom:3px;
    margin-bottom:5px;
}

.bill-to{
    margin-bottom:10px;
}

.bill-to .name{
    font-size:11px;
    font-weight:bold;
}

.bill-to .line{
    font-size:9px;
    color:#222;
}

.meta-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:10px;
}

.meta-table td{
    padding:3px 0;
    border-bottom:1px dotted #999;
    font-size:9px;
    color:#000;
}

.meta-table td.label{
    color:#444;
    width:45%;
}

.meta-table td.value{
    text-align:right;
    font-weight:bold;
}

.items-table{
    width:100%;
    border-collapse:collapse;
    border:1px solid #000;
    margin-bottom:0;
}

.items-table th,
.items-table td{
    padding:6px 7px;
    border:0;
    border-bottom:1px solid #000;
    font-size:9px;
    color:#000;
}

.items-table th{
    font-weight:bold;
    text-align:left;
    background:#f0f0f0;
}

.items-table td.amount,
.items-table th.amount{
    text-align:right;
}

.items-table tbody tr:last-child td{
    border-bottom:1px solid #000;
}

.total-row td{
    padding:8px 7px;
    font-size:11px;
    font-weight:bold;
    border-top:2px solid #000;
}

.notes{
    margin-top:10px;
    padding:6px 8px;
    border:1px dashed #999;
    font-size:8px;
    color:#333;
}

.notes .notes-title{
    font-weight:bold;
    margin-bottom:2px;
    text-transform:uppercase;
    font-size:7px;
    color:#000;
}

.signature{
    margin-top:26px;
    font-size:8px;
    color:#333;
    text-align:center;
}

.pdf-footer{
    width:100%;
    padding-top:5px;
    border-top:1px solid #000;
    font-family:notosans,notosansbengali,sans-serif;
    font-size:7px;
    color:#000;
    text-align:center;
}
</style>
</head>

<body>

{{-- Fixed footer for every PDF page --}}
<htmlpagefooter name="receiptFooter">
    <div class="pdf-footer">
        {{ $organization['name'] }} &bull; System-generated receipt &bull; Generated {{ app_datetime(now()) }}
    </div>
</htmlpagefooter>

<sethtmlpagefooter name="receiptFooter" value="on" />

<div class="header">

    @if(!empty($organization['logo_path']))
        <img
            src="{{ $organization['logo_path'] }}"
            class="logo"
        >
    @endif

    <div class="organization">
        {{ $organization['name'] }}
    </div>

    <div class="receipt-title">
        {{ $receipt['title'] }}
    </div>

    @if(!empty($receipt['status']))
        <div class="status">
            {{ $receipt['status'] }}
        </div>
    @endif

</div>

<table class="top-meta">
    <tr>
        <td class="label">Receipt No</td>
        <td>: {{ $receipt['receipt_no'] }}</td>
    </tr>
    <tr>
        <td class="label">Date</td>
        <td>: {{ $receipt['issued_at'] }}</td>
    </tr>
</table>

@if(!empty($receipt['bill_to']))
    <div class="bill-to">
        <div class="section-title">Received From</div>

        <div class="name">
            {{ $receipt['bill_to']['name'] ?? '-' }}
        </div>

        @foreach(($receipt['bill_to']['lines'] ?? []) as $line)
            @if(!empty($line))
                <div class="line">{{ $line }}</div>
            @endif
        @endforeach
    </div>
@endif

@if(!empty($receipt['meta']))
    <table class="meta-table">
        @foreach($receipt['meta'] as $row)
            <tr>
                <td class="label">{{ $row['label'] }}</td>
                <td class="value">{{ $row['value'] ?? '-' }}</td>
            </tr>
        @endforeach
    </table>
@endif

<table class="items-table">
    <thead>
        <tr>
            <th>Description</th>
            <th class="amount">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($receipt['items'] as $item)
            <tr>
                <td>{{ $item['label'] }}</td>
                <td class="amount">{{ $item['value'] }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td>{{ $receipt['total_label'] }}</td>
            <td class="amount">{{ $receipt['total_amount'] }}</td>
        </tr>
    </tbody>
</table>

@if(!empty($receipt['notes']))
    <div class="notes">
        <div class="notes-title">Note</div>
        {{ $receipt['notes'] }}
    </div>
@endif

<div class="signature">
    This is a system-generated receipt and does not require a signature.
</div>

</body>
</html>