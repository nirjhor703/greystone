<?php

return [
    'disk' => 'public',
    'quality' => (int) env('MEDIA_WEBP_QUALITY', 82),
    'max_pixels' => (int) env('MEDIA_MAX_SOURCE_PIXELS', 25000000),
    'presets' => ['thumbnail' => 320, 'card' => 720, 'large' => 1600],
];
