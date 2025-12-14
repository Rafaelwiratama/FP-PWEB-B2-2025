<?php
header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| KONFIGURASI RSS FEED
|--------------------------------------------------------------------------
*/
$feeds = [
    'IGN'      => 'https://feeds.ign.com/ign/all',
    'GameSpot' => 'https://www.gamespot.com/feeds/news/',
    'PC Gamer' => 'https://www.pcgamer.com/rss/'
];

$news = [];
$maxItems = 10;

foreach ($feeds as $source => $url) {
    $rss = @simplexml_load_file($url);
    if (!$rss) continue;

    foreach ($rss->channel->item as $item) {

        // ambil gambar dari enclosure / media
        $image = '';
        if (isset($item->enclosure['url'])) {
            $image = (string)$item->enclosure['url'];
        }

        $news[] = [
            'title'  => (string)$item->title,
            'image'  => $image ?: 'https://via.placeholder.com/600x400?text=Game+News',
            'source' => $source,
            'url'    => (string)$item->link
        ];

        if (count($news) >= $maxItems) break;
    }

    if (count($news) >= $maxItems) break;
}

echo json_encode($news, JSON_PRETTY_PRINT);
