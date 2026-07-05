<?php

namespace App\Services;

use App\Models\Page;
use App\Models\UploadFile;
use App\Models\User;
use File;
use Illuminate\Support\Facades\Auth;

class PageService
{
    public function create($req)
    {
        $item = $req->all();
        if ($req->type == "contact") {
            $itemArray = [
                "address" => $item['address'],
                "map" => $item['map'],
                "facebook_page" => $item['facebook_page'],
                "phone" => $item['phone'],
                "email" => $item['email'],
                "facebook" => $item['facebook'],
                "instagram" => $item['instagram'],
                "google_plus" => $item['google_plus'],
                "telegram" => $item['telegram'],
            ];
        }
        $itemData = [
            "title" => json_encode($item["title"]),
            "content" => $req->type == "contact" ? json_encode($itemArray) : json_encode($req->content),
            "status" => $item["status"],
            "type"  => $req->type == 'term-condition' ? 'term_condition' : $req->type,
        ];
        return Page::create($itemData);
    }
    public function update($data, $req)
    {
        $item = $req->all();
        if ($req->type == "contact") {
            $itemArray = [
                "address" => $item['address'],
                "map" => $item['map'],
                "facebook_page" => $item['facebook_page'],
                "phone" => $item['phone'],
                "email" => $item['email'],
                "facebook" => $item['facebook'],
                "instagram" => $item['instagram'],
                "google_plus" => $item['google_plus'],
                "telegram" => $item['telegram'],
            ];
        }
        $itemData = [
            "title" => json_encode($item["title"]),
            "content" => $req->type == "contact" ? json_encode($itemArray) : json_encode($req->content),
            "status" => $item["status"],
            "type"  => $req->type == 'term-condition' ? 'term_condition' : $req->type,
        ];
        $data->update($itemData);
    }

    //termCondition
    public function createTermCondition($req)
    {
        $item = $req->all();
        $itemData = [
            "title" => json_encode($item["title"]),
            "content" => $req->content ? json_encode($req->content) : null,
            "status" => $item["status"],
            "type"  => $req->type == 'garage' ? 'term_condition_garage' : 'term_condition_member',
        ];
        return Page::create($itemData);
    }
    public function updateTermCondition($data, $req)
    {
        $item = $req->all();
        $itemData = [
            "title" => json_encode($item["title"]),
            "content" => $req->content ? json_encode($req->content) : null,
            "status" => $item["status"],
            "type"  => $req->type == 'garage' ? 'term_condition_garage' : 'term_condition_member',
        ];
        $data->update($itemData);
    }
    //endTermCondition

    //privacy
    public function createPrivacy($req)
    {
        $item = $req->all();
        $itemData = [
            "title" => json_encode($item["title"]),
            "content" => $req->content ? json_encode($req->content) : null,
            "status" => $item["status"],
            "type"  => $req->type == 'garage' ? 'privacy_garage' : 'privacy_member',
        ];
        return Page::create($itemData);
    }
    public function updatePrivacy($data, $req)
    {
        $item = $req->all();
        $itemData = [
            "title" => json_encode($item["title"]),
            "content" => $req->content ? json_encode($req->content) : null,
            "status" => $item["status"],
            "type"  => $req->type == 'garage' ? 'privacy_garage' : 'privacy_member',
        ];
        $data->update($itemData);
    }
    //endPrivacy
}
