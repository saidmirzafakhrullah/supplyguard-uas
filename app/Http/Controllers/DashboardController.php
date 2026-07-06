<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $summary = [
            'countries' => 250,
            'ports' => 28,
            'news' => 16,
            'average_risk' => 42,
            'low_risk' => 4,
            'medium_risk' => 6,
            'high_risk' => 2,
        ];

        $riskLabels = [
            'Indonesia',
            'China',
            'Germany',
            'Australia',
            'Japan',
            'Singapore'
        ];

        $riskData = [34, 58, 22, 41, 29, 18];

        $latestNews = [
            [
                'title' => 'Global shipping delays increase due to port congestion',
                'country' => 'China',
                'sentiment' => 'Negative',
                'risk' => 'High'
            ],
            [
                'title' => 'Export activity improves after currency stabilization',
                'country' => 'Germany',
                'sentiment' => 'Positive',
                'risk' => 'Low'
            ],
            [
                'title' => 'Heavy rain may affect logistics operations',
                'country' => 'Indonesia',
                'sentiment' => 'Neutral',
                'risk' => 'Medium'
            ],
        ];

        return view('dashboard', compact(
            'summary',
            'riskLabels',
            'riskData',
            'latestNews'
        ));
    }
}