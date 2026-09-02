<?php

namespace App\Http\Controllers;

use App\Services\PosOfflineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(protected PosOfflineService $offline)
    {
    }

    /**
     * GET /pos/offline-bootstrap?branch_id=
     *
     * One-shot snapshot the POS stores in IndexedDB so it can keep selling
     * with no connection. Called on POS load (when online) and from the
     * "Sync now" button. Same auth as ringing up a sale — a cashier who can
     * create a sale can pull the catalogue it needs.
     */
    public function offlineBootstrap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
        ]);

        return response()->json(
            $this->offline->bootstrap((int) $validated['branch_id'])
        );
    }
}
