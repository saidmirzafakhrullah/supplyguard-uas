<?php

namespace App\Services;

use App\Models\RiskScore;
use App\Models\Country;
use Illuminate\Support\Collection;

class RiskSnapshotService
{
    public static function countries(): array
    {
        return Country::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country) => [
                'name' => $country->name,
                'official_name' => $country->official_name ?: $country->name,
                'code' => strtoupper((string) $country->code),
                'capital' => $country->capital ?: '-',
                'region' => $country->region ?: 'Unknown',
                'subregion' => $country->subregion ?: '-',
                'population' => (int) $country->population,
                'currency_code' => $country->currency_code ?: '-',
                'currency_name' => $country->currency_name ?: '-',
                'currency' => $country->currency_code ?: '-',
                'flag' => $country->flag,
                'latitude' => (float) $country->latitude,
                'longitude' => (float) $country->longitude,
                'landlocked' => (bool) $country->landlocked,
            ])
            ->all();
    }

    public static function latestByCountry(): Collection
    {
        $latestDate = RiskScore::query()->max('score_date');
        if (!$latestDate) {
            return collect();
        }

        return RiskScore::query()
            ->whereDate('score_date', substr((string) $latestDate, 0, 10))
            ->get()
            ->keyBy('country_code');
    }

    public static function apply(
        array $country,
        Collection $snapshots,
        string $totalKey = 'total_risk'
    ): array {
        $code = strtoupper((string) ($country['code'] ?? ''));
        /** @var RiskScore|null $score */
        $score = $snapshots->get($code);
        if (!$score) {
            return $country;
        }

        foreach ([
            'weather_risk', 'inflation_risk', 'currency_risk',
            'news_risk', 'port_risk',
        ] as $field) {
            $country[$field] = $score->{$field};
        }

        $country[$totalKey] = (float) $score->total_risk;
        $country['category'] = $score->category;
        $country['recommendation'] = $score->recommendation;
        $country['risk_data_source'] = 'Database risk_scores';
        $country['badge'] = match ($score->category) {
            'Low' => 'risk-low',
            'Medium' => 'risk-medium',
            'High' => 'risk-high',
            'Critical' => 'bg-dark text-white',
            default => 'risk-medium',
        };

        return $country;
    }
}
