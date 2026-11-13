<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: *');

$cacheFile = __DIR__ . '/events-feed.json';
$ttl = 600;

/**
 * Varsayılan yaklaşan etkinlikler (WordPress API yanıt vermezse).
 *
 * @return list<array<string, string>>
 */
function bumed_events_fallback(): array
{
    $imgV = '20261113ody';

    return [
        [
            'id' => 'odyssey-2026',
            'date' => '2026-08-19',
            'dateDay' => '19',
            'dateMonth' => 'Ağu',
            'time' => '20:00 – 21:30',
            'title' => 'Odyssey: Destandan Sinemaya Bir Yolculuk',
            'location' => 'Online Program',
            'image' => "event-up-odyssey.jpg?v={$imgV}",
            'url' => 'https://bumed.webonalti.net/etkinlikler',
        ],
        [
            'id' => 'beach-party-2026',
            'date' => '2026-08-15',
            'dateDay' => '15',
            'dateMonth' => 'Ağu',
            'time' => '11:00 – 22:00',
            'title' => 'BÜMED Beach Party 2026 İçin Geri Sayım Başladı!',
            'location' => 'Goga Beach, Kilyos',
            'image' => "event-up-beach-party.jpg?v={$imgV}",
            'url' => 'https://bumed.webonalti.net/etkinlikler',
        ],
        [
            'id' => 'finans-2026',
            'date' => '2026-06-18',
            'dateDay' => '18',
            'dateMonth' => 'Haz',
            'time' => '20:30 – 21:45',
            'title' => 'Kişisel Finans İçin Finansal Okuryazarlık',
            'location' => 'Online Program',
            'image' => "event-up-finans.jpg?v={$imgV}",
            'url' => 'https://bumed.webonalti.net/etkinlikler',
        ],
    ];
}

/**
 * WordPress / The Events Calendar REST (varsa).
 *
 * @return list<array<string, string>>|null
 */
function bumed_events_from_wordpress(): ?array
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $endpoints = [
        'https://bumed.webonalti.net/wp-json/tribe/events/v1/events?per_page=12&start_date=now',
        'https://bumed.webonalti.net/wp-json/wp/v2/tribe_events?per_page=12&orderby=date&order=asc',
    ];

    foreach ($endpoints as $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_USERAGENT => 'BUMED-demo1-events/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !is_string($raw) || $raw === '') {
            continue;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            continue;
        }

        $items = $data['events'] ?? $data;
        if (!is_array($items) || $items === []) {
            continue;
        }

        $months = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
        $out = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = (string) ($item['title'] ?? $item['name'] ?? '');
            if ($title === '') {
                continue;
            }
            $start = (string) ($item['start_date'] ?? $item['date'] ?? '');
            $ts = $start !== '' ? strtotime($start) : false;
            $day = $ts ? date('j', $ts) : '';
            $mon = $ts ? $months[(int) date('n', $ts) - 1] : '';
            $dateIso = $ts ? date('Y-m-d', $ts) : '';

            $img = '';
            if (!empty($item['image']['url'])) {
                $img = (string) $item['image']['url'];
            } elseif (!empty($item['featured_image'])) {
                $img = (string) $item['featured_image'];
            }

            $link = (string) ($item['url'] ?? $item['link'] ?? 'https://bumed.webonalti.net/etkinlikler');
            $location = 'Online Program';
            if (!empty($item['venue']['venue']) && is_string($item['venue']['venue'])) {
                $location = $item['venue']['venue'];
            }

            $time = '';
            if ($ts) {
                $endTs = !empty($item['end_date']) ? strtotime((string) $item['end_date']) : false;
                $time = date('H:i', $ts);
                if ($endTs) {
                    $time .= ' – ' . date('H:i', $endTs);
                }
            }

            $out[] = [
                'id' => (string) ($item['id'] ?? md5($title . $start)),
                'date' => $dateIso,
                'dateDay' => $day,
                'dateMonth' => $mon,
                'time' => $time !== '' ? $time : '—',
                'title' => $title,
                'location' => $location,
                'image' => $img !== '' ? $img : 'event-up-odyssey.jpg?v=20261113ody',
                'url' => $link,
            ];

            if (count($out) >= 12) {
                break;
            }
        }

        if ($out !== []) {
            return $out;
        }
    }

    return null;
}

if (
    is_file($cacheFile)
    && filesize($cacheFile) > 16
    && (time() - filemtime($cacheFile)) < $ttl
) {
    readfile($cacheFile);
    exit;
}

$events = bumed_events_from_wordpress();
$source = 'wordpress';

if ($events === null || $events === []) {
    $events = bumed_events_fallback();
    $source = 'fallback';
}

$payload = json_encode(
    ['source' => $source, 'events' => $events],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

if (is_string($payload)) {
    file_put_contents($cacheFile, $payload);
    echo $payload;
}
