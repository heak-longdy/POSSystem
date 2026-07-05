<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * @var Builder
     * @return Builder
     */
    public function betweenDate($model, $fallbackCurrentDate = false)
    {
        return $model->when(filled(request('from_date')), function ($query) {
            $query->whereDate("created_at", ">=", date("Y-m-d", strtotime(request('from_date'))));
        })
            ->when(filled(request('to_date')), function ($query) {
                $query->whereDate("created_at", "<=", date("Y-m-d", strtotime(request('to_date'))));
            })
            ->when(!filled(request('to_date')) && !filled(request('to_date')), function ($query) use ($fallbackCurrentDate) {
                $query->when($fallbackCurrentDate, function ($query) {
                    $query->whereDate("created_at", ">=", date("Y-m-d", strtotime(Carbon::now())));
                })
                    ->whereDate("created_at", "<=", date("Y-m-d", strtotime(Carbon::now())));

            });
    }
}
