<?php

return [
    // Server url
    "url" => "https://fcm.googleapis.com/fcm/send",

    // Authorization
    "authorization" => env('FIREBASE_KEY'),

    // Content Type
    "content_type" => "application/json",

    // Option
    "option" => [
        "content_available" => true,
        "priority" => "high",
        "badge" => 0,
        "show_in_foreground" => true,
    ],

    // Topic
    "topic" => "tcr",
];
