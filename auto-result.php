<?php
// Enable CORS for all origins (for development use; restrict domain in production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Target website
$url = 'https://satta-king-fast.com/';

// Load HTML
$html = @file_get_contents($url);
if (!$html) {
    echo json_encode(['error' => 'Unable to fetch source site']);
    exit;
}

// Suppress warnings and parse HTML
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
libxml_clear_errors();

// XPath setup
$xpath = new DOMXPath($dom);

// Game IDs and labels
$markets = [
    'DESAWAR' => 'DS',
    'SHRI GANESH' => 'SG',
    'FARIDABAD' => 'FB',
    'GHAZIABAD' => 'GB',
    'GALI' => 'GL'
];

$results = [];

foreach ($markets as $name => $id) {
    $yesterdayXPath = "//tr[@id='$id']/td[contains(@class, 'yesterday-number')]/h3";
    $todayXPath = "//tr[@id='$id']/td[contains(@class, 'today-number')]/h3";

    $yesterdayNode = $xpath->query($yesterdayXPath);
    $todayNode = $xpath->query($todayXPath);

    $yesterday = ($yesterdayNode->length > 0) ? trim($yesterdayNode->item(0)->nodeValue) : 'not found';
    $todayRaw = ($todayNode->length > 0) ? trim($todayNode->item(0)->nodeValue) : 'not found';
    $today = ($todayRaw === 'XX' || $todayRaw === '') ? 'not-announced' : $todayRaw;

    //Override Ghaziabad today result
    // if ($name === 'GHAZIABAD') {
    //     $today = '83';
    // }

    $results[$name] = [
        'yesterday' => $yesterday,
        'today' => $today
    ];
}

// Return JSON response
echo json_encode($results, JSON_PRETTY_PRINT);
