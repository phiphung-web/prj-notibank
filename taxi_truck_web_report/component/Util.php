<?php

namespace app\component;

class Util
{
    public function sendMessage($title, $con)
    {
        $content = [
            'en' => $con,
        ];

        $headings = [
            'en' => $title,
        ];
        $hashes_array = [];
        array_push($hashes_array, [
            'id' => 'like-button',
            'text' => 'Like',
            'icon' => 'http://i.imgur.com/N8SN8ZS.png',
            'url' => 'https://yoursite.com',
        ]);
        $fields = [
            'app_id' => '22679187-cf10-424d-9ad4-985178f968c0',
            'included_segments' => [
                    'Active Users',
            ],
            'data' => [
                'foo' => 'bar',
            ],
            'contents' => $content,
            'headings' => $headings,
//            'web_buttons' => $hashes_array
        ];

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic MDI3OWYzYmItMTY3ZS00OTUzLThmZWYtZTgzMDc4ZGJhYzU1',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
