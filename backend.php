<?php
// ======================================================
// 1. CONFIGURATION (अपनी API Key यहाँ पेस्ट करें)
// ======================================================
$apiKey = "AIzaSyADjXSmUOuUNO43QU5Rca0e9eZ3rwrWiT8";

// ======================================================
// 2. SETUP & HEADERS (छेड़छाड़ न करें)
// ======================================================

// Cross-Origin Resource Sharing (CORS) - ताकि कोई एरर न आए
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: text/plain; charset=UTF-8");

// सिर्फ POST रिक्वेस्ट ही स्वीकार करें
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Error: Only POST method is allowed.";
    exit;
}

// ======================================================
// 3. INPUT HANDLING
// ======================================================

// Frontend से डेटा लेना
$docType = isset($_POST['documentType']) ? trim($_POST['documentType']) : '';
$userContext = isset($_POST['userContext']) ? trim($_POST['userContext']) : '';

// अगर खाली है तो रोक दें
if (empty($docType) || empty($userContext)) {
    echo "Error: Please provide both the Topic and Details.";
    exit;
}

// ======================================================
// 4. PROMPT ENGINEERING (AI का दिमाग)
// ======================================================

// यह AI को बताता है कि उसे कैसे व्यवहार करना है
$prompt = "
You are an expert Professional Writer and HR Specialist working for Novaplexes.
Your Task: Write a professional document based on the following details.

Details provided by user:
- Document Type: $docType
- Context/Key Points: $userContext

STRICT RULES:
1. Language: Write STRICTLY IN ENGLISH only. Do not use any other language.
2. Grammar: Must be flawless, professional, and grammatically correct.
3. Formatting: 
   - Do NOT use Markdown symbols like ** (bold) or ## (headings). 
   - Use plain text with proper spacing and indentation.
   - If it is a Resume/CV, organize it clearly with headers.
   - If it is an Email, include a Subject Line.
4. Tone: Polite, formal, and persuasive.
5. Content: Do not include conversational filler like 'Here is the draft'. Just output the document text directly.
";

// ======================================================
// 5. API CALL (GOOGLE GEMINI)
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

// cURL Setup (Shared Hosting Friendly)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL एरर रोकने के लिए

$response = curl_exec($ch);

// ======================================================
// 6. RESPONSE HANDLING
// ======================================================

if (curl_errno($ch)) {
    echo 'Connection Error: ' . curl_error($ch);
} else {
    $responseData = json_decode($response, true);
    
    // Check if valid content exists
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Final clean up (Extra safety to remove Markdown if AI ignores rules)
        $cleanText = str_replace(['**', '##', '```'], '', $generatedText);
        
        echo $cleanText;
    } else {
        // अगर API Key गलत है या कोटा खत्म हो गया है
        echo "System Error: Unable to generate text. Please check your API Key or Quota.";
    }
}

curl_close($ch);
?>