<?php

use App\Models\CompanyInfoModel;
use App\Models\PackageAudio;
use App\Models\PackageVideo;
use App\Models\UserCourse;
use App\Models\UserPackageActive;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

function getFullName($item)
{
    return $item ? $item->first_name . ' ' . $item->last_name : '';
}

function getFullNameWithRefCode($item)
{
    return $item ? $item->first_name . ' ' . $item->last_name . ' (' . $item->referent_code . ')' : '';
}

function getNameByLocale($item)
{
    return App::getLocale() == "km" ? $item->name_kh : $item->name_en;
}

function routeActive(string $route)
{
    $arr = explode(',', $route);
    foreach ($arr as $item) {
        if (request()->is($item)) {
            return true;
        }
    }
    return false;
}

function customUrl($url, $queryParam)
{
    $pattern = "/\?/i";
    $query = "";
    $i = 0;
    $parseUrl = parse_url($url, PHP_URL_QUERY);
    parse_str($parseUrl, $params);
    foreach (collect($queryParam) as $key => $value) {
        if (!isset($params[$key])) {
            if ($i == 0) {
                $hasQuery = preg_match($pattern, $url);
                if ($hasQuery < 1) {
                    $query .= '?' . $key . '=' . $value;
                } else {
                    $query .= '&' . $key . '=' . $value;
                }
            } else {
                $query .= '&' . $key . '=' . $value;
            }
        }
        $i++;
    }
    return $url ? $url . $query : '';
}

/**
 * status check return
 *
 * @param int $val;
 * @return string;
 */
function statusCheck($val)
{
    if (!$val) return 'undefined';
    switch ($val) {
        case 1:
            return 'ស្នើរសុំ';
            break;
        case 2:
            return 'ជោគជ័យ';
            break;
        case 3:
            return 'បដិសេដ';
            break;
        case 4:
            return 'បោះបង់';
            break;
        default:
            return 'undefined';
            break;
    }
}
/**
 * status check return color
 *
 * @param int $val;
 * @return string;
 */
function statusCheckColor($val)
{
    if (!$val) return 'undefined';
    switch ($val) {
        case 1:
            return '#c59130';
            break;
        case 2:
            return '#689f39';
            break;
        case 3:
            return '#791111';
            break;
        case 4:
            return '#d52e2b';
            break;
        default:
            return 'undefined';
            break;
    }
}

function before($cost, $percent)
{
    return $cost;
}

function after($cost, $percent)
{
    $costDiscount = $percent / 100;
    return $cost - ($cost * $costDiscount);
}
function checkCourse($id)
{
    $active = UserCourse::where([['user_id', auth()->id()], ['course_id', $id]])->first();
    if ($active) return 'ទិញរួច';
    return 'ទិញវគ្គសិក្សា';
}
function checkPackage($id)
{
    $active = UserPackageActive::where([['user_id', auth()->id()], ['package_id', $id]])->first();
    if ($active) return 'ទិញរួច';
    return 'ទិញកញ្ចប់សិក្សា';
}
// id package
function videoFullOrTrial($id, $videoId, $course_id = null)
{
    $active = UserPackageActive::where([['user_id', auth()->id()], ['package_id', $id]])->first();
    $activeCourse = UserCourse::where([['user_id', auth()->id()], ['course_id', $course_id]])->first();
    $video = PackageVideo::findOrFail($videoId);
    if ($video->is_free) return $video->url;
    if ($activeCourse || auth()->id() == optional($activeCourse)->author_id) return $video->url;
    return $active ? $video->url : $video->url_trailer;
}
function videoActive($id, $videoId, $course_id = null)
{
    $active = UserPackageActive::where([['user_id', auth()->id()], ['package_id', $id]])->first();
    $video = PackageVideo::findOrFail($videoId);
    $activeCourse = UserCourse::where([['user_id', auth()->id()], ['course_id', $course_id]])->first();
    if ($video->is_free) return '';
    if ($video->url_trailer) return '';
    if ($activeCourse) return '';
    return $active ? '' : 'disable-list';
}
function audioActive($id, $audioId, $course_id = null)
{
    $active = UserPackageActive::where([['user_id', auth()->id()], ['package_id', $id]])->first();
    $video = PackageAudio::findOrFail($audioId);
    $activeCourse = UserCourse::where([['user_id', auth()->id()], ['course_id', $course_id]])->first();
    if ($video->is_free) return true;
    if ($video->url_trailer) return true;
    if ($activeCourse) return true;
    return $active ? true : false;
}
function audioFullOrTrial($id, $audioId, $course_id = null)
{
    $active = UserPackageActive::where([['user_id', auth()->id()], ['package_id', $id]])->first();
    $audio = PackageAudio::findOrFail($audioId);
    $activeCourse = UserCourse::where([['user_id', auth()->id()], ['course_id', $course_id]])->first();
    if ($audio->is_free) return $audio->path;
    if ($activeCourse || auth()->id() == optional($activeCourse)->author_id) return $audio->path;
    return  $active ? $audio->path : $audio->path_trail;
}

function vimeo_duration($id)
{
    try {
        $response = Http::get("https://vimeo.com/api/oembed.json?url=https://vimeo.com/$id");
        $data = $response->json();
        $second = $data['duration'] % 60;
        return (int) ($data['duration'] / 60) . ':' . ($second > 9 ? $second : 0 . $second);
    } catch (Exception $e) {
        return 'បង់ប្រាក់';
    }
}
function getContactInfo()
{
    $getContact = CompanyInfoModel::get()->first();

    if ($getContact) {
        $id = $getContact->id;
        $location_of_company = $getContact->location_of_company;
        $mobile_phone = $getContact->mobile_phone;
        $telephone = $getContact->telephone;
        $email = $getContact->email;
        $facebook = $getContact->facebook_id;
        $twitter = $getContact->twitter_id;
        $youtube = $getContact->youtube_id;
        $instagram = $getContact->instagram_id;
        $lat = $getContact->map_lat;
        $long = $getContact->map_lng;
        $zoomLevel = $getContact->map_level_zoom;
    } else {
        $id = null;
        $location_of_company = null;
        $mobile_phone = null;
        $telephone = null;
        $email = null;
        $facebook = null;
        $twitter = null;
        $youtube = null;
        $instagram = null;
        $lat = null;
        $long = null;
        $zoomLevel = null;
    }

    $data = [
        'id' => $id,
        'location_of_company' => $location_of_company,
        'mobile_phone' => $mobile_phone,
        'telephone' => $telephone,
        'email' => $email,
        'facebook' => $facebook,
        'twitter' => $twitter,
        'youtube' => $youtube,
        'instagram' => $instagram,
        'lat' => $lat,
        'long' => $long,
        'zoomLevel' => $zoomLevel,
    ];

    return $data;
}

function checkSenderImage($sender, $data)
{
    if ($sender) return $data->withReceiver->image_path ?? '';
    return  $data->withSender->image_path ?? '';
}
function checkSenderName($sender, $data)
{
    if ($sender) return $data->withReceiver ? $data->withReceiver->first_name . ' ' . $data->withReceiver->last_name  : 'Unknown';
    return  $data->withSender ? $data->withSender->first_name . ' ' . $data->withSender->last_name  : 'Unknown';
}
function parentName($parent)
{
    if ($parent) return $parent->first_name . ' ' . $parent->last_name;
    return 'មិនមានមេក្រុម';
}
