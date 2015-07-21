<?php
namespace Wheniwork\Feedback\Service;

class HipChatService
{
    private static function post($endpoint, $params)
    {
        $url = "https://api.hipchat.com/v2" . $endpoint;
        $postfields = json_encode($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $_ENV['HIPCHAT_KEY'],
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function postMessage($content)
    {
        self::post("/room/" . $_ENV['HIPCHAT_ROOM'] . "/notification", [
            'message' => $content,
            'color' => 'gray',
            'notify' => true
        ]);
    }
}
