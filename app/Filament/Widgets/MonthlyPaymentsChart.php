<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\LineChartWidget;

class MonthlyPaymentsChart extends LineChartWidget
{
    protected ?string $heading = 'Monthly Payments';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected string $color = 'warning';

    public ?string $filter = null;

    protected function getData(): array
    {
        $selectedYear = (int) ($this->filter ?? now()->year);

        $months = [];
        $values = [];

        for ($i = 0; $i < 12; $i++) {
            $date = \Carbon\Carbon::createFromDate($selectedYear, $i + 1, 1);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $months[] = $date->format('M') . "\n" . $date->format('Y');
            $values[] = Payment::whereBetween('paid_date', [$monthStart, $monthEnd])
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Payments',
                    'data' => $values,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected bool $isCollapsible = true;

    protected function getFilters(): ?array
    {
        $currentYear = now()->year;
        $filters = [];
        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
            $filters[(string) $year] = (string) $year;
        }
        return $filters;
    }
}