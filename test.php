<?php
// अपनी Key यहाँ डालें (ध्यान रहे quotes "" के अंदर हो)
$apiKey = "AIzaSyDKZ6GEnBHdg0RDFgs-PNXPxyK0ULSSReE"; // यहाँ अपनी नई वाली Key डालें

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        ["parts" => [["text" => "Say Hello"]]]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo 'Curl Error: ' . curl_error($ch);
} else {
    echo "<h1>API Test Result:</h1>";
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
}
curl_close($ch);
?>
