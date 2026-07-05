<?php

use Carbon\Carbon;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Gate;

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

function findNumberOfMonth($StartDate="",$numberOfDays=0){
    $postDate = \Carbon\Carbon::parse($StartDate);  // Your PostDate
    // Add the number of days to the post date
    $endDate = $postDate->copy()->addDays($numberOfDays);

    // Calculate the total difference in days between PostDate and EndDate
    $daysDifference = $postDate->diffInDays($endDate);

    // Calculate the fractional months (using 30.44 days per month for average)
    $monthsDifference = $daysDifference / 30;
    return $monthsDifference;
}

function resData($data = null)
{
    return response()->json([
        'data' => $data,
        'message' => 'fetch_data_success',
        'error' => false,
    ], 200);
}

function resSuccess($message, $data = null, array $options = [])
{
    return response()->json([
        'message' => $message,
        'error' => false,
        'data' => $data,
        ...$options,
    ], 200);
}

function resFail($message, Exception $exception = null)
{
    $response = [
        'message' => $message,
        'error' => true,
    ];
    if ($exception) {
        $response['exception'] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
    }
    return response()->json($response, 202);
}

function resValidate(MessageBag $message)
{
    $response = [
        'validate' => $message,
        'error' => true,
    ];
    return response()->json($response, 422);
}
function CheckRole($field)
{
    return Gate::check($field) ? true : false;
}
function createFormat($date, $format = null)
{
    if ($date == "currentDate") {
        return Carbon::now()->format($format);
    }
    return $date->format('Y-m-d');
}