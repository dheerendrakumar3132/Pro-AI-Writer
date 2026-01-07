<?php
// ======================================================
// CONFIGURATION
// ======================================================
$apiKey = "AIzaSyDKZ6GEnBHdg0RDFgs-PNXPxyK0ULSSReE"; 

// ======================================================
// CORS & HEADER SETUP
// ======================================================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: text/plain; charset=UTF-8");

// Handle Pre-flight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ======================================================
// DATA HANDLING
// ======================================================

// Block direct GET access
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo "Server is running. Please use the website form to send data.";
    exit;
}

$docType = isset($_POST['documentType']) ? trim($_POST['documentType']) : '';
$userContext = isset($_POST['userContext']) ? trim($_POST['userContext']) : '';

// Create query
$fullQuery = "Topic/Type: " . $docType . ". Details: " . $userContext;

if (empty($docType) && empty($userContext)) {
    echo "Error: Empty input.";
    exit;
}

// ======================================================
// AI PROMPT INSTRUCTIONS
// ======================================================

$prompt = "
You are a highly intelligent AI Assistant for Novaplexes.
User Request: $fullQuery

INSTRUCTIONS:
1. If the user asks for a Document (Resume, Email, Application, Report):
   - Write it in a strictly PROFESSIONAL format.
   - Use proper English.
   - Do not include markdown formatting like ** or ##.

2. If the user asks a General Question (Search, Info, Coding):
   - Answer the question accurately.
   - Provide a direct answer.

3. General Rules:
   - Output ONLY the result text.
   - No conversational fillers.
";

// ======================================================
// API CALL (GOOGLE GEMINI)
// ======================================================

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

// ======================================================
// OUTPUT PROCESSING
// ======================================================

if (curl_errno($ch)) {
    echo 'Connection Error: ' . curl_error($ch);
} else {
    $responseData = json_decode($response, true);
    
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Remove markdown symbols for clean text
        $cleanText = str_replace(['**', '##', '```'], '', $text);
        
        echo $cleanText;
    } else {
        echo "API Error: Unable to generate result.";
    }
}
curl_close($ch);
?>

