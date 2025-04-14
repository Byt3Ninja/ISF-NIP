<?php

namespace App\Http\Controllers\Judger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class JudgerScreeningController extends Controller
{
    public function index()
    {
        $screeningData = collect([
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
        ]);

        $statusData = collect([
            (object)[
                'status' => 'startups',
                'count' => $screeningData->count(),
            ],
            (object)[
                'status' => 'not-started',
                'count' => $screeningData->where('status', 'not-started')->count(),
            ],
            (object)[
                'status' => 'in-progress',
                'count' => $screeningData->where('status', 'in-progress')->count(),
            ],
            (object)[
                'status' => 'completed',
                'count' => $screeningData->where('status', 'completed')->count(),
            ],
        ]);

        $statuses = ['not-started', 'in-progress', 'completed'];

        // Add your logic here
        return view('judger.screening.index', compact('screeningData', 'statusData', 'statuses'));
    }
}
