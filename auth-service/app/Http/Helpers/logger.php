<?php

function log_exception(\Exception $e)
{
    Illuminate\Support\Facades\Log::info('Exception log:', [
        'error' => [
            'code' => http_code_by_exception_type($e),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
