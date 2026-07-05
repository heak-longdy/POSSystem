<?php

namespace App\Services;

use App\Models\CarAccessories;
use App\Models\CategoryBlog;
use App\Models\MemberCar;
use App\Models\User;

class Select2Service
{

    public function select2Garage($search = null)
    {
        return User::where('type', 'garage')->where('status', 1)->where(function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            }
        })->take(12)->get();
    }
    public function select2Member($search = null)
    {
        return User::where('type', 'member')->where('status', 1)->where(function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            }
        })->take(12)->get();
    }
    public function select2CategoryBlog($search = null)
    {
        return CategoryBlog::where('status', 1)->where(function ($q) use ($search) {
            if ($search) {
                $q->where('name->en', 'like', '%' . $search . '%');
            }
        })->take(12)->get();
    }
    public function memberCar($req)
    {
        $data = MemberCar::where('member_id', $req->member_id)->where(function ($q) use ($req) {
            if ($req->search) {
                $q->where('brand', 'LIKE', '%' . $req->search . '%');
            }
        })->take(12)->get();
        return $data;
    }
    public function typeAccessories($req)
    {
        return CarAccessories::whereNull('parent_id')->where('status', 1)->where(function ($q) use ($req) {
            if ($req->search) {
                $q->where('name->en', 'like', '%' . $req->search . '%');
            }
        })->take(12)->orderBy('ordering', 'asc')->get();
    }
}
