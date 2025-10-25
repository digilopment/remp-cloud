<?php
namespace App\Core;

class HttpClient
{
    public static function post($url, array $data = [], array $headers = []): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['error' => $error];
        }

        $decoded = json_decode($response, true);
        return $decoded ?: ['error' => 'Invalid JSON: ' . $response];
    }
}
