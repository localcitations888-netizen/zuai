<?php
// Utility to list available Gemini models for your API key
$api_key = 'AIzaSyBgQcekfLBo1HiuP4dJ-17vfpUZKN7lhZM'; 
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $api_key;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Gemini Model Debugger</h1>";
echo "HTTP Status: " . $http_code . "<br><br>";

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "<h3>Available Models:</h3><ul>";
    if (isset($data['models'])) {
        foreach ($data['models'] as $m) {
            echo "<li><b>" . $m['name'] . "</b> - " . $m['displayName'] . " (Supports: " . implode(", ", $m['supportedGenerationMethods']) . ")</li>";
        }
    } else {
        echo "No models found in the response.";
    }
    echo "</ul>";
} else {
    echo "Error fetching models: " . $response;
}
?>
