<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait TransactionLogging
{
    /**
     * Surround a callback with database transaction and logging
     */
    protected function surroundWithTransaction(callable $callback, string $operation = 'Database operation', array $context = []): mixed
    {
        return DB::transaction(function () use ($callback, $operation, $context) {
            try {
                Log::info("Starting transaction: {$operation}");
                $result = $callback();
                Log::info("Transaction completed successfully: {$operation}", [
                    'context' => $context
                ]);
                return $result;
            } catch (\Exception $e) {
                Log::error("Transaction failed: {$operation}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'context' => $context
                ]);
                throw $e;
            }
        });
    }
}
