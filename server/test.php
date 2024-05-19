<?php
$apiKey = 'Add_your_VirusTotal_API_key_here';
$url = 'https://ljy.vsb.mybluehost.me/SBB/SBB/index/';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/vtapi/v2/url/scan');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'apikey' => $apiKey,
    'url' => $url
));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
