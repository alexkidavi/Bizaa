<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; padding: 10px 12px; }
        .center { text-align: center; }
        .logo { max-height: 44px; max-width: 130px; display: block; margin: 0 auto 6px; }
        .biz-name { font-size: 15px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px; }
        .biz-sub { font-size: 9px; color: #555; margin-top: 2px; }
        .div { text-align: center; color: #aaa; margin: 7px 0; font-size: 10px; }
        .meta { font-size: 9px; color: #555; margin-bottom: 5px; }
        .meta .ref { font-weight: 700; font-size: 11px; color: #000; margin-top: 2px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items td { padding: 3px 0; font-size: 10px; vertical-align: top; }
        .item-name { font-weight: 700; font-size: 11px; }
        .item-sub { font-size: 9px; color: #555; }
        .tr { width: 100%; }
        .tr td { padding: 1px 0; font-size: 10px; }
        .tr.grand td { font-weight: 900; font-size: 13px; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 4px 0; }
        .tr.grn td { color: #059669; font-weight: 700; }
        .tr.red td { color: #dc2626; font-weight: 700; }
        .right { text-align: right; }
        .footer { text-align: center; color: #888; font-size: 9px; margin-top: 10px; }
        .footer .bold { font-weight: 700; color: #000; font-size: 11px; }
    </style>
</head>
<body>
    <div class="center">
        @if($branding['logo'])
            <img class="logo" src="{{ $branding['logo'] }}">
        @endif
        <div class="biz-name">{{ $branding['name'] }}</div>
        @if($business?->business_location)
            <div class="biz-sub">{{ $business->business_location }}</div>
        @endif
        @php
            $contacts = array_filter([$business?->business_phone, $business?->business_email]);
        @endphp
        @if($contacts)
            <div class="biz-sub">{{ implode(' · ', $contacts) }}</div>
        @endif
    </div>

    <div class="div">- - - - - - - - - - - - - - - - -</div>

    <div class="meta">
        @if($sale->branch)
            <div>{{ $sale->branch->name }}</div>
        @endif
        <div>{{ $sale->created_at->format('d M Y, H:i') }}</div>
        <div class="ref">Receipt #{{ $sale->reference }}</div>
        @if($sale->cashier)
            <div>Cashier: {{ $sale->cashier->name }}</div>
        @endif
        @if($sale->customer)
            <div>Customer: {{ $sale->customer->name }}</div>
        @endif
    </div>

    <div class="div">- - - - - - - - - - - - - - - - -</div>

    <table class="items">
        @foreach($sale->items as $item)
            <tr>
                <td colspan="2">
                    <div class="item-name">{{ $item->name ?? $item->product->name ?? 'Item' }}</div>
                    <div class="item-sub">
                        {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                        &times; KES {{ number_format((float) $item->unit_price, 2) }}
                        @if((float) $item->discount > 0)
                            (-{{ $item->discount }}%)
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </table>

    <div class="div">- - - - - - - - - - - - - - - - -</div>

    <table class="tr" cellpadding="0" cellspacing="0">
        <tr><td>Subtotal</td><td class="right">KES {{ number_format((float) $sale->subtotal, 2) }}</td></tr>
        @if((float) $sale->discount > 0)
            <tr class="grn"><td>Discount</td><td class="right">- KES {{ number_format((float) $sale->discount, 2) }}</td></tr>
        @endif
        @if((float) $sale->tax > 0)
            <tr><td>VAT</td><td class="right">KES {{ number_format((float) $sale->tax, 2) }}</td></tr>
        @endif
        <tr class="grand"><td>TOTAL</td><td class="right">KES {{ number_format((float) $sale->total, 2) }}</td></tr>
        <tr><td>Paid</td><td class="right">KES {{ number_format($paidTotal, 2) }}</td></tr>
        @foreach($sale->payments as $payment)
            @if($sale->payments->count() > 1)
                <tr><td>{{ ucfirst($payment->method) }}</td><td class="right">KES {{ number_format((float) $payment->amount, 2) }}</td></tr>
            @endif
        @endforeach
        @if($change > 0)
            <tr class="grn"><td>Change</td><td class="right">KES {{ number_format($change, 2) }}</td></tr>
        @endif
        @if($balance > 0)
            <tr class="red"><td>Balance Due</td><td class="right">KES {{ number_format($balance, 2) }}</td></tr>
        @endif
    </table>

    <div class="footer">
        <div class="bold">Thank you!</div>
        <div style="margin-top: 5px;">Powered by Bizaa</div>
    </div>
</body>
</html>
