<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$available_langs = ['en', 'fr', 'ar'];

$lang = $_SESSION['lang'] ?? 'en';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
    $uri = $_SERVER['REQUEST_URI'];
    $parsed = parse_url($uri);
    $redirect = $parsed['path'] ?? '/';
    if ($redirect !== '' && $redirect[0] === '/' && strpos($redirect, '://') === false && strpos($redirect, '..') === false) {
        // safe — internal path
    } else {
        $redirect = '/';
    }
    $query = [];
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $query);
    }
    unset($query['lang']);
    $qs = http_build_query($query);
    if ($qs !== '') {
        $redirect .= '?' . $qs;
    }
    header("Location: $redirect");
    exit();
}

$lang = in_array($lang, $available_langs) ? $lang : 'en';

$lang_file = __DIR__ . "/../lang/{$lang}.php";
$translations = [];
if (file_exists($lang_file)) {
    $translations = require $lang_file;
}

define('CURRENT_LANG', $lang);

function __(string $key, ...$args): string
{
    global $translations;
    $text = $translations[$key] ?? $key;
    if (!empty($args)) {
        $text = sprintf($text, ...$args);
    }
    return $text;
}

function get_lang_code(): string
{
    return CURRENT_LANG;
}

function get_lang_dir(): string
{
    global $translations;
    return $translations['dir'] ?? 'ltr';
}

function lang_url(string $code): string
{
    $query = $_GET;
    unset($query['lang']);
    $query['lang'] = $code;
    return '?' . http_build_query($query);
}
