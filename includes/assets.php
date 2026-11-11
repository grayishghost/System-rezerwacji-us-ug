<?php


function gah_asset_file(string $relative): ?string
{
    if (str_contains($relative, "\0") || str_contains($relative, '\\')
        || !preg_match('~^(assets|css|js)/~D', $relative)) {
        return null;
    }
    foreach (explode('/', $relative) as $part) {
        if ($part === '..' || $part === '.') { return null; }
    }
    $root = dirname(__DIR__);
    $file = realpath($root . '/' . $relative);
    return $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file) ? $file : null;
}
function gah_asset_url(string $relative): string
{
    $file = gah_asset_file($relative);
    $version = $file ? substr(hash_file('sha256', $file), 0, 12) : 'missing';
    if (!$file) { error_log('Brak zasobu strony: ' . $relative); }
    $path = implode('/', array_map('rawurlencode', explode('/', $relative)));
    return rtrim((string)BASE_URL, '/') . '/' . $path . '?v=' . $version;
}
function gah_room_image(array $room): string
{
    $candidate = $room['Zdjecie_Pokoju'] ?? '';
    if (is_string($candidate) && $candidate !== '') {
        if (!str_starts_with($candidate, 'assets/')) { $candidate = 'assets/' . $candidate; }
        if (preg_match('~\.(jpe?g|png|webp)$~iD', $candidate) && gah_asset_file($candidate)) {
            return $candidate;
        }
    }
    
    $fallbacks = ['assets/scroll (1).jpg', 'assets/scroll (2).jpg', 'assets/scroll (3).png'];
    return $fallbacks[((int)$room['ID_Pokoju']) % count($fallbacks)];
}
