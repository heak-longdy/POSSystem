<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\BaseWebSiteController;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\Position;
use App\Models\Sector;

class JobController extends BaseWebSiteController
{
    protected $layout = 'website::pages.';
    public function job(Request $request)
    {
        $data['position'] = $request->position_id ? Position::find($request->position_id) : "";
        $data['sector'] = $request->sector_id ? Sector::find($request->sector_id) : "";
        $data['job'] = $request->job_id ? Job::find($request->job_id) : "";

        $data['data'] = Job::where(function ($query) use ($request) {
            // Apply filter for position_id if present
            $query->when($request->position_id, function ($query, $positionId) {
                $query->where('position_id', $positionId);
            });

            // Apply filter for sector_id if present
            $query->when($request->sector_id, function ($query, $sectorId) {
                $query->where('sector_id', $sectorId);
            });

            // Apply filter for job_id if present
            $query->when($request->job_id, function ($query, $job_id) {
                $query->where('id', $job_id);
            });
        })
            ->orderBy('id', 'desc')  // Order results by 'id' in descending order
            ->paginate(15);          // Paginate with 15 items per page

        return view($this->layout . 'job', $data);
    }

    public function jobDetail($id = "")
    {
        $data['data'] = Job::find($id);
        $data['blogRelates'] = Job::limit(3)->orderBy('id', 'desc')->get();
        return view($this->layout . 'jobDetail', $data);
    }
}
