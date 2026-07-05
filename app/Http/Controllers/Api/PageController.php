<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Contact;
class PageController extends Controller
{
    public function index(Request $req)
    {
        $page = Page::where('type', $req->type)->first();
        return response()->json([
            'message' => true,
            'data' => $page,
        ], 200);
    }
    public function contact()
    {
        $text = Contact::first();
        return response()->json([
            'message' => true,
            'data' => $text,
        ], 200);
    }
}
