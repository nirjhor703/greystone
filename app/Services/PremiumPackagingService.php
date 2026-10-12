<?php

namespace App\Services;

use App\Models\PremiumPackagingTier;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class PremiumPackagingService
{
    public function tiersFor(Product $product)
    {
        $query = $product->premium_packaging_tier_override
            ? PremiumPackagingTier::query()->where('product_id', $product->id)
            : PremiumPackagingTier::shared();

        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }
    public function physicalPieces(Product $product, int $quantity): int
    {
        return max(1, $quantity); // Existing products have one piece per unit.
    }

    public function resolve(Product $product, string $type, ?int $tierId, int $quantity): array
    {
        if ($type !== 'premium') {
            return ['type' => 'normal', 'tier' => null, 'charge' => 0.0];
        }

        if (!$product->packaging_type_enabled) {
            throw ValidationException::withMessages(['packaging_type' => 'Premium packaging is unavailable for this product.']);
        }

        $pieces = $this->physicalPieces($product, $quantity);
        $effectiveTiers = $this->tiersFor($product);
        $tier = $tierId
            ? $effectiveTiers->firstWhere('id', $tierId)
            : null;

        if ($tier) {
            if ($pieces < $tier->min_pieces || $pieces > $tier->max_pieces) {
                throw ValidationException::withMessages(['packaging_tier_id' => 'The selected packaging tier does not support this piece count.']);
            }
        } elseif ($effectiveTiers->isNotEmpty()) {
            throw ValidationException::withMessages(['packaging_tier_id' => 'Select a valid packaging tier for this product.']);
        }

        return [
            'type' => 'premium',
            'tier' => $tier,
            'charge' => $tier ? (float) $tier->price : (float) $product->premium_packaging_charge,
        ];
    }

    public function recommended(Product $product, int $quantity): ?PremiumPackagingTier
    {
        $pieces = $this->physicalPieces($product, $quantity);

        return $this->tiersFor($product)->first(
            fn (PremiumPackagingTier $tier): bool => $tier->min_pieces <= $pieces && $tier->max_pieces >= $pieces
        );
    }
}
