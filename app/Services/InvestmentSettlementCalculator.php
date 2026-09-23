<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class InvestmentSettlementCalculator
{
    /**
     * @param Collection<int, object> $investors
     * @param Collection<int, object> $entries
     * @return array<int, array<string, mixed>>
     */
    public function calculate(Collection $investors, Collection $entries, string $month, float $monthlyProfit): array
    {
        $start = CarbonImmutable::parse($month.'-01')->startOfDay();
        $end = $start->endOfMonth();
        $daysInMonth = $start->daysInMonth;

        $investorWeights = $investors
            ->mapWithKeys(fn ($investor) => [(int) $investor->id => 0.0])
            ->all();
        $totalWeight = 0.0;

        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            $dailyInvestorCapital = array_fill_keys(array_keys($investorWeights), 0.0);
            $dailyTotalCapital = 0.0;

            foreach ($entries as $entry) {
                if (($entry->status ?? 'active') === 'cancelled') {
                    continue;
                }

                $entryDate = CarbonImmutable::parse($entry->active_date ?? $entry->entry_date)->startOfDay();

                if ($entryDate->gt($day)) {
                    continue;
                }

                $amount = (float) $entry->amount;
                $type = (string) $entry->entry_type;
                $investorId = $entry->investment_investor_id ? (int) $entry->investment_investor_id : null;

                if ($type === 'investor_investment' && $investorId) {
                    $dailyInvestorCapital[$investorId] = ($dailyInvestorCapital[$investorId] ?? 0.0) + $amount;
                    $dailyTotalCapital += $amount;
                }

                if ($type === 'capital_return' && $investorId) {
                    $dailyInvestorCapital[$investorId] = max(0.0, ($dailyInvestorCapital[$investorId] ?? 0.0) - $amount);
                    $dailyTotalCapital = max(0.0, $dailyTotalCapital - $amount);
                }
            }

            foreach ($dailyInvestorCapital as $investorId => $capital) {
                $investorWeights[$investorId] = ($investorWeights[$investorId] ?? 0.0) + $capital;
            }

            $totalWeight += $dailyTotalCapital;
        }

        return $investors
            ->filter(fn ($investor) => array_key_exists((int) $investor->id, $investorWeights))
            ->map(function ($investor) use ($investorWeights, $totalWeight, $monthlyProfit, $daysInMonth): array {
                $investorWeight = (float) ($investorWeights[(int) $investor->id] ?? 0);
                $averageCapital = $daysInMonth > 0 ? $investorWeight / $daysInMonth : 0.0;
                $sharePercent = $totalWeight > 0 ? ($investorWeight / $totalWeight) * 100 : 0.0;
                $payable = (bool) ($investor->is_active ?? true)
                    ? ($monthlyProfit * $sharePercent) / 100
                    : 0.0;

                return [
                    'investor' => $investor,
                    'average_capital' => round($averageCapital, 2),
                    'share_percent' => round($sharePercent, 4),
                    'payable' => round($payable, 2),
                ];
            })
            ->values()
            ->all();
    }
}
