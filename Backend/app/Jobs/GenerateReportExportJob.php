<?php

namespace App\Jobs;

use App\Exports\GenericReportExport;
use App\Models\Business;
use App\Models\ReportExport;
use App\Services\ReportBranding;
use App\Services\ReportExportRegistry;
use App\Services\ReportsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class GenerateReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    public function __construct(protected int $exportId) {}

    public function handle(ReportsService $service): void
    {
        $export = ReportExport::find($this->exportId);
        if (! $export) {
            return;
        }

        $export->update(['status' => 'processing']);

        try {
            [$title, $headings, $rows, $summary, $rangeLabel] = $this->build($service, $export);

            $fileName = sprintf(
                'exports/%d/%s-%s.%s',
                $export->business_id,
                str($export->report_type)->slug(),
                now()->format('Ymd-His'),
                $export->format
            );

            $branding = ReportBranding::forBusiness(Business::find($export->business_id));

            if ($export->format === 'xlsx') {
                Excel::store(new GenericReportExport($headings, $rows, $title, $branding), $fileName, 'local');
            } else {
                $pdf = Pdf::loadView('exports.report-pdf', [
                    'title'        => $title,
                    'branding'     => $branding,
                    'generatedAt'  => now()->format('d M Y, H:i'),
                    'rangeLabel'   => $rangeLabel,
                    'summary'      => $summary,
                    'headings'     => $headings,
                    'rows'         => $rows,
                ])->setPaper('a4', count($headings) > 7 ? 'landscape' : 'portrait');

                Storage::disk('local')->put($fileName, $pdf->output());
            }

            $export->update([
                'status'       => 'completed',
                'file_path'    => $fileName,
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $export->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolves report_type -> [title, headings, rows, summary, rangeLabel].
     * Every report except close-day and account-statement follows the
     * same "flat rows + registry column set" shape; those two get their
     * own row-building because their underlying data isn't a flat list.
     */
    protected function build(ReportsService $service, ReportExport $export): array
    {
        $p = $export->params ?? [];
        $businessId = $export->business_id;
        $type = $export->report_type;

        $rangeLabel = isset($p['from'], $p['to']) ? "{$p['from']} to {$p['to']}" : null;

        if ($type === 'close-day') {
            $data = $service->closeDayReport($p['from'], $p['to'], $businessId);
            $headings = ['Date', 'Transactions', 'Total'];
            $rows = collect($data['daily_totals'] ?? [])
                ->map(fn ($d) => [(string) $d->date, (string) $d->transactions, (string) $d->total])
                ->all();
            $summary = [
                'Gross Sales'   => number_format($data['gross_sales'], 2),
                'Net Sales'     => number_format($data['net_sales'], 2),
                'Total Paid'    => number_format($data['total_paid'], 2),
                'Balance Due'   => number_format($data['total_balance_due'], 2),
                'Gross Profit'  => number_format($data['gross_profit'], 2),
                'Profit Margin' => $data['profit_margin'] . '%',
            ];
            return ['Close Day Report', $headings, $rows, $summary, "{$p['from']} to {$p['to']}"];
        }

        if ($type === 'account-statement') {
            $partyId = $p['party_type'] === 'customer'
                ? (int) \Illuminate\Support\Facades\Crypt::decryptString($p['party_id'])
                : (int) $p['party_id'];

            $data = $service->accountStatement($businessId, $p['party_type'], $partyId, $p['from'] ?? null, $p['to'] ?? null);
            $headings = ['Date', 'Type', 'Reference', 'Debit', 'Credit', 'Balance'];
            $rows = collect($data['entries'] ?? [])
                ->map(fn ($e) => [
                    (string) $e['date'], (string) $e['type'], (string) ($e['reference'] ?? ''),
                    $e['debit'] > 0 ? number_format($e['debit'], 2) : '',
                    $e['credit'] > 0 ? number_format($e['credit'], 2) : '',
                    number_format($e['balance'], 2),
                ])
                ->all();
            $summary = [
                'Party'            => $data['party']->name ?? '—',
                'Opening Balance'  => number_format($data['opening_balance'], 2),
                'Closing Balance'  => number_format($data['closing_balance'], 2),
            ];
            $title = 'Account Statement — ' . ($data['party']->name ?? '');
            return [$title, $headings, $rows, $summary, $rangeLabel];
        }

        $definition = ReportExportRegistry::get($type);
        if (! $definition) {
            throw new \InvalidArgumentException("Unknown report type: {$type}");
        }

        $result = $this->callService($service, $type, $businessId, $p);
        $rowsRaw = $result[$definition['rowsKey']] ?? collect();
        $columns = $definition['columns'];

        $rows = collect($rowsRaw)
            ->map(fn ($row) => ReportExportRegistry::flattenRow((array) $row, $columns))
            ->all();

        $summary = collect($result['summary'] ?? [])
            ->mapWithKeys(fn ($v, $k) => [str($k)->replace('_', ' ')->title()->toString() => is_numeric($v) ? number_format((float) $v, 2) : $v])
            ->all();

        return [$definition['label'], array_values($columns), $rows, $summary, $rangeLabel];
    }

    protected function callService(ReportsService $service, string $type, int $businessId, array $p): array
    {
        return match ($type) {
            'items-sale'         => $service->itemsSaleReport($p['from'], $p['to'], $businessId, $p['branch_id'] ?? null),
            'sales-customer'     => $service->salesByCustomer($p['from'], $p['to'], $businessId),
            'sales-branch'       => $service->salesByBranch($p['from'], $p['to'], $businessId),
            'items-ledger'       => $service->itemsLedger($p['from'], $p['to'], $businessId, $p['product_id'] ?? null),
            'customer-balances'  => $service->customerBalances($businessId, $p['search'] ?? null),
            'supplier-balances'  => $service->supplierBalances($businessId, $p['search'] ?? null),
            'sale-payments'      => $service->salePayments($p['from'], $p['to'], $businessId, $p['method'] ?? null, $p['search'] ?? null),
            'purchase-payments'  => $service->purchasePayments($p['from'], $p['to'], $businessId, $p['method'] ?? null, $p['search'] ?? null),
            'general-sales'      => $service->generalSalesReport($p['from'], $p['to'], $businessId, $p['branch_id'] ?? null, $p['payment_method'] ?? null),
            'revenue-item'       => $service->revenueByItem($p['from'], $p['to'], $businessId, $p['branch_id'] ?? null),
            'items-purchase'     => $service->itemsPurchaseReport($p['from'], $p['to'], $businessId, $p['supplier_id'] ?? null),
            'profit'             => $service->profitReport($p['from'], $p['to'], $businessId),
            'staff-sales'        => $service->staffSalesReport($p['from'], $p['to'], $businessId, $p['branch_id'] ?? null),
            'expenses'           => $service->expensesReport($p['from'], $p['to'], $businessId, $p['category_id'] ?? null),
            default              => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };
    }
}
