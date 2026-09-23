<?php

namespace Tests\Unit;

use App\Services\InvestmentSettlementCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class InvestmentSettlementCalculatorTest extends TestCase
{
    public function test_investor_gets_monthly_profit_by_weighted_active_capital(): void
    {
        $calculator = new InvestmentSettlementCalculator();

        $investors = collect([
            (object) ['id' => 1, 'name' => 'Tahiyat Ahmed', 'is_active' => true],
        ]);

        $entries = collect([
            (object) [
                'investment_investor_id' => 1,
                'entry_type' => 'investor_investment',
                'entry_date' => CarbonImmutable::parse('2026-09-01'),
                'amount' => 10000,
                'status' => 'active',
            ],
        ]);

        $rows = $calculator->calculate($investors, $entries, '2026-09', 5000);

        $this->assertCount(1, $rows);
        $this->assertSame(100.0, $rows[0]['share_percent']);
        $this->assertSame(5000.0, $rows[0]['payable']);
    }

    public function test_investment_counts_from_active_date_not_receive_date(): void
    {
        $calculator = new InvestmentSettlementCalculator();

        $investors = collect([
            (object) ['id' => 1, 'name' => 'Pending Investor', 'is_active' => true],
        ]);

        $entries = collect([
            (object) [
                'investment_investor_id' => 1,
                'entry_type' => 'investor_investment',
                'entry_date' => CarbonImmutable::parse('2026-09-01'),
                'active_date' => CarbonImmutable::parse('2026-09-16'),
                'amount' => 10000,
                'status' => 'active',
            ],
        ]);

        $rows = $calculator->calculate($investors, $entries, '2026-09', 3000);

        $this->assertSame(5000.0, $rows[0]['average_capital']);
        $this->assertSame(3000.0, $rows[0]['payable']);
    }

    public function test_inactive_investor_share_is_reserved_not_redistributed(): void
    {
        $calculator = new InvestmentSettlementCalculator();

        $investors = collect([
            (object) ['id' => 1, 'name' => 'Active Investor', 'is_active' => true],
            (object) ['id' => 2, 'name' => 'Paused Investor', 'is_active' => false],
        ]);

        $entries = collect([
            (object) [
                'investment_investor_id' => 1,
                'entry_type' => 'investor_investment',
                'entry_date' => CarbonImmutable::parse('2026-09-01'),
                'amount' => 10000,
                'status' => 'active',
            ],
            (object) [
                'investment_investor_id' => 2,
                'entry_type' => 'investor_investment',
                'entry_date' => CarbonImmutable::parse('2026-09-01'),
                'amount' => 10000,
                'status' => 'active',
            ],
        ]);

        $rows = $calculator->calculate($investors, $entries, '2026-09', 5000);

        $this->assertSame(50.0, $rows[0]['share_percent']);
        $this->assertSame(2500.0, $rows[0]['payable']);
        $this->assertSame(50.0, $rows[1]['share_percent']);
        $this->assertSame(0.0, $rows[1]['payable']);
    }
}
