<?php

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $exception) {
    error_log((string) $exception);

    http_response_code(500);
    echo getenv('VERCEL_ENV') === 'preview'
        ? $exception->getMessage()
        : 'Application error';
}
