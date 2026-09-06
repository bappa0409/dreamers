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
    margin: 18px 0;
    height: 0;
}

table {
    border-collapse: collapse;
    width: 100%;
}

/* ================= HEADER ================= */

.header-table {
    margin-bottom: 20px;
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
    width: 18px;
}

.company-logo {
    width: 62px;
    height: 62px;
    border: 1px solid #000;
    text-align: center;
    vertical-align: middle;
    font-size: 25px;
    font-weight: bold;
    color: #333;
    background: #fafafa;
}

.company-name-cell {
    vertical-align: middle;
}

.company-info h1 {
    margin: 0;
    font-size: 21px;
    font-weight: 700;
}

.company-info p {
    margin: 3px 0 0;
    font-size: 10px;
    color: #666;
}

.company-contact {
    margin-top: 5px;
    font-size: 9px;
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
}

.voucher-heading span {
    display: inline-block;
    margin-top: 6px;
    padding: 4px 9px;
    border: 1px solid #bbb;
    font-size: 9px;
    color: #555;
}

/* ================= VOUCHER INFORMATION ================= */

.voucher-info-table {
    margin: 23px 0 20px;
    font-size: 11px;
}

.voucher-info-table td {
    border: none;
    padding: 3px 0;
    line-height: 1.7;
    vertical-align: top;
}

.info-label {
    width: 105px;
    font-weight: 600;
    color: #444;
    padding-right: 10px !important;
}

.info-value {
    color: #222;
    padding-right: 45px !important;
}

.description-row .info-value {
    padding-right: 0 !important;
}

/* ================= ACCOUNTING TABLE ================= */

.voucher-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.voucher-table th,
.voucher-table td {
    border: 1px solid #bfc3c7;
    padding: 11px 12px;
    font-size: 11px;
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

.account-code {
    font-weight: 600;
}

.account-description {
    display: block;
    margin-top: 3px;
    color: #777;
    font-size: 9px;
}

.total-row td {
    font-weight: 700;
    background: #fafafa;
}

/* ================= SIGNATURE ================= */

.signature-table {
    margin-top: 65px;
}

.signature-table td {
    border: none;
    width: 200px;
    text-align: center;
    font-size: 10px;
    padding: 0;
}

.signature-spacer {
    width: auto;
}

.signature-divider {
    border-left: 1px solid #bbb;
}

.signature-line {
    border-top: 1px solid #444;
    margin-bottom: 7px;
}

.signature-title {
    font-weight: 600;
}

.signature-name {
    color: #666;
    margin-top: 3px;
}

/* ================= FOOTER ================= */

.pdf-footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: -8mm;
    width: 100%;
    padding-top: 11px;
    border-top: 1px solid #ddd;
    text-align: center;
    color: #777;
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
            @if(!empty($organization['logo_path']))
                <img
                    src="{{ $organization['logo_path'] }}"
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

                @if(
                    !empty($organization['address']) ||
                    !empty($organization['phone']) ||
                    !empty($organization['email'])
                )
                    <div class="company-contact">
                        {{ collect([
                            $organization['address'] ?? null,
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
        <td class="info-label">Voucher No</td>
        <td class="info-value">{{ $voucher['voucher_no'] }}</td>
        <td class="info-label">Date</td>
        <td class="info-value">{{ $voucher['date'] ?? '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Type</td>
        <td class="info-value">{{ $voucher['type'] }}</td>
        <td class="info-label">Source</td>
        <td class="info-value">{{ $voucher['source'] }}</td>
    </tr>
    <tr>
        <td class="info-label">Posted At</td>
        <td class="info-value">{{ $voucher['posted_at'] ?? '—' }}</td>
        <td class="info-label">Reference</td>
        <td class="info-value">{{ $voucher['reference'] ?? '—' }}</td>
    </tr>
    @if(!empty($voucher['description']))
        <tr class="description-row">
            <td class="info-label">Description</td>
            <td class="info-value" colspan="3">{{ $voucher['description'] }}</td>
        </tr>
    @endif
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
            <td>Total</td>
            <td class="amount">{{ number_format($voucher['total_debit'], 2) }}</td>
            <td class="amount">{{ number_format($voucher['total_credit'], 2) }}</td>
        </tr>

    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td>
            <div class="signature-line"></div>
            <div class="signature-title">Prepared By</div>
            <div class="signature-name">{{ $voucher['prepared_by'] }}</div>
        </td>
        <td class="signature-spacer signature-divider">&nbsp;</td>
        <td>
            <div class="signature-line"></div>
            <div class="signature-title">Authorized By</div>
            <div class="signature-name">{{ $voucher['authorized_by'] }}</div>
        </td>
    </tr>
</table>

</body>
</html>