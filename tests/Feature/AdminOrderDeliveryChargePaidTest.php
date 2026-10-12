<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AdminOrderDeliveryChargePaidTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_charge_paid_amount_becomes_order_delivery_charge_and_paid_amount(): void
    {
        $controller = new OrderController();
        $method = new ReflectionMethod($controller, 'orderData');
        $method->setAccessible(true);

        $data = $method->invoke($controller, [
            'brand_id' => 1,
            'customer_name' => 'Test Customer',
            'phone' => '01700000000',
            'alternative_phone' => null,
            'customer_email' => null,
            'delivery_area' => 'inside_dhaka',
            'district' => 'Dhaka',
            'area_thana' => 'Mirpur',
            'road_no' => '10',
            'house_no' => '20',
            'full_address' => 'House- 20, Road- 10, Mirpur, Dhaka',
            'order_note' => null,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_DELIVERY_CHARGE_PAID,
            'delivery_charge_paid' => 150,
            'order_channel' => Order::CHANNEL_OFFLINE,
            'order_source' => Order::SOURCE_PHONE,
            'source_note' => null,
        ], 1000.0, 0.0);

        $this->assertSame(150.0, $data['delivery_charge']);
        $this->assertSame(150.0, $data['delivery_charge_paid']);
        $this->assertSame(1150.0, $data['grand_total']);
    }

    public function test_manual_discount_is_subtracted_separately_from_order_total(): void
    {
        $controller = new OrderController();
        $method = new ReflectionMethod($controller, 'orderData');
        $method->setAccessible(true);

        $data = $method->invoke($controller, [
            'brand_id' => 1,
            'customer_name' => 'Test Customer',
            'phone' => '01700000000',
            'alternative_phone' => null,
            'customer_email' => null,
            'delivery_area' => 'inside_dhaka',
            'district' => 'Dhaka',
            'area_thana' => 'Mirpur',
            'road_no' => '10',
            'house_no' => '20',
            'full_address' => 'House- 20, Road- 10, Mirpur, Dhaka',
            'order_note' => null,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'delivery_charge_paid' => null,
            'manual_discount_amount' => 100,
            'order_channel' => Order::CHANNEL_OFFLINE,
            'order_source' => Order::SOURCE_PHONE,
            'source_note' => null,
        ], 1000.0, 50.0);

        $this->assertSame(100.0, $data['manual_discount_amount']);
        $this->assertSame(930.0, $data['grand_total']);
    }
}
