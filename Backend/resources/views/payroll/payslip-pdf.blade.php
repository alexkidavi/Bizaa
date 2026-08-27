<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $employee->full_name }}</title>
    @php
        $accent = $branding['color'] ?? '#229DE7';
        $bizName = $branding['name'] ?? 'Bizaa';
        $logo = $branding['logo'] ?? null;
        $rgb = sscanf($accent, '#%02x%02x%02x');
        $tint = sprintf('#%02x%02x%02x', ...array_map(fn ($c) => (int) round($c + (255 - $c) * 0.92), $rgb));
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 40px 42px 50px 42px; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }

        .header { text-align: center; padding-bottom: 16px; margin-bottom: 18px; border-bottom: 2.5px solid {{ $accent }}; }
        .header .logo-wrap { margin: 0 auto 8px auto; width: 48px; height: 48px; }
        .header .logo-wrap img { width: 48px; height: 48px; object-fit: contain; }
        .header .biz { font-size: 11px; color: {{ $accent }}; font-weight: bold; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 4px; }
        .header h1 { font-size: 17px; color: #0f172a; }

        .meta-table { width: 100%; margin-bottom: 16px; }
        .meta-table td { padding: 3px 0; font-size: 9.5px; }
        .meta-table .label { color: #64748b; width: 33%; }
        .meta-table .value { font-weight: bold; color: #0f172a; }

        .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: {{ $accent }}; background: {{ $tint }}; padding: 5px 8px; margin-top: 12px; margin-bottom: 0; }

        table.lines { width: 100%; border-collapse: collapse; }
        table.lines td { padding: 5px 8px; border-bottom: 1px solid #eef2f7; font-size: 9.5px; }
        table.lines td.amt { text-align: right; font-weight: bold; }

        .totals-table { width: 100%; margin-top: 14px; border-top: 2px solid {{ $accent }}; }
        .totals-table td { padding: 6px 8px; font-size: 10.5px; }
        .totals-table td.label { color: #475569; }
        .totals-table td.amt { text-align: right; font-weight: bold; }
        .totals-table tr.net td { font-size: 13px; font-weight: bold; color: {{ $accent }}; border-top: 1px solid #e2e8f0; padding-top: 10px; }

        .employer-note { margin-top: 16px; font-size: 8.5px; color: #94a3b8; }

        .footer { margin-top: 28px; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        @if ($logo)
            <div class="logo-wrap"><img src="{{ $logo }}" /></div>
        @endif
        <div class="biz">{{ $bizName }}</div>
        <h1>Payslip — {{ $period->name }}</h1>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Employee</td><td class="value">{{ $employee->full_name }}</td>
            <td class="label">Employee No.</td><td class="value">{{ $employee->employee_number ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td><td class="value">{{ $employee->designation ?? '—' }}</td>
            <td class="label">Pay Period</td><td class="value">{{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">KRA PIN</td><td class="value">{{ $employee->kra_pin ?? '—' }}</td>
            <td class="label">Pay Date</td><td class="value">{{ $period->pay_date?->format('d M Y') ?? '—' }}</td>
        </tr>
    </table>

    <div class="section-title">Earnings</div>
    <table class="lines">
        @foreach ($payslip->lines->where('type', 'earning') as $line)
        <tr><td>{{ $line->name }}</td><td class="amt">{{ number_format($line->amount, 2) }}</td></tr>
        @endforeach
    </table>

    <div class="section-title">Deductions</div>
    <table class="lines">
        @foreach ($payslip->lines->where('type', 'deduction') as $line)
        <tr><td>{{ $line->name }}</td><td class="amt">{{ number_format($line->amount, 2) }}</td></tr>
        @endforeach
    </table>

    <table class="totals-table">
        <tr><td class="label">Gross Earnings</td><td class="amt">{{ number_format($payslip->gross_earnings, 2) }}</td></tr>
        <tr><td class="label">Total Deductions</td><td class="amt">{{ number_format($payslip->paye + $payslip->nssf_employee + $payslip->shif + $payslip->housing_levy_employee + $payslip->other_deductions + $payslip->advance_repayment, 2) }}</td></tr>
        <tr class="net"><td class="label">Net Pay</td><td class="amt">KES {{ number_format($payslip->net_pay, 2) }}</td></tr>
    </table>

    <p class="employer-note">Employer statutory contributions (not deducted from net pay): NSSF {{ number_format($payslip->nssf_employer, 2) }} · Housing Levy {{ number_format($payslip->housing_levy_employer, 2) }}</p>

    <div class="footer">Generated {{ now()->format('d M Y, H:i') }} · {{ $bizName }} payroll system</div>
</body>
</html>
