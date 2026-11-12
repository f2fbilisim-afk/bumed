<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=900');
header('Access-Control-Allow-Origin: *');

$cacheFile = __DIR__ . '/instagram-feed.json';
$ttl = 1800;
$username = 'bumedofficial';

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
    readfile($cacheFile);
    exit;
}

function ig_http(string $url, bool $headOnly = false): string
{
    if (!function_exists('curl_init')) {
        return '';
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => !$headOnly,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_NOBODY => $headOnly,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    if ($headOnly) {
        curl_exec($ch);
        $effective = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $effective;
    }
    $body = curl_exec($ch);
    curl_close($ch);
    return is_string($body) ? $body : '';
}

function ig_thumb(string $shortcode): string
{
    $url = ig_http("https://www.instagram.com/p/{$shortcode}/media/?size=l", true);
    if ($url !== '' && str_contains($url, 'cdninstagram')) {
        return $url;
    }
    return '';
}

function discover_shortcodes(string $username): array
{
    $codes = [];
    $queries = [
        rawurlencode("site:instagram.com/{$username}/p"),
        rawurlencode("{$username} instagram.com/p"),
    ];
    foreach ($queries as $q) {
        $html = ig_http('https://html.duckduckgo.com/html/?q=' . $q);
        if ($html === '') {
            continue;
        }
        if (preg_match_all('#instagram\.com/(?:' . preg_quote($username, '#') . '/)?p/([A-Za-z0-9_-]+)#', $html, $m)) {
            foreach ($m[1] as $code) {
                $codes[] = $code;
            }
        }
        $codes = array_values(array_unique($codes));
        if (count($codes) >= 3) {
            break;
        }
    }
    return $codes;
}

function feed_from_graph_token(string $token): ?array
{
    $me = ig_http('https://graph.instagram.com/me/media?fields=id,media_type,media_url,permalink,thumbnail_url,caption,timestamp&limit=3&access_token=' . rawurlencode($token));
    if ($me === '') {
        return null;
    }
    $json = json_decode($me, true);
    if (!is_array($json) || empty($json['data'])) {
        return null;
    }
    $posts = [];
    foreach ($json['data'] as $row) {
        $thumb = $row['thumbnail_url'] ?? $row['media_url'] ?? '';
        if ($thumb === '') {
            continue;
        }
        $posts[] = [
            'shortcode' => basename(rtrim(parse_url($row['permalink'] ?? '', PHP_URL_PATH) ?: '', '/')),
            'url' => $row['permalink'] ?? '',
            'thumbnail' => $thumb,
            'caption' => isset($row['caption']) ? mb_substr((string) $row['caption'], 0, 120) : '',
        ];
        if (count($posts) >= 3) {
            break;
        }
    }
    if (count($posts) < 1) {
        return null;
    }
    return $posts;
}

$posts = [];
$tokenFile = __DIR__ . '/.instagram-token';
if (is_readable($tokenFile)) {
    $token = trim((string) file_get_contents($tokenFile));
    if ($token !== '') {
        $graphPosts = feed_from_graph_token($token);
        if (is_array($graphPosts)) {
            $posts = $graphPosts;
        }
    }
}

if (count($posts) < 3) {
    $shortcodes = discover_shortcodes($username);
    if (count($shortcodes) < 3) {
        $shortcodes = array_merge($shortcodes, ['DHi9hmZIGMS', 'DE4k7niIanw', 'C48RfyZIjT1']);
        $shortcodes = array_values(array_unique($shortcodes));
    }
    foreach ($shortcodes as $code) {
        $thumb = ig_thumb($code);
        if ($thumb === '') {
            continue;
        }
        $posts[] = [
            'shortcode' => $code,
            'url' => "https://www.instagram.com/p/{$code}/",
            'thumbnail' => $thumb,
        ];
        if (count($posts) >= 3) {
            break;
        }
    }
}

$payload = [
    'username' => $username,
    'profileUrl' => "https://www.instagram.com/{$username}/",
    'fetchedAt' => gmdate('c'),
    'posts' => $posts,
];

$encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($encoded !== false) {
    file_put_contents($cacheFile, $encoded);
    echo $encoded;
} else {
    http_response_code(500);
    echo '{"posts":[]}';
}
