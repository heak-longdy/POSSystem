<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Slide;
use Illuminate\Support\Facades\Auth;

class SlideService
{
    public function create($request)
    {
        $item = $request->all();
       // $item["name"] = $request->name;
        Slide::create($item);
    }
    public function update($data, $request)
    {
        $item = $request->all();
        //$item["name"] = json_encode($request->name);
        $data->update($item);
    }
    public function sumLevel()
    {
        $item = Slide::orderBy('ordering', 'desc')->first();
        return isset($item->ordering) ? (int)$item->ordering + 1 : 1;
    }
}
