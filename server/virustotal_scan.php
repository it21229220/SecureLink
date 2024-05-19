<?php

$apiKey = 'Add_your_VirusTotal_API_key_here';


/**
 * Scan a URL using VirusTotal
 * 
 * @param string $url The URL to be scanned
 * @return bool True if the URL is malicious, false otherwise
 */
function scanUrlWithVirusTotal($url) {
    global $apiKey; // Use the global API key
    $apiUrl = 'https://www.virustotal.com/vtapi/v2/url/report';
    $params = array(
        'apikey' => $apiKey,
        'resource' => $url,
    );

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute cURL session
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the response
    $result = json_decode($response, true);

    // Check the scan results
    if (isset($result['positives']) && $result['positives'] > 0) {
        
        return 1; // URL is malicious
    }
    
    return 0; // URL is not malicious
}

//detect phishing urls from ML
function isPhishingUrl($url) {
    $command = escapeshellcmd("python3 ../phishing_detect.py " . escapeshellarg($url));
    $output = shell_exec($command);
    $result = json_decode($output, true);
    if ($result['is_phishing']); {
        return 1;
    }
    return 0;
}

?>