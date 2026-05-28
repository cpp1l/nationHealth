<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

trait LogsExceptions
{
    /**
     * Log error messages if any exception occur during database interaction.
     *
     * @param  Exception|Throwable  $exception
     * @param  string  $logMessage
     * @param  string|null  $flashMessage  Custom flash message; defaults to messages.database_error
     * @return void
     */
    protected function handleDatabaseErrors(
        Exception|Throwable $exception,
        string $logMessage,
        ?string $flashMessage = null
    ): void {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        Log::channel('db_errors')->error($logMessage, [
            'class' => $caller['class'] ?? 'unknown_class',
            'method' => $caller['function'] ?? 'unknown_method',
            'error_message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line_in_file' => $exception->getLine()
        ]);
        Session::flash('error', $flashMessage ?? __('messages.database_error'));
    }
}
