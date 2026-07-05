<?php
namespace App\Functions;

use Illuminate\Support\Facades\Http;

class Messaging
{
    // Send by token
    public static function token($token, $title, $body, $data = null)
    {
        if (is_array(json_decode($token))) {
            foreach ($token as $item) {
                self::send($item, $title, $body, $data);
            }
            return;
        }
        return self::send($token, $title, $body, $data);
    }

    // Send by topic
    public static function topic($title, $body, $data = null)
    {
        return self::send("/topics/" . config('messaging.topic'), $title, $body, $data);
    }

    // Send Messaging
    protected static function send($to, $title, $body, $data)
    {
        Http::withHeaders([
            'Content-Type' => config('messaging.content_type'),
            'Authorization' => "key=" . config('messaging.authorization'),
        ])
            ->post(config('messaging.url'), [
                "to" => $to,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    array_column(config('messaging.option'), null),
                ],
                "data" => $data,
            ]);
    }

}
