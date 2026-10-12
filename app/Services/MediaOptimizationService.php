<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaOptimizationService
{
    private array $variantUrlCache = [];

    private array $existsCache = [];

    public function optimize(UploadedFile $file, string $directory, string $preset = 'large'): array
    {
        $type = @exif_imagetype($file->getRealPath());
        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || ! function_exists('imagewebp')) {
            return ['path' => $file->store($directory, config('media.disk', 'public')), 'variants' => []];
        }
        $size = @getimagesize($file->getRealPath());
        if (! $size || ((int) $size[0] * (int) $size[1]) > config('media.max_pixels')) throw new RuntimeException('The image dimensions are too large to process safely.');
        $source = match ($type) { IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getRealPath()), IMAGETYPE_PNG => @imagecreatefrompng($file->getRealPath()), IMAGETYPE_WEBP => @imagecreatefromwebp($file->getRealPath()) };
        if (! $source) throw new RuntimeException('Unable to decode the uploaded image.');
        $source = $this->orient($source, $file->getRealPath(), $type);
        $width = imagesx($source); $height = imagesy($source); $image = $this->resize($source, $width, $height, (int) config("media.presets.{$preset}", 1600));
        $disk = Storage::disk(config('media.disk', 'public')); $base = trim($directory, '/').'/'.pathinfo($file->hashName(), PATHINFO_FILENAME); $path = $base.'.webp';
        $tmp = tmpfile(); imagewebp($image, stream_get_meta_data($tmp)['uri'], (int) config('media.quality', 82)); rewind($tmp); $disk->put($path, stream_get_contents($tmp)); fclose($tmp);
        $variants = [$preset => $path];
        foreach (config('media.presets', []) as $name => $max) {
            if ($name === $preset || $max >= max(imagesx($image), imagesy($image))) continue;
            $variant = $this->resize($image, imagesx($image), imagesy($image), (int) $max); $variantPath = $base.'-'.$name.'.webp'; $tmp = tmpfile(); imagewebp($variant, stream_get_meta_data($tmp)['uri'], (int) config('media.quality', 82)); rewind($tmp); $disk->put($variantPath, stream_get_contents($tmp)); fclose($tmp); imagedestroy($variant); $variants[$name] = $variantPath;
        }
        imagedestroy($source); imagedestroy($image);
        return ['path' => $path, 'variants' => $variants];
    }

    public function optimizeStored(string $path, string $preset = 'large'): ?array
    {
        $disk = Storage::disk(config('media.disk', 'public'));
        if (! $disk->exists($path)) return null;
        if (! function_exists('imagewebp')) throw new RuntimeException('GD WebP support is unavailable in the current PHP CLI environment.');
        if (str_ends_with(strtolower($path), '.webp') && $disk->exists($path)) {
            $variants = [$preset => $path];
            foreach (array_keys(config('media.presets', [])) as $name) {
                $candidate = preg_replace('/\.webp$/i', '-'.$name.'.webp', $path) ?: $path;
                if ($disk->exists($candidate)) $variants[$name] = $candidate;
            }
            if (count($variants) === count(config('media.presets', []))) return ['path' => $path, 'variants' => $variants];
        }
        $absolute = $disk->path($path);
        $type = @exif_imagetype($absolute);
        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) return null;
        $size = @getimagesize($absolute);
        if (! $size || ((int) $size[0] * (int) $size[1]) > config('media.max_pixels')) return null;
        $fake = new UploadedFile($absolute, basename($absolute), mime_content_type($absolute) ?: null, null, true);
        $result = $this->optimize($fake, dirname($path) === '.' ? '' : dirname($path), $preset);
        if (str_ends_with(strtolower($path), '.webp')) {
            $base = preg_replace('/\.webp$/i', '', $path) ?: $path;
            foreach ($result['variants'] as $name => $generated) {
                $target = $name === 'large' ? $path : $base.'-'.$name.'.webp';
                if ($generated !== $target) { $disk->copy($generated, $target); $disk->delete($generated); }
                $result['variants'][$name] = $target;
            }
            $result['path'] = $path;
        }
        return $result;
    }

    public function inspectStored(string $path): array
    {
        $disk = Storage::disk(config('media.disk', 'public'));
        if (! $disk->exists($path)) return ['status' => 'missing', 'format' => null, 'needs_optimization' => false, 'variants' => []];
        $absolute = $disk->path($path); $type = @exif_imagetype($absolute); $format = $type ? image_type_to_mime_type($type) : null;
        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) return ['status' => 'unsupported', 'format' => $format, 'needs_optimization' => false, 'variants' => []];
        if (! function_exists('imagewebp')) return ['status' => 'processor_unavailable', 'format' => $format, 'needs_optimization' => true, 'variants' => []];
        $variants = [];
        foreach (array_keys(config('media.presets', [])) as $name) {
            $isWebp = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp';
            $candidate = $isWebp ? ($name === 'large' ? $path : (preg_replace('/\.webp$/i', '-'.$name.'.webp', $path) ?: $path)) : null;
            $variants[$name] = $candidate !== null && $disk->exists($candidate);
        }
        $complete = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp' && ! in_array(false, $variants, true);
        return ['status' => $complete ? 'already_optimized' : 'eligible', 'format' => $format, 'needs_optimization' => ! $complete, 'variants' => $variants];
    }

    public function variantUrl(?string $path, string $variant = 'large'): ?string
    {
        if (! $path) return null;
        if (preg_match('#^(https?:)?//#i', $path)) return $path;
        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#i', '', $path) ?: $path;

        $cacheKey = $path.'|'.$variant;
        $requestCache = $this->requestCache('variant_urls');
        if (array_key_exists($cacheKey, $requestCache)) {
            return $requestCache[$cacheKey];
        }
        if (array_key_exists($cacheKey, $this->variantUrlCache)) {
            return $this->variantUrlCache[$cacheKey];
        }

        $diskName = config('media.disk', 'public');
        $disk = Storage::disk($diskName);
        $candidate = $path;
        if ($variant !== 'large') {
            $candidate = preg_replace('/\.webp$/i', '-'.$variant.'.webp', $path) ?: $path;
        }

        $resolved = $this->diskExists($diskName, $candidate)
            ? $candidate
            : $path;
        if (! $this->diskExists($diskName, $resolved)) {
            return $this->rememberVariantUrl($cacheKey, null);
        }
        $url = $diskName === 'public' && config('filesystems.disks.public.driver') === 'local'
            ? '/storage/'.ltrim($resolved, '/')
            : $disk->url($resolved);

        return $this->rememberVariantUrl($cacheKey, $url);
    }

    private function diskExists(string $diskName, string $path): bool
    {
        $key = $diskName.'|'.$path;
        $requestCache = $this->requestCache('exists');
        if (array_key_exists($key, $requestCache)) {
            return $requestCache[$key];
        }
        if (array_key_exists($key, $this->existsCache)) {
            return $this->existsCache[$key];
        }

        $exists = Storage::disk($diskName)->exists($path);
        $this->existsCache[$key] = $exists;
        $this->storeRequestCache('exists', $key, $exists);

        return $exists;
    }

    private function rememberVariantUrl(string $key, ?string $url): ?string
    {
        $this->variantUrlCache[$key] = $url;
        $this->storeRequestCache('variant_urls', $key, $url);

        return $url;
    }

    private function requestCache(string $bucket): array
    {
        if (! app()->bound('request')) {
            return [];
        }

        return (array) request()->attributes->get('media_optimization_'.$bucket, []);
    }

    private function storeRequestCache(string $bucket, string $key, mixed $value): void
    {
        if (! app()->bound('request')) {
            return;
        }

        $cache = $this->requestCache($bucket);
        $cache[$key] = $value;
        request()->attributes->set('media_optimization_'.$bucket, $cache);
    }

    private function orient($image, string $path, int $type)
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) return $image;
        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
        return match ((int) $orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private function resize($source, int $width, int $height, int $max)
    {
        $scale = min(1, $max / max($width, $height)); $w = max(1, (int) round($width * $scale)); $h = max(1, (int) round($height * $scale)); $target = imagecreatetruecolor($w, $h); imagealphablending($target, false); imagesavealpha($target, true); imagecopyresampled($target, $source, 0, 0, 0, 0, $w, $h, $width, $height); return $target;
    }
}
