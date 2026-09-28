<?php

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $exception) {
    error_log(sprintf(
        'GREYSTONE_BOOT_ERROR %s: %s in %s:%d',
        $exception::class,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
    ));
    http_response_code(500);
    echo getenv('VERCEL_ENV') === 'preview'
        ? $exception->getMessage()
        : 'Application error';
}
