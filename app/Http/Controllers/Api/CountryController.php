<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
class CountryController extends Controller
{
    public function index()
    {
        $data = Country::select('id','name','code','flag')->get();
      	if(count ($data) > 0){
          $message = true;
          $country = $data; 
        }else{
          $message = false;
          $country = [];
        }
        return response()->json([
            'message' => true,
            'data' => $country,
        ], 200);

    }
}
