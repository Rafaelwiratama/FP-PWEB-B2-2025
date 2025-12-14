<?php
header('Content-Type: application/json');

$feeds = [
    'IGN'      => 'https://feeds.ign.com/ign/all',
    'GameSpot' => 'https://www.gamespot.com/feeds/news/'
];

$news = [];

$context = stream_context_create([
    "ssl" => [
        "verify_peer"      => false,
        "verify_peer_name" => false,
    ],
    "http" => [
        "timeout" => 10
    ]
]);

foreach ($feeds as $source => $url) {
    $content = @file_get_contents($url, false, $context);
    if (!$content) continue;

    $rss = @simplexml_load_string($content);
    if (!$rss || !isset($rss->channel->item)) continue;

    foreach ($rss->channel->item as $item) {

        $image = 'https://via.placeholder.com/600x400?text=Game+News';

        // ambil enclosure image jika ada
        if (isset($item->enclosure['url'])) {
            $image = (string)$item->enclosure['url'];
        }

        $news[] = [
            'title'  => (string)$item->title,
            'image'  => $image,
            'source' => $source,
            'url'    => (string)$item->link
        ];

        if (count($news) >= 10) break;
    }

    if (count($news) >= 10) break;
}

echo json_encode($news, JSON_PRETTY_PRINT);
