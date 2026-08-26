<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateReportExportJob;
use App\Models\ReportExport;
use App\Services\ReportExportRegistry;
use App\Services\ReportsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(protected ReportsService $service) {}

    protected const REPORT_TYPES = [
        'close-day', 'items-sale', 'sales-customer', 'sales-branch', 'items-ledger',
        'customer-balances', 'supplier-balances', 'sale-payments', 'purchase-payments',
        'general-sales', 'revenue-item', 'items-purchase', 'profit', 'staff-sales',
        'expenses', 'account-statement',
    ];

    // Reports that don't take a date range at all — everything else does.
    protected const NO_DATE_RANGE = ['customer-balances', 'supplier-balances'];

    /**
     * POST /api/reports/export — enqueue generation, return immediately.
     * The actual file is built by GenerateReportExportJob on the queue,
     * not in this request — reports can involve large scans and a full
     * year's data shouldn't hold an HTTP connection open.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'report_type' => 'required|string|in:' . implode(',', self::REPORT_TYPES),
            'format'      => 'required|string|in:xlsx,pdf',
            'params'      => 'nullable|array',
        ]);

        $params = $data['params'] ?? [];

        // The view endpoints resolve preset -> concrete from/to server-side
        // (see ReportController::range()) — the export job needs the same
        // resolution done once here, since by the time a queued job runs
        // "today" must already mean a fixed date, not whatever day the
        // job happens to execute on.
        if (! in_array($data['report_type'], self::NO_DATE_RANGE, true) && ! empty($params['preset'])) {
            [$from, $to] = $this->service->resolveDateRange(
                $params['preset'],
                $params['from'] ?? null,
                $params['to'] ?? null
            );
            $params['from'] = $from;
            $params['to'] = $to;
            unset($params['preset']);
        }

        $export = ReportExport::create([
            'business_id' => Auth::user()->business_id,
            'user_id'     => Auth::id(),
            'report_type' => $data['report_type'],
            'format'      => $data['format'],
            'params'      => $params,
            'status'      => 'pending',
        ]);

        GenerateReportExportJob::dispatch($export->id);

        return response()->json([
            'message' => 'Export queued.',
            'data'    => ['id' => $export->id, 'status' => $export->status],
        ], 202);
    }

    /**
     * GET /api/reports/export/{id} — poll status.
     */
    public function show(int $id): JsonResponse
    {
        $export = ReportExport::where('business_id', Auth::user()->business_id)->findOrFail($id);

        return response()->json([
            'data' => [
                'id'      => $export->id,
                'status'  => $export->status,
                'error'   => $export->error_message,
                'ready'   => $export->status === 'completed',
            ],
        ]);
    }

    /**
     * GET /api/reports/export/{id}/download
     */
    public function download(int $id): StreamedResponse|JsonResponse
    {
        $export = ReportExport::where('business_id', Auth::user()->business_id)->findOrFail($id);

        if ($export->status !== 'completed' || ! $export->file_path || ! Storage::disk('local')->exists($export->file_path)) {
            return response()->json(['message' => 'Export is not ready yet.'], 404);
        }

        $definition = ReportExportRegistry::get($export->report_type);
        $label = $definition['label'] ?? ucwords(str_replace('-', ' ', $export->report_type));
        $downloadName = str_replace(' ', '-', $label) . '-' . $export->created_at->format('Ymd-His') . '.' . $export->format;

        return Storage::disk('local')->download($export->file_path, $downloadName);
    }
}
