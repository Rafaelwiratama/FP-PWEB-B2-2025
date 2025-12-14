<?php
header('Content-Type: application/json');

/* ===============================
   KONFIGURASI NEWSAPI
================================ */
$apiKey = '957b7c20fd134c6a96b9a2fcd3c9b566'; 

$url = "https://newsapi.org/v2/top-headlines?" . http_build_query([
    'category' => 'gaming',
    'language' => 'en',
    'pageSize' => 10,
    'apiKey'   => $apiKey
]);

/* ===============================
   FETCH DATA
================================ */
$context = stream_context_create([
    "http" => [
        "timeout" => 10
    ]
]);

$response = @file_get_contents($url, false, $context);

if (!$response) {
    echo json_encode([]);
    exit;
}

$data = json_decode($response, true);
$news = [];

/* ===============================
   PARSE DATA
================================ */
if (!empty($data['articles'])) {
    foreach ($data['articles'] as $item) {

        $news[] = [
            'title'  => $item['title'] ?? 'Game News',
            'image'  => $item['urlToImage'] 
                        ?: 'https://via.placeholder.com/600x400?text=Game+News',
            'source' => $item['source']['name'] ?? 'NewsAPI',
            'url'    => $item['url'] ?? '#'
        ];
    }
}

echo json_encode($news, JSON_PRETTY_PRINT);
