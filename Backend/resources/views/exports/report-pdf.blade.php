<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @php
        $accent = $branding['color'] ?? '#229DE7';
        $bizName = $branding['name'] ?? 'Bizaa';
        $logo = $branding['logo'] ?? null;

        // dompdf's CSS parser doesn't reliably support 8-digit alpha hex,
        // so tint the accent toward white in PHP instead of using
        // rgba()/#RRGGBBAA for card backgrounds.
        $rgb = sscanf($accent, '#%02x%02x%02x');
        $tint = sprintf('#%02x%02x%02x', ...array_map(fn ($c) => (int) round($c + (255 - $c) * 0.90), $rgb));
        $tintBorder = sprintf('#%02x%02x%02x', ...array_map(fn ($c) => (int) round($c + (255 - $c) * 0.65), $rgb));

        // Pair up summary entries two-per-row for a card-grid look.
        $summaryPairs = array_chunk($summary ?? [], 2, true);
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { margin: 46px 42px 58px 42px; }

        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }

        /* ---------- header (centered letterhead) ---------- */
        .header { text-align: center; padding-bottom: 18px; margin-bottom: 24px; border-bottom: 2.5px solid {{ $accent }}; }
        .header .logo-wrap { margin: 0 auto 10px auto; width: 54px; height: 54px; }
        .header .logo-wrap img { width: 54px; height: 54px; object-fit: contain; }
        .header .biz { font-size: 11px; color: {{ $accent }}; font-weight: bold; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 6px; }
        .header h1 { font-size: 20px; color: #0f172a; margin-bottom: 8px; }
        .header .meta { font-size: 9px; color: #64748b; }
        .header .meta .sep { color: {{ $accent }}; margin: 0 6px; }

        /* ---------- summary card grid ---------- */
        .summary-wrap { margin-bottom: 24px; }
        .summary-wrap .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 9px; }
        table.summary-row { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.summary-row td.card { width: 50%; background: {{ $tint }}; border: 1px solid {{ $tintBorder }}; border-radius: 6px; padding: 10px 14px; }
        table.summary-row td.card .label { display: block; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $accent }}; margin-bottom: 4px; }
        table.summary-row td.card .value { display: block; font-size: 13px; font-weight: bold; color: #0f172a; }
        table.summary-row td.gap { width: 14px; border: none; background: transparent; padding: 0; }

        /* ---------- data table ---------- */
        .table-wrap .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 9px; }
        table.data { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
        table.data th { background: {{ $accent }}; color: #ffffff; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.03em; text-align: left; padding: 9px 10px; }
        table.data td { font-size: 9.5px; padding: 8px 10px; border-bottom: 1px solid #eef2f7; }
        table.data tr:nth-child(even) td { background: #f8fafc; }

        /* ---------- footer ---------- */
        .footer { position: fixed; bottom: 0px; left: 0; right: 0; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; text-align: center; }
        .footer .page-num:after { content: "Page " counter(page); }
    </style>
</head>
<body>
    <div class="header">
        @if($logo)
            <div class="logo-wrap"><img src="{{ $logo }}" alt="{{ $bizName }}"></div>
        @endif
        <div class="biz">{{ $bizName }}</div>
        <h1>{{ $title }}</h1>
        <div class="meta">Generated {{ $generatedAt }}@if($rangeLabel) <span class="sep">&middot;</span> {{ $rangeLabel }} @endif</div>
    </div>

    @if(!empty($summary))
        <div class="summary-wrap">
            <div class="section-title">Summary</div>
            @foreach($summaryPairs as $pair)
                <table class="summary-row">
                    <tr>
                        @php $entries = array_values($pair); @endphp
                        @if(count($entries) === 1)
                            <td class="card" colspan="3">
                                <span class="label">{{ array_key_first($pair) }}</span>
                                <span class="value">{{ $entries[0] }}</span>
                            </td>
                        @else
                            @foreach($pair as $label => $value)
                                <td class="card">
                                    <span class="label">{{ $label }}</span>
                                    <span class="value">{{ $value }}</span>
                                </td>
                                @if(!$loop->last)
                                    <td class="gap"></td>
                                @endif
                            @endforeach
                        @endif
                    </tr>
                </table>
            @endforeach
        </div>
    @endif

    <div class="table-wrap">
        <div class="section-title">Details</div>
        <table class="data">
            <thead>
                <tr>
                    @foreach($headings as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($headings) }}" style="text-align:center;color:#94a3b8;padding:16px;">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        {{ $bizName }} &middot; Generated by Bizaa &middot; <span class="page-num"></span>
    </div>
</body>
</html>
