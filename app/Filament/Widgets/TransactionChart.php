<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class TransactionChart extends ChartWidget
{
    protected ?string $heading = 'Transactions (30 Days)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Ambil data transaksi per hari selama 30 hari
        $data = Trend::query(
            Transaction::query()->where('status', 'success')
        )
            ->between(
                start: now()->startOfMonth(),
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        // Return data untuk chart
        return [
            'datasets' => [
                [
                    'label' => 'Total Transactions',
                    'data' => $data->map(
                        fn (TrendValue $value) => $value->aggregate
                    ),
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(
                fn (TrendValue $value) => $value->date
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}