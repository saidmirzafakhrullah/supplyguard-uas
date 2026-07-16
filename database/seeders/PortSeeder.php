<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ports')->truncate();

        $ports = [
            ['Pelabuhan Tanjung Priok', 'Indonesia', 'IDN', 'Jakarta', 'Asia', -6.1040, 106.8860],
            ['Pelabuhan Tanjung Perak', 'Indonesia', 'IDN', 'Surabaya', 'Asia', -7.2038, 112.7326],
            ['Pelabuhan Belawan', 'Indonesia', 'IDN', 'Medan', 'Asia', 3.7856, 98.6942],
            ['Pelabuhan Makassar', 'Indonesia', 'IDN', 'Makassar', 'Asia', -5.1333, 119.4167],
            ['Pelabuhan Batam Center', 'Indonesia', 'IDN', 'Batam', 'Asia', 1.1301, 104.0529],
            ['Port of Singapore', 'Singapore', 'SGP', 'Singapore', 'Asia', 1.2640, 103.8400],
            ['Port Klang', 'Malaysia', 'MYS', 'Klang', 'Asia', 3.0000, 101.4000],
            ['Port of Tanjung Pelepas', 'Malaysia', 'MYS', 'Johor', 'Asia', 1.3620, 103.5480],
            ['Laem Chabang Port', 'Thailand', 'THA', 'Chonburi', 'Asia', 13.0833, 100.8833],
            ['Port of Bangkok', 'Thailand', 'THA', 'Bangkok', 'Asia', 13.7000, 100.5833],
            ['Port of Manila', 'Philippines', 'PHL', 'Manila', 'Asia', 14.5833, 120.9667],
            ['Port of Cebu', 'Philippines', 'PHL', 'Cebu', 'Asia', 10.2929, 123.9058],
            ['Port of Ho Chi Minh City', 'Vietnam', 'VNM', 'Ho Chi Minh City', 'Asia', 10.7769, 106.7009],
            ['Port of Hai Phong', 'Vietnam', 'VNM', 'Hai Phong', 'Asia', 20.8449, 106.6881],
            ['Port of Yangon', 'Myanmar', 'MMR', 'Yangon', 'Asia', 16.8000, 96.1500],
            ['Port of Shanghai', 'China', 'CHN', 'Shanghai', 'Asia', 31.2300, 121.4740],
            ['Port of Ningbo-Zhoushan', 'China', 'CHN', 'Ningbo', 'Asia', 29.8683, 121.5440],
            ['Port of Shenzhen', 'China', 'CHN', 'Shenzhen', 'Asia', 22.5431, 114.0579],
            ['Port of Guangzhou', 'China', 'CHN', 'Guangzhou', 'Asia', 23.1291, 113.2644],
            ['Port of Qingdao', 'China', 'CHN', 'Qingdao', 'Asia', 36.0671, 120.3826],
            ['Port of Tianjin', 'China', 'CHN', 'Tianjin', 'Asia', 39.0842, 117.2009],
            ['Port of Hong Kong', 'Hong Kong', 'HKG', 'Hong Kong', 'Asia', 22.3193, 114.1694],
            ['Port of Busan', 'South Korea', 'KOR', 'Busan', 'Asia', 35.0951, 129.0403],
            ['Port of Incheon', 'South Korea', 'KOR', 'Incheon', 'Asia', 37.4563, 126.7052],
            ['Port of Yokohama', 'Japan', 'JPN', 'Yokohama', 'Asia', 35.4500, 139.6500],
            ['Port of Tokyo', 'Japan', 'JPN', 'Tokyo', 'Asia', 35.6500, 139.7700],
            ['Port of Kobe', 'Japan', 'JPN', 'Kobe', 'Asia', 34.6901, 135.1955],
            ['Port of Osaka', 'Japan', 'JPN', 'Osaka', 'Asia', 34.6937, 135.5023],
            ['Port of Kaohsiung', 'Taiwan', 'TWN', 'Kaohsiung', 'Asia', 22.6273, 120.3014],
            ['Port of Keelung', 'Taiwan', 'TWN', 'Keelung', 'Asia', 25.1276, 121.7392],
            ['Port of Colombo', 'Sri Lanka', 'LKA', 'Colombo', 'Asia', 6.9271, 79.8612],
            ['Port of Chennai', 'India', 'IND', 'Chennai', 'Asia', 13.0827, 80.2707],
            ['Jawaharlal Nehru Port', 'India', 'IND', 'Mumbai', 'Asia', 18.9490, 72.9512],
            ['Port of Mundra', 'India', 'IND', 'Mundra', 'Asia', 22.8397, 69.7211],
            ['Port of Karachi', 'Pakistan', 'PAK', 'Karachi', 'Asia', 24.8607, 67.0011],
            ['Port Qasim', 'Pakistan', 'PAK', 'Karachi', 'Asia', 24.7766, 67.3466],
            ['Port of Chittagong', 'Bangladesh', 'BGD', 'Chittagong', 'Asia', 22.3569, 91.7832],
            ['Port of Dubai', 'United Arab Emirates', 'ARE', 'Dubai', 'Asia', 25.2048, 55.2708],
            ['Jebel Ali Port', 'United Arab Emirates', 'ARE', 'Dubai', 'Asia', 25.0118, 55.0612],
            ['Port of Doha', 'Qatar', 'QAT', 'Doha', 'Asia', 25.2854, 51.5310],
            ['Port of Dammam', 'Saudi Arabia', 'SAU', 'Dammam', 'Asia', 26.4207, 50.0888],
            ['Jeddah Islamic Port', 'Saudi Arabia', 'SAU', 'Jeddah', 'Asia', 21.4858, 39.1925],
            ['Port of Hamburg', 'Germany', 'DEU', 'Hamburg', 'Europe', 53.5460, 9.9660],
            ['Port of Bremen', 'Germany', 'DEU', 'Bremen', 'Europe', 53.0793, 8.8017],
            ['Port of Rotterdam', 'Netherlands', 'NLD', 'Rotterdam', 'Europe', 51.9500, 4.1400],
            ['Port of Amsterdam', 'Netherlands', 'NLD', 'Amsterdam', 'Europe', 52.3676, 4.9041],
            ['Port of Antwerp', 'Belgium', 'BEL', 'Antwerp', 'Europe', 51.2194, 4.4025],
            ['Port of Zeebrugge', 'Belgium', 'BEL', 'Bruges', 'Europe', 51.3300, 3.2000],
            ['Port of Le Havre', 'France', 'FRA', 'Le Havre', 'Europe', 49.4944, 0.1079],
            ['Port of Marseille', 'France', 'FRA', 'Marseille', 'Europe', 43.2965, 5.3698],
            ['Port of Barcelona', 'Spain', 'ESP', 'Barcelona', 'Europe', 41.3851, 2.1734],
            ['Port of Valencia', 'Spain', 'ESP', 'Valencia', 'Europe', 39.4699, -0.3763],
            ['Port of Algeciras', 'Spain', 'ESP', 'Algeciras', 'Europe', 36.1408, -5.4562],
            ['Port of Genoa', 'Italy', 'ITA', 'Genoa', 'Europe', 44.4056, 8.9463],
            ['Port of Trieste', 'Italy', 'ITA', 'Trieste', 'Europe', 45.6495, 13.7768],
            ['Port of Piraeus', 'Greece', 'GRC', 'Piraeus', 'Europe', 37.9420, 23.6469],
            ['Port of Istanbul', 'Turkey', 'TUR', 'Istanbul', 'Europe', 41.0082, 28.9784],
            ['Port of Izmir', 'Turkey', 'TUR', 'Izmir', 'Europe', 38.4237, 27.1428],
            ['Port of Felixstowe', 'United Kingdom', 'GBR', 'Felixstowe', 'Europe', 51.9542, 1.3511],
            ['Port of London', 'United Kingdom', 'GBR', 'London', 'Europe', 51.5072, -0.1276],
            ['Port of Liverpool', 'United Kingdom', 'GBR', 'Liverpool', 'Europe', 53.4084, -2.9916],
            ['Port of Dublin', 'Ireland', 'IRL', 'Dublin', 'Europe', 53.3498, -6.2603],
            ['Port of Gothenburg', 'Sweden', 'SWE', 'Gothenburg', 'Europe', 57.7089, 11.9746],
            ['Port of Stockholm', 'Sweden', 'SWE', 'Stockholm', 'Europe', 59.3293, 18.0686],
            ['Port of Oslo', 'Norway', 'NOR', 'Oslo', 'Europe', 59.9139, 10.7522],
            ['Port of Copenhagen', 'Denmark', 'DNK', 'Copenhagen', 'Europe', 55.6761, 12.5683],
            ['Port of Gdansk', 'Poland', 'POL', 'Gdansk', 'Europe', 54.3520, 18.6466],
            ['Port of Constanta', 'Romania', 'ROU', 'Constanta', 'Europe', 44.1598, 28.6348],
            ['Port of Los Angeles', 'United States', 'USA', 'Los Angeles', 'Americas', 33.7400, -118.2700],
            ['Port of Long Beach', 'United States', 'USA', 'Long Beach', 'Americas', 33.7701, -118.1937],
            ['Port of New York and New Jersey', 'United States', 'USA', 'New York', 'Americas', 40.7128, -74.0060],
            ['Port of Houston', 'United States', 'USA', 'Houston', 'Americas', 29.7604, -95.3698],
            ['Port of Savannah', 'United States', 'USA', 'Savannah', 'Americas', 32.0809, -81.0912],
            ['Port of Seattle', 'United States', 'USA', 'Seattle', 'Americas', 47.6062, -122.3321],
            ['Port of Oakland', 'United States', 'USA', 'Oakland', 'Americas', 37.8044, -122.2712],
            ['Port of Miami', 'United States', 'USA', 'Miami', 'Americas', 25.7617, -80.1918],
            ['Port of Vancouver', 'Canada', 'CAN', 'Vancouver', 'Americas', 49.2827, -123.1207],
            ['Port of Montreal', 'Canada', 'CAN', 'Montreal', 'Americas', 45.5017, -73.5673],
            ['Port of Halifax', 'Canada', 'CAN', 'Halifax', 'Americas', 44.6488, -63.5752],
            ['Port of Manzanillo', 'Mexico', 'MEX', 'Manzanillo', 'Americas', 19.0522, -104.3158],
            ['Port of Veracruz', 'Mexico', 'MEX', 'Veracruz', 'Americas', 19.1738, -96.1342],
            ['Port of Lazaro Cardenas', 'Mexico', 'MEX', 'Lazaro Cardenas', 'Americas', 17.9568, -102.1943],
            ['Port of Balboa', 'Panama', 'PAN', 'Balboa', 'Americas', 8.9500, -79.5667],
            ['Port of Colon', 'Panama', 'PAN', 'Colon', 'Americas', 9.3590, -79.9014],
            ['Port of Santos', 'Brazil', 'BRA', 'Santos', 'Americas', -23.9608, -46.3336],
            ['Port of Rio de Janeiro', 'Brazil', 'BRA', 'Rio de Janeiro', 'Americas', -22.9068, -43.1729],
            ['Port of Paranagua', 'Brazil', 'BRA', 'Paranagua', 'Americas', -25.5205, -48.5095],
            ['Port of Buenos Aires', 'Argentina', 'ARG', 'Buenos Aires', 'Americas', -34.6037, -58.3816],
            ['Port of Rosario', 'Argentina', 'ARG', 'Rosario', 'Americas', -32.9442, -60.6505],
            ['Port of Valparaiso', 'Chile', 'CHL', 'Valparaiso', 'Americas', -33.0472, -71.6127],
            ['Port of San Antonio', 'Chile', 'CHL', 'San Antonio', 'Americas', -33.5947, -71.6075],
            ['Port of Callao', 'Peru', 'PER', 'Callao', 'Americas', -12.0464, -77.1428],
            ['Port of Cartagena', 'Colombia', 'COL', 'Cartagena', 'Americas', 10.3910, -75.4794],
            ['Port of Buenaventura', 'Colombia', 'COL', 'Buenaventura', 'Americas', 3.8801, -77.0312],
            ['Port of Guayaquil', 'Ecuador', 'ECU', 'Guayaquil', 'Americas', -2.1894, -79.8891],
            ['Port of Kingston', 'Jamaica', 'JAM', 'Kingston', 'Americas', 17.9712, -76.7928],
            ['Port of Durban', 'South Africa', 'ZAF', 'Durban', 'Africa', -29.8587, 31.0218],
            ['Port of Cape Town', 'South Africa', 'ZAF', 'Cape Town', 'Africa', -33.9249, 18.4241],
            ['Port of Richards Bay', 'South Africa', 'ZAF', 'Richards Bay', 'Africa', -28.7807, 32.0383],
            ['Port of Mombasa', 'Kenya', 'KEN', 'Mombasa', 'Africa', -4.0435, 39.6682],
            ['Port of Dar es Salaam', 'Tanzania', 'TZA', 'Dar es Salaam', 'Africa', -6.7924, 39.2083],
            ['Port of Djibouti', 'Djibouti', 'DJI', 'Djibouti', 'Africa', 11.5721, 43.1456],
            ['Port of Alexandria', 'Egypt', 'EGY', 'Alexandria', 'Africa', 31.2001, 29.9187],
            ['Port Said', 'Egypt', 'EGY', 'Port Said', 'Africa', 31.2653, 32.3019],
            ['Port of Casablanca', 'Morocco', 'MAR', 'Casablanca', 'Africa', 33.5731, -7.5898],
            ['Tanger Med Port', 'Morocco', 'MAR', 'Tangier', 'Africa', 35.7595, -5.8340],
            ['Port of Lagos', 'Nigeria', 'NGA', 'Lagos', 'Africa', 6.5244, 3.3792],
            ['Port Harcourt', 'Nigeria', 'NGA', 'Port Harcourt', 'Africa', 4.8156, 7.0498],
            ['Port of Tema', 'Ghana', 'GHA', 'Tema', 'Africa', 5.6037, -0.1870],
            ['Port of Abidjan', 'Ivory Coast', 'CIV', 'Abidjan', 'Africa', 5.3600, -4.0083],
            ['Port of Dakar', 'Senegal', 'SEN', 'Dakar', 'Africa', 14.7167, -17.4677],
            ['Port of Luanda', 'Angola', 'AGO', 'Luanda', 'Africa', -8.8390, 13.2894],
            ['Port of Maputo', 'Mozambique', 'MOZ', 'Maputo', 'Africa', -25.9692, 32.5732],
            ['Port of Beira', 'Mozambique', 'MOZ', 'Beira', 'Africa', -19.8333, 34.8500],
            ['Port of Sydney', 'Australia', 'AUS', 'Sydney', 'Oceania', -33.8688, 151.2093],
            ['Port of Melbourne', 'Australia', 'AUS', 'Melbourne', 'Oceania', -37.8136, 144.9631],
            ['Port of Brisbane', 'Australia', 'AUS', 'Brisbane', 'Oceania', -27.4705, 153.0260],
            ['Port of Fremantle', 'Australia', 'AUS', 'Fremantle', 'Oceania', -32.0569, 115.7439],
            ['Port of Adelaide', 'Australia', 'AUS', 'Adelaide', 'Oceania', -34.9285, 138.6007],
            ['Port of Auckland', 'New Zealand', 'NZL', 'Auckland', 'Oceania', -36.8485, 174.7633],
            ['Port of Tauranga', 'New Zealand', 'NZL', 'Tauranga', 'Oceania', -37.6878, 176.1651],
            ['Port of Wellington', 'New Zealand', 'NZL', 'Wellington', 'Oceania', -41.2865, 174.7762],
            ['Port Moresby', 'Papua New Guinea', 'PNG', 'Port Moresby', 'Oceania', -9.4438, 147.1803],
            ['Port of Suva', 'Fiji', 'FJI', 'Suva', 'Oceania', -18.1248, 178.4501],
        ];

        $extraCountries = [
            ['Brunei', 'BRN', 'Bandar Seri Begawan', 'Asia', 4.9031, 114.9398],
            ['Cambodia', 'KHM', 'Sihanoukville', 'Asia', 10.6253, 103.5234],
            ['Oman', 'OMN', 'Muscat', 'Asia', 23.5880, 58.3829],
            ['Kuwait', 'KWT', 'Kuwait City', 'Asia', 29.3759, 47.9774],
            ['Bahrain', 'BHR', 'Manama', 'Asia', 26.2235, 50.5876],
            ['Israel', 'ISR', 'Haifa', 'Asia', 32.7940, 34.9896],
            ['Jordan', 'JOR', 'Aqaba', 'Asia', 29.5321, 35.0063],
            ['Lebanon', 'LBN', 'Beirut', 'Asia', 33.8938, 35.5018],
            ['Iran', 'IRN', 'Bandar Abbas', 'Asia', 27.1832, 56.2666],
            ['Iraq', 'IRQ', 'Umm Qasr', 'Asia', 30.0362, 47.9190],
            ['Portugal', 'PRT', 'Lisbon', 'Europe', 38.7223, -9.1393],
            ['Finland', 'FIN', 'Helsinki', 'Europe', 60.1699, 24.9384],
            ['Estonia', 'EST', 'Tallinn', 'Europe', 59.4370, 24.7536],
            ['Latvia', 'LVA', 'Riga', 'Europe', 56.9496, 24.1052],
            ['Lithuania', 'LTU', 'Klaipeda', 'Europe', 55.7033, 21.1443],
            ['Croatia', 'HRV', 'Rijeka', 'Europe', 45.3271, 14.4422],
            ['Slovenia', 'SVN', 'Koper', 'Europe', 45.5481, 13.7302],
            ['Bulgaria', 'BGR', 'Varna', 'Europe', 43.2141, 27.9147],
            ['Ukraine', 'UKR', 'Odesa', 'Europe', 46.4825, 30.7233],
            ['Iceland', 'ISL', 'Reykjavik', 'Europe', 64.1466, -21.9426],
            ['Uruguay', 'URY', 'Montevideo', 'Americas', -34.9011, -56.1645],
            ['Venezuela', 'VEN', 'Puerto Cabello', 'Americas', 10.4700, -68.0100],
            ['Costa Rica', 'CRI', 'Limon', 'Americas', 9.9907, -83.0359],
            ['Guatemala', 'GTM', 'Puerto Quetzal', 'Americas', 13.9250, -90.7817],
            ['Honduras', 'HND', 'Puerto Cortes', 'Americas', 15.8256, -87.9297],
            ['Dominican Republic', 'DOM', 'Santo Domingo', 'Americas', 18.4861, -69.9312],
            ['Cuba', 'CUB', 'Havana', 'Americas', 23.1136, -82.3666],
            ['Trinidad and Tobago', 'TTO', 'Port of Spain', 'Americas', 10.6603, -61.5086],
            ['Suriname', 'SUR', 'Paramaribo', 'Americas', 5.8520, -55.2038],
            ['Guyana', 'GUY', 'Georgetown', 'Americas', 6.8013, -58.1551],
            ['Algeria', 'DZA', 'Algiers', 'Africa', 36.7538, 3.0588],
            ['Tunisia', 'TUN', 'Tunis', 'Africa', 36.8065, 10.1815],
            ['Libya', 'LBY', 'Tripoli', 'Africa', 32.8872, 13.1913],
            ['Sudan', 'SDN', 'Port Sudan', 'Africa', 19.6158, 37.2164],
            ['Ethiopia', 'ETH', 'Dry Port Modjo', 'Africa', 8.5913, 39.1218],
            ['Cameroon', 'CMR', 'Douala', 'Africa', 4.0511, 9.7679],
            ['Gabon', 'GAB', 'Libreville', 'Africa', 0.4162, 9.4673],
            ['Namibia', 'NAM', 'Walvis Bay', 'Africa', -22.9576, 14.5053],
            ['Madagascar', 'MDG', 'Toamasina', 'Africa', -18.1492, 49.4023],
            ['Mauritius', 'MUS', 'Port Louis', 'Africa', -20.1609, 57.5012],
            ['Vanuatu', 'VUT', 'Port Vila', 'Oceania', -17.7333, 168.3167],
            ['Samoa', 'WSM', 'Apia', 'Oceania', -13.8507, -171.7514],
            ['Tonga', 'TON', 'Nukuʻalofa', 'Oceania', -21.1393, -175.2049],
            ['Solomon Islands', 'SLB', 'Honiara', 'Oceania', -9.4456, 159.9729],
            ['Kiribati', 'KIR', 'Tarawa', 'Oceania', 1.4518, 173.0394],
        ];

        $rows = [];
        $now = now();

        foreach ($ports as $index => $port) {
            $rows[] = $this->makePortRow($port, $index, $now);
        }

        $counter = count($rows);

        while ($counter < 200) {
            foreach ($extraCountries as $extra) {
                if ($counter >= 200) {
                    break;
                }

                $portNumber = ($counter % 3) + 1;

                $port = [
                    'Pelabuhan Logistik ' . $extra[0] . ' ' . $portNumber,
                    $extra[0],
                    $extra[1],
                    $extra[2],
                    $extra[3],
                    $extra[4] + (($portNumber - 2) * 0.18),
                    $extra[5] + (($portNumber - 2) * 0.18),
                ];

                $rows[] = $this->makePortRow($port, $counter, $now);
                $counter++;
            }
        }

        DB::table('ports')->insert($rows);
    }

    private function makePortRow(array $port, int $index, $now): array
    {
        $statuses = ['Active', 'Active', 'Active', 'Limited', 'Maintenance'];
        $risks = ['Low', 'Low', 'Medium', 'Medium', 'High'];
        $congestions = ['Low', 'Medium', 'Medium', 'High'];

        return [
            'port_name' => $port[0],
            'country' => $port[1],
            'country_code' => $port[2],
            'city' => $port[3],
            'region' => $port[4],
            'latitude' => $port[5],
            'longitude' => $port[6],
            'status' => $statuses[$index % count($statuses)],
            'capacity' => 120000 + (($index % 18) * 45000),
            'congestion_level' => $congestions[$index % count($congestions)],
            'risk_level' => $risks[$index % count($risks)],
            'notes' => 'Data pelabuhan global untuk monitoring risiko rantai pasok SupplyGuard.',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}