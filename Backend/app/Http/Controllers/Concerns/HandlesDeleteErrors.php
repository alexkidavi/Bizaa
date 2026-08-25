<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Provides a safe wrapper for soft-delete operations.
 *
 * - Catches constraint violations and returns a friendly message.
 * - Never leaks raw SQL or stack traces to the client.
 * - Validation errors (422) bubble up normally via Laravel's
 *   ValidatesRequests trait — no interception needed.
 */
trait HandlesDeleteErrors
{
    /**
     * Execute a delete closure and return a JSON response.
     *
     * @param  \Closure(): void  $action
     * @param  string            $successMessage
     */
    protected function safeDelete(
        \Closure $action,
        string $successMessage = 'Record deleted successfully'
    ): JsonResponse {
        try {
            $action();
            return response()->json(['message' => $successMessage]);
        } catch (QueryException $e) {
            // Foreign-key constraint violation — record is still referenced
            if (in_array($e->getCode(), ['23000', '23503'])) {
                return response()->json([
                    'message' => 'Cannot delete this record because it is linked to other data. Please remove the linked records first.',
                ], 422);
            }

            return response()->json([
                'message' => 'Failed to delete the record. Please try again.',
            ], 500);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }
}
