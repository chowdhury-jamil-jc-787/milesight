<?php

namespace App\Http\Controllers;

use App\Models\Graphic;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class GraphicController extends Controller
{
    /**
     * GET /graphics
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $q = \App\Models\Graphic::query();

        // --- filters ---
        if ($request->filled('device_name')) {
            $q->where('device_name', 'like', '%'.$request->string('device_name').'%');
        }

        if ($request->filled('voltage')) {
            $q->where('voltage', $request->input('voltage'));
        } else {
            if ($request->filled('voltage_min')) $q->where('voltage', '>=', $request->input('voltage_min'));
            if ($request->filled('voltage_max')) $q->where('voltage', '<=', $request->input('voltage_max'));
        }

        if ($request->filled('time')) {
            $q->where('time', $request->input('time'));
        } else {
            if ($request->filled('time_from')) $q->where('time', '>=', $request->input('time_from'));
            if ($request->filled('time_to'))   $q->where('time', '<=', $request->input('time_to'));
        }

        if ($request->filled('created_from')) {
            $q->where('created_at', '>=', \Carbon\Carbon::parse($request->input('created_from')));
        }
        if ($request->filled('created_to')) {
            $createdTo = \Carbon\Carbon::parse($request->input('created_to'));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->input('created_to'))) {
                $createdTo = $createdTo->endOfDay();
            }
            $q->where('created_at', '<=', $createdTo);
        }
        // --------------

        $q->orderByDesc('id');

        $paginated = $q->paginate(10);

        // Force "result" to be a numerically indexed array ([] when empty)
        $result = $paginated->getCollection()->values()->all();

        return response()->json([
            'count' => $paginated->total(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
            'result' => $result, // <- always an array; [] when no rows
        ]);
    }


    /**
     * POST /graphics
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name'  => ['required', 'string', 'max:255'],
            'voltage'      => ['required', 'numeric'],
            'recorded_at'  => ['required', 'date'], // accepts ISO 8601 or "Y-m-d H:i:s"
        ]);

        // Normalize to "Y-m-d H:i:s" for the DB
        $validated['recorded_at'] = Carbon::parse($validated['recorded_at'])->format('Y-m-d H:i:s');

        $graphic = Graphic::create($validated);

        return response()->json($graphic, 201);
    }

    /**
     * GET /graphics/{graphic}
     */
    public function show(Graphic $graphic)
    {
        return response()->json($graphic);
    }

    /**
     * PUT/PATCH /graphics/{graphic}
     */
    public function update(Request $request, Graphic $graphic)
    {
        $validated = $request->validate([
            'device_name'  => ['sometimes', 'required', 'string', 'max:255'],
            'voltage'      => ['sometimes', 'required', 'numeric'],
            'recorded_at'  => ['sometimes', 'required', 'date'], // accepts Y-m-d H:i:s or ISO8601
        ]);

        if (isset($validated['recorded_at'])) {
            // normalize to proper DB datetime format
            $validated['recorded_at'] = Carbon::parse($validated['recorded_at'])->format('Y-m-d H:i:s');
        }

        $graphic->update($validated);

        return response()->json($graphic);
    }

    /**
     * DELETE /graphics/{graphic}
     */
    public function destroy(Graphic $graphic)
    {
        $graphic->delete();

        return response()->json([
            'message' => 'Graphic deleted successfully',
            'id'      => $graphic->id,
        ], 200);
    }


public function chart(Request $request)
{
    // 1) Validate inputs
    $validated = $request->validate([
        'device_name' => ['nullable', 'string', 'max:255'],     // single device
        'devices'     => ['nullable', 'array'],                 // multiple devices
        'devices.*'   => ['string', 'max:255'],

        'from'        => ['nullable', 'date'],                  // recorded_at start (inclusive)
        'to'          => ['nullable', 'date'],                  // recorded_at end (inclusive)

        'group_by'    => ['nullable', 'in:hour,day,week,month,year'],
        'agg'         => ['nullable', 'in:avg,min,max'],
        'bucket_fill' => ['nullable', 'boolean'],

        // optional: keep old labels-array format if needed
        'format'      => ['nullable', 'in:points,labels'],
    ]);

    // 2) Defaults
    $groupBy    = $validated['group_by']    ?? 'hour';
    $agg        = $validated['agg']         ?? 'avg';
    $bucketFill = array_key_exists('bucket_fill', $validated) ? (bool)$validated['bucket_fill'] : true;
    $format     = $validated['format']      ?? 'points'; // default to points (time+voltage inside data)

    // 3) Date range defaults: if not provided, use **today** (app timezone)
    if (!isset($validated['from']) || !isset($validated['to'])) {
        $from = now()->startOfDay();
        $to   = now()->endOfDay();
    } else {
        $from = Carbon::parse($validated['from']);
        $to   = Carbon::parse($validated['to']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$request->input('to'))) {
            $to = $to->endOfDay(); // include full day if date-only
        }
    }

    // 4) Base query
    $base = Graphic::query()->whereBetween('recorded_at', [$from, $to]);

    // device filters
    $singleDevice = null;
    if (!empty($validated['device_name'])) {
        $singleDevice = $validated['device_name'];
        $base->where('device_name', $singleDevice);
    } elseif (!empty($validated['devices'])) {
        $base->whereIn('device_name', $validated['devices']);
    }

    // 5) Bucket SQL + agg
    $aggFn = match ($agg) {
        'min' => 'MIN',
        'max' => 'MAX',
        default => 'AVG',
    };

    // MySQL/MariaDB expressions
    $bucketSelect = match ($groupBy) {
        'hour'  => "DATE_FORMAT(recorded_at, '%Y-%m-%d %H:00:00')",
        'day'   => "DATE_FORMAT(recorded_at, '%Y-%m-%d')",
        'week'  => "DATE_FORMAT(DATE_SUB(recorded_at, INTERVAL WEEKDAY(recorded_at) DAY), '%Y-%m-%d')", // Monday as week start
        'month' => "DATE_FORMAT(recorded_at, '%Y-%m')",
        'year'  => "DATE_FORMAT(recorded_at, '%Y')",
        default => "DATE_FORMAT(recorded_at, '%Y-%m-%d %H:00:00')",
    };

    // 6) Fetch grouped rows
    if ($singleDevice) {
        $rows = $base
            ->selectRaw("$bucketSelect as bucket, {$aggFn}(voltage) as value")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();
    } else {
        $rows = $base
            ->selectRaw("$bucketSelect as bucket, device_name, {$aggFn}(voltage) as value")
            ->groupBy('bucket', 'device_name')
            ->orderBy('bucket')
            ->get();
    }

    // 7) Build labels (internal if format=points)
    $labels = [];
    if ($bucketFill) {
        // normalize boundaries to bucket starts
        switch ($groupBy) {
            case 'hour':
                $start = $from->copy()->startOfHour();
                $end   = $to->copy()->startOfHour();
                $step  = '1 hour';
                $fmt   = 'Y-m-d H:00:00';
                break;
            case 'day':
                $start = $from->copy()->startOfDay();
                $end   = $to->copy()->startOfDay();
                $step  = '1 day';
                $fmt   = 'Y-m-d';
                break;
            case 'week':
                $start = $from->copy()->startOfWeek(Carbon::MONDAY);
                $end   = $to->copy()->startOfWeek(Carbon::MONDAY);
                $step  = '1 week';
                $fmt   = 'Y-m-d'; // week label is the Monday date
                break;
            case 'month':
                $start = $from->copy()->startOfMonth();
                $end   = $to->copy()->startOfMonth();
                $step  = '1 month';
                $fmt   = 'Y-m';
                break;
            case 'year':
                $start = $from->copy()->startOfYear();
                $end   = $to->copy()->startOfYear();
                $step  = '1 year';
                $fmt   = 'Y';
                break;
            default:
                $start = $from->copy()->startOfHour();
                $end   = $to->copy()->startOfHour();
                $step  = '1 hour';
                $fmt   = 'Y-m-d H:00:00';
        }
        $period = CarbonPeriod::create($start, $step, $end);
        foreach ($period as $dt) {
            $labels[] = $dt->format($fmt);
        }
    } else {
        $labels = $rows->pluck('bucket')->unique()->values()->all();
    }

    // helper: build points [{time, voltage}] aligned to labels
    $makePoints = function(array $labels, $map) {
        $points = [];
        foreach ($labels as $b) {
            $val = optional($map->get($b))->value;
            $points[] = ['time' => $b, 'voltage' => $val !== null ? (float)$val : null];
        }
        return $points;
    };

    // 8) Build series
    $series = [];
    if ($singleDevice) {
        $map = $rows->keyBy('bucket');
        $data = $format === 'points'
            ? $makePoints($labels, $map)
            : array_map(fn($b) => optional($map->get($b))->value, $labels);

        $series[] = [
            'name'   => "{$aggFn} Voltage",
            'device' => $singleDevice,
            'data'   => $data,
        ];
    } else {
        $devicesFound = $rows->pluck('device_name')->unique()->values();
        foreach ($devicesFound as $dev) {
            $map = $rows->where('device_name', $dev)->keyBy('bucket');
            $data = $format === 'points'
                ? $makePoints($labels, $map)
                : array_map(fn($b) => optional($map->get($b))->value, $labels);

            $series[] = ['name' => $dev, 'data' => $data];
        }
        if ($devicesFound->isEmpty() && !empty($validated['devices'])) {
            // emit empty series for requested devices with no data
            foreach ($validated['devices'] as $dev) {
                $series[] = [
                    'name' => $dev,
                    'data' => $format === 'points'
                        ? array_map(fn($b) => ['time' => $b, 'voltage' => null], $labels)
                        : array_fill(0, count($labels), null),
                ];
            }
        }
    }

    // 9) Response
    $response = [
        'query' => [
            'device_name' => $singleDevice,
            'devices'     => $validated['devices'] ?? null,
            'from'        => $from->toDateTimeString(),
            'to'          => $to->toDateTimeString(),
            'group_by'    => $groupBy,
            'agg'         => strtolower($aggFn),
            'bucket_fill' => $bucketFill,
            'format'      => $format,
        ],
        'series' => $series,
        'result' => collect($series)->sum(function ($s) {
            // count non-null points
            $vals = array_map(
                fn($p) => is_array($p) ? ($p['voltage'] ?? null) : $p,
                $s['data']
            );
            return count(array_filter($vals, fn($v) => $v !== null));
        }),
    ];

    // Only include labels if explicitly asked for labels format
    if ($format === 'labels') {
        $response['labels'] = $labels;
    }

    return response()->json($response);
}




}
