<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Position;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SelectController extends Controller
{
    public function SelectPositionSearch(Request $req)
    {
        $data = Position::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->search) {
                $q->where('title', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'desc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }

    public function SelectSectorSearch(Request $req)
    {
        $data = Sector::where('status', 1)->where(function (Builder $q) use ($req) {
            $q->when($req->position_id, function ($q) use ($req) {
                $q->where('position_id', $req->position_id);
            });
            if ($req->search) {
                $q->where('title', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'desc')->take(50)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }

    public function SelectJobSearch(Request $req)
    {
        $data = Job::where('status', 1)->where(function (Builder $q) use ($req) {
            $q->when($req->position_id, function ($q) use ($req) {
                $q->where('position_id', $req->position_id);
            });
            $q->when($req->sector_id, function ($q) use ($req) {
                $q->where('sector_id', $req->sector_id);
            });
            if ($req->search) {
                $q->where('title', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'desc')->take(50)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
}
