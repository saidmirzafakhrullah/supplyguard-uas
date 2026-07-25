<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class CurrencyController extends Controller
{
    public function index()
    {
        $countries = Country::query()->orderBy('name')->get()
            ->map(fn (Country $country) => [
                'name' => $country->name,
                'official_name' => $country->official_name ?: $country->name,
                'code' => $country->code,
                'capital' => $country->capital ?: '-',
                'region' => $country->region ?: '-',
                'subregion' => $country->subregion ?: '-',
                'population' => $country->population,
                'currency_code' => $country->currency_code ?: 'N/A',
                'currency_name' => $country->currency_name ?: 'No official currency data',
                'flag' => $country->flag ?: '',
            ])->all();

        if (count($countries) === 0) {
            $countries = $this->fallbackCountries();
        }

        [$rates, $previousRates, $dataSource, $rateDate] = $this->getExchangeData();
        $apiStatus = $dataSource === 'Live API'
            ? "Kurs aktual berhasil dimuat dari Exchange Rate API ({$rateDate})."
            : 'Exchange Rate API gagal. Sistem memakai snapshot kurs terakhir yang tersimpan.';

        $countries = collect($countries)
            ->map(function ($country) use ($rates, $previousRates, $dataSource) {
                return $this->addCurrencyImpact($country, $rates, $previousRates, $dataSource);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return view('currency.index', compact('countries', 'apiStatus'));
    }

    private function mapRestCountries(array $data)
    {
        return collect($data)
            ->map(function ($country) {
                $currencyCode = 'N/A';
                $currencyName = 'No official currency data';

                if (!empty($country['currencies'])) {
                    $firstCode = array_key_first($country['currencies']);
                    $currencyCode = $firstCode;
                    $currencyName = $country['currencies'][$firstCode]['name'] ?? 'Unknown currency';
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'flag' => $country['flags']['png'] ?? '',
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function mapMledozeCountries(array $data)
    {
        return collect($data)
            ->map(function ($country) {
                $currencyCode = 'N/A';
                $currencyName = 'No official currency data';

                if (!empty($country['currencies'])) {
                    $firstCode = array_key_first($country['currencies']);
                    $currencyCode = $firstCode;
                    $currencyName = $country['currencies'][$firstCode]['name'] ?? 'Unknown currency';
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'flag' => '',
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function addCurrencyImpact(
        array $country,
        array $rates,
        array $previousRates,
        string $dataSource
    )
    {
        $currencyCode = $country['currency_code'];

        if ($currencyCode === 'N/A') {
            $country['exchange_rate'] = 0;
            $country['volatility'] = 0;
            $country['exchange_change'] = 0;
            $country['currency_risk'] = 0;
            $country['category'] = 'No Data';
            $country['badge'] = 'bg-secondary text-white';
            $country['recommendation'] = 'Currency data is not available for this country.';

            return $country;
        }

        if (!isset($rates[$currencyCode])) {
            $country['exchange_rate'] = null;
            $country['volatility'] = 0;
            $country['exchange_change'] = 0;
            $country['currency_risk'] = 0;
            $country['category'] = 'No Data';
            $country['badge'] = 'bg-secondary text-white';
            $country['recommendation'] = 'Nilai tukar mata uang ini tidak tersedia dari API.';
            $country['data_source'] = 'No Data';
            return $country;
        }

        $exchangeRate = (float) $rates[$currencyCode];
        $previousRate = (float) ($previousRates[$currencyCode] ?? $exchangeRate);
        $exchangeChange = $previousRate > 0
            ? round((($exchangeRate - $previousRate) / $previousRate) * 100, 2)
            : 0;
        $volatility = round(abs($exchangeChange), 2);

        $currencyRisk = round(
            ($volatility * 0.60) +
            (abs($exchangeChange) * 4),
            2
        );

        if ($currencyRisk > 100) {
            $currencyRisk = 100;
        }

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation = 'Currency condition is stable for import transaction.';

        if ($currencyRisk > 25 && $currencyRisk <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Monitor exchange rate before import transaction.';
        } elseif ($currencyRisk > 50 && $currencyRisk <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation = 'Prepare currency buffer or alternative supplier country.';
        } elseif ($currencyRisk > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Delay import transaction until currency risk decreases.';
        }

        $country['exchange_rate'] = $exchangeRate;
        $country['volatility'] = $volatility;
        $country['exchange_change'] = $exchangeChange;
        $country['currency_risk'] = $currencyRisk;
        $country['category'] = $category;
        $country['badge'] = $badge;
        $country['recommendation'] = $recommendation;
        $country['data_source'] = $dataSource;

        return $country;
    }

    private function getExchangeData(): array
    {
        $endpoint = 'https://open.er-api.com/v6/latest/USD';
        $today = now()->toDateString();
        $latestStoredDate = ExchangeRate::query()->max('rate_date');
        $latestStoredRates = $latestStoredDate
            ? ExchangeRate::query()->whereDate('rate_date', substr((string) $latestStoredDate, 0, 10))
                ->pluck('rate', 'currency_code')->map(fn ($rate) => (float) $rate)->all()
            : [];
        $previousDate = ExchangeRate::query()
            ->whereDate('rate_date', '<', $today)
            ->max('rate_date');
        $previousRates = $previousDate
            ? ExchangeRate::query()->whereDate('rate_date', substr((string) $previousDate, 0, 10))
                ->pluck('rate', 'currency_code')->map(fn ($rate) => (float) $rate)->all()
            : $latestStoredRates;

        try {
            $response = Http::acceptJson()->timeout(15)->retry(2, 300)->get($endpoint);
            $rates = $response->successful() ? $response->json('rates', []) : [];
            if (!is_array($rates) || $rates === []) {
                throw new \RuntimeException('API tidak mengembalikan data kurs.');
            }

            $now = now();
            $rows = collect($rates)->map(fn ($rate, $code) => [
                'base_currency' => 'USD',
                'currency_code' => strtoupper((string) $code),
                'rate' => (float) $rate,
                'rate_date' => $today,
                'source' => 'Exchange Rate API',
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all();
            ExchangeRate::query()->upsert(
                $rows,
                ['base_currency', 'currency_code', 'rate_date'],
                ['rate', 'source', 'updated_at']
            );

            Cache::put('supplyguard.currency.last_snapshot', $rates, now()->addDays(7));
            Cache::put('supplyguard.currency.last_date', $today, now()->addDays(7));

            return [$rates, $previousRates, 'Live API', $today];
        } catch (Throwable $exception) {
            report($exception);
            return [
                $latestStoredRates,
                $previousRates ?: $latestStoredRates,
                'Database Cache',
                $latestStoredDate ? substr((string) $latestStoredDate, 0, 10) : '-',
            ];
        }
    }

    private function fallbackCountries()
    {
        return [
            [
                'name' => 'Indonesia',
                'official_name' => 'Republic of Indonesia',
                'code' => 'ID',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'population' => 273523621,
                'currency_code' => 'IDR',
                'currency_name' => 'Indonesian Rupiah',
                'flag' => '',
            ],
            [
                'name' => 'Germany',
                'official_name' => 'Federal Republic of Germany',
                'code' => 'DE',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'population' => 83240525,
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'flag' => '',
            ],
            [
                'name' => 'China',
                'official_name' => 'People’s Republic of China',
                'code' => 'CN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 1402112000,
                'currency_code' => 'CNY',
                'currency_name' => 'Chinese Yuan',
                'flag' => '',
            ],
            [
                'name' => 'Japan',
                'official_name' => 'Japan',
                'code' => 'JP',
                'capital' => 'Tokyo',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 125800000,
                'currency_code' => 'JPY',
                'currency_name' => 'Japanese Yen',
                'flag' => '',
            ],
        ];
    }
}
