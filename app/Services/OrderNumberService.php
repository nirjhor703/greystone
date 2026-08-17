<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderNumberService
{
    public function generateInvoiceNumber(Brand $brand): string
    {
        $prefix = $this->invoicePrefix($brand);
        $pattern = $prefix.'-INV-%';

        $lastInvoice = Order::query()
            ->where('brand_id', $brand->id)
            ->where('invoice_number', 'like', $pattern)
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextSerial = 1;

        if (
            is_string($lastInvoice)
            && preg_match(
                '/^'.preg_quote($prefix, '/').'-INV-(\d+)$/',
                $lastInvoice,
                $matches
            )
        ) {
            $nextSerial = ((int) $matches[1]) + 1;
        }

        do {
            $invoiceNumber = sprintf(
                '%s-INV-%09d',
                $prefix,
                $nextSerial
            );

            $nextSerial++;
        } while (
            Order::query()
                ->where('invoice_number', $invoiceNumber)
                ->exists()
        );

        return $invoiceNumber;
    }

    public function invoicePrefix(Brand $brand): string
    {
        $slug = Str::slug($brand->slug ?: $brand->name);

        return match ($slug) {
            'grey-stone' => 'GS',
            'blue-shade', 'blue-shades' => 'BS',
            'pink-touch' => 'PT',
            default => $this->initials($brand->name),
        };
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->join('');

        return Str::substr($initials ?: 'GS', 0, 3);
    }
}
