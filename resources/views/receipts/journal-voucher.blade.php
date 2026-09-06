<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $voucher['title'] }}</title>

<style>
* {
    box-sizing: border-box;
}

@page {
    margin: 12mm;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
    font-size: 11px;
}

hr {
    border: 0;
    border-top: 1px solid #000;
    margin: 10px 0;
    height: 0;
}

table {
    border-collapse: collapse;
    width: 100%;
}

/* ================= HEADER ================= */

.header-table {
    margin-bottom: 10px;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: middle;
}

.company-logo-cell {
    width: 62px;
}

.header-gap {
    width: 14px;
}

.company-logo {
    width: 62px;
    height: 62px;
    border: 1px solid #000;
    text-align: center;
    vertical-align: middle;
    font-size: 25px;
    font-weight: bold;
    color: #000;
    background: #fafafa;
}

.company-name-cell {
    vertical-align: middle;
}

.company-info h1 {
    margin: 0;
    font-size: 21px;
    font-weight: 700;
    color: #000;
}

.company-info p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #000;
}
.company-address {
    font-size: 14px;
    color: #000;
}

.company-contact {
    margin-top: 5px;
    font-size: 11px;
    color: #000;
}

.voucher-heading {
    text-align: right;
    vertical-align: top;
}

.voucher-heading h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: .7px;
    color: #000;
}

.voucher-heading span {
    display: inline-block;
    margin-top: 6px;
    padding: 4px 9px;
    border: 1px solid #000;
    font-size: 9px;
    font-weight: 600;
    color: #000;
}

/* ================= VOUCHER INFORMATION ================= */

.voucher-info-table {
    margin: 10px 0 25px;
    font-size: 10px;
}

.voucher-info-table td {
    border: none;
    padding: 0 10px 6px 0;
    vertical-align: top;
}

.info-col-70 {
    width: 70%;
}

.info-col-30 {
    width: 30%;
    padding-right: 0;
}

.info-label {
    display: block;
    font-weight: 700;
    font-size: 11px;
    color: #000;
}

.info-value {
    display: block;
    font-weight: 600;
    font-size: 11px;
    color: #000;
}

/* ================= ACCOUNTING TABLE ================= */

.voucher-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.voucher-table th,
.voucher-table td {
    border: 1px solid #000;
    padding: 11px 12px;
    font-size: 11px;
    color: #000;
}

.voucher-table th {
    background: #f1f1f1;
    font-weight: 700;
    text-align: left;
}

.voucher-table th.amount,
.voucher-table td.amount {
    text-align: right;
    width: 145px;
}
.text-right{
    text-align: right;
}

.account-code {
    font-weight: 600;
}

.account-description {
    display: block;
    margin-top: 3px;
    color: #000;
    font-size: 9px;
}

.total-row td {
    font-weight: 700;
    background: #fafafa;
}

/* ================= SIGNATURE ================= */

.signature-block {
    margin-top: 60px;
}

.signature-block td {
    border: none;
    width: 45%;
    padding: 0;
}

.signature-block .signature-gap {
    width: 10%;
}

.signature-line-table td {
    border: none;
    border-top: 1px solid #000;
    padding-top: 6px;
    text-align: center;
}

.signature-title {
    font-weight: 700;
    font-size: 10px;
    color: #000;
}

.signature-name {
    margin-top: 3px;
    font-size: 10px;
    font-weight: 600;
    color: #000;
}

/* ================= FOOTER ================= */

.pdf-footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: -8mm;
    width: 100%;
    padding-top: 11px;
    border-top: 1px solid #000;
    text-align: center;
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8px;
    white-space: nowrap;
}
</style>
</head>

<body>

{{-- Fixed footer, repeated on every PDF page by mPDF (position: fixed) --}}
<div class="pdf-footer">
    {{ $organization['name'] }} &bull; System-generated voucher &bull; Generated {{ app_datetime(now()) }}
</div>

@php
    $initials = collect(explode(' ', trim($organization['name'] ?? '')))
        ->filter()
        ->map(fn($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('');
@endphp

<table class="header-table">
    <tr>
        <td class="company-logo-cell">
            @if(!empty($organization['site_logo_other']))
                <img
                    src="{{ $organization['site_logo_other'] }}"
                    style="width: 62px; height: 62px; object-fit: contain;"
                >
            @else
                <div class="company-logo">
                    {{ strtoupper($initials ?: 'A') }}
                </div>
            @endif
        </td>
        <td class="header-gap">&nbsp;</td>
        <td class="company-name-cell">
            <div class="company-info">
                <h1>{{ $organization['name'] }}</h1>

                @if(!empty($organization['tagline']))
                    <p>{{ $organization['tagline'] }}</p>
                @endif

                 @if(!empty($organization['address']))
                    <div class="company-address">
                        {{ $organization['address'] }}
                    </div>
                @endif

                @if(!empty($organization['phone']) || !empty($organization['email']))
                    <div class="company-contact">
                        {{ collect([
                            !empty($organization['phone'])
                                ? 'Phone: '.$organization['phone']
                                : null,
                            !empty($organization['email'])
                                ? 'Email: '.$organization['email']
                                : null,
                        ])->filter()->implode('  |  ') }}
                    </div>
                @endif

                @if(!empty($organization['website']))
                    <div class="company-contact">
                        Web: {{ $organization['website'] }}
                    </div>
                @endif
            </div>
        </td>
        <td class="voucher-heading">
            <h2>{{ strtoupper($voucher['title']) }}</h2>

            @if(!empty($voucher['status']))
                <span>{{ strtoupper($voucher['status']) }}</span>
            @endif
        </td>
    </tr>
</table>

<hr>

<table class="voucher-info-table">
    <tr>
        <td class="info-col-70">
            <span class="info-label">Voucher No</span>
            <span class="info-value">: {{ $voucher['voucher_no'] }}</span>
        </td>
        <td class="info-col-30">
            <span class="info-label">Date</span>
            <span class="info-value">: {{ $voucher['date'] ?? '—' }}</span>
        </td>
    </tr>
    <tr>
        <td class="info-col-70">
            <span class="info-label">Posted At</span>
            <span class="info-value">: {{ $voucher['posted_at'] ?? '—' }}</span>
        </td>
        <td class="info-col-30">
            <span class="info-label">Reference</span>
            <span class="info-value">: {{ $voucher['reference'] ?? '—' }}</span>
        </td>
    </tr>
</table>

<table class="voucher-table">
    <thead>
        <tr>
            <th>Account Particulars</th>
            <th class="amount">Debit</th>
            <th class="amount">Credit</th>
        </tr>
    </thead>

    <tbody>
        @foreach($voucher['entries'] as $entry)
            <tr>
                <td>
                    <span class="account-code">
                        {{ $entry['account'] }}
                    </span>
                    <br>
                    @if(!empty($entry['description']))
                        <span class="account-description">
                            {{ $entry['description'] }}
                        </span>
                    @endif
                </td>

                <td class="amount">
                    {{ $entry['debit'] > 0
                        ? number_format($entry['debit'], 2)
                        : '—'
                    }}
                </td>

                <td class="amount">
                    {{ $entry['credit'] > 0
                        ? number_format($entry['credit'], 2)
                        : '—'
                    }}
                </td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td class="text-right">Total</td>
            <td class="amount">{{ number_format($voucher['total_debit'], 2) }}</td>
            <td class="amount">{{ number_format($voucher['total_credit'], 2) }}</td>
        </tr>

    </tbody>
</table>

<table class="signature-block">
    <tr>
        <td>
            <table class="signature-line-table">
                <tr>
                    <td style="border-top: none; height: 30px;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #000; padding-top: 4px;">
                        <div class="signature-title">Prepared By</div>
                        <div class="signature-name">{{ $voucher['prepared_by'] }}</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="signature-gap">&nbsp;</td>
        <td>
            <table class="signature-line-table">
                <tr>
                    <td style="border-top: none; height: 30px;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #000; padding-top: 4px;">
                        <div class="signature-title">Authorized By</div>
                        <div class="signature-name">{{ $voucher['authorized_by'] }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>