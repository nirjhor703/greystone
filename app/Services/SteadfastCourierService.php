<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SteadfastCourierService
{
    public function createConsignment(Order $order): array
    {
        [$apiKey, $secretKey, $baseUrl] = $this->credentials();

        $payload = $this->payload($order);

        try {
            $response = Http::withHeaders([
                'Api-Key' => $apiKey,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
            ])
                ->acceptJson()
                ->timeout(20)
                ->post($baseUrl.'/create_order', $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Could not connect to Steadfast Courier.',
                previous: $exception
            );
        }

        $data = $response->json();

        if (!$response->successful()) {
            throw new RuntimeException(
                $data['message']
                    ?? 'Steadfast Courier rejected the request.'
            );
        }

        return is_array($data) ? $data : [];
    }

    public function deliveryStatus(Order $order): array
    {
        [$apiKey, $secretKey, $baseUrl] = $this->credentials();

        $path = $order->steadfast_consignment_id
            ? '/status_by_cid/'.$order->steadfast_consignment_id
            : '/status_by_invoice/'.$order->invoice_number;

        try {
            $response = Http::withHeaders([
                'Api-Key' => $apiKey,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
            ])
                ->acceptJson()
                ->timeout(20)
                ->get($baseUrl.$path);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Could not connect to Steadfast Courier.',
                previous: $exception
            );
        }

        $data = $response->json();

        if (!$response->successful()) {
            throw new RuntimeException(
                $data['message']
                    ?? 'Could not fetch Steadfast delivery status.'
            );
        }

        return is_array($data) ? $data : [];
    }

    public function payload(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'invoice' => $order->invoice_number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->phone,
            'alternative_phone' => $order->alternative_phone,
            'recipient_email' => $order->customer_email,
            'recipient_address' => $order->full_address,
            'cod_amount' => (float) $order->grand_total,
            'note' => $order->order_note,
            'item_description' => $this->itemDescription($order),
            'total_lot' => (int) $order->items->sum('quantity'),
            'delivery_type' => 0,
        ];
    }

    private function itemDescription(Order $order): string
    {
        return $order->items
            ->map(function ($item): string {
                $parts = [
                    $item->product_name,
                    $item->color ? 'Color: '.$item->color : null,
                    $item->size ? 'Size: '.$item->size : null,
                    'Qty: '.$item->quantity,
                ];

                return collect($parts)
                    ->filter()
                    ->join(', ');
            })
            ->join(' | ');
    }

    private function credentials(): array
    {
        $apiKey = config('services.steadfast.api_key');
        $secretKey = config('services.steadfast.secret_key');
        $baseUrl = rtrim(
            (string) config('services.steadfast.base_url'),
            '/'
        );

        if (!$apiKey || !$secretKey || !$baseUrl) {
            throw new RuntimeException(
                'Steadfast API credentials are not configured.'
            );
        }

        return [$apiKey, $secretKey, $baseUrl];
    }
}
