<?php
// Asset optimization build script
// Generates minified, bundled CSS and JS files
// Usage: php tools/build-assets.php

$root = __DIR__ . '/..';

// === CSS Minifier ===
function minify_css($css) {
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = preg_replace('/[\r\n]+/', ' ', $css);
    $css = preg_replace('/[ \t]+/', ' ', $css);
    $css = preg_replace('/\s*([{}|;:,~()>])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    return trim($css);
}

// === JS Minifier (basic) ===
function minify_js($js) {
    $js = preg_replace('!//.*!m', '', $js);
    $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
    $js = preg_replace('/\s+/', ' ', $js);
    $js = preg_replace('/\s*([\{\}\(\);,:+\-])\s*/', '$1', $js);
    $js = preg_replace('/\s*([\!\?\~\^\&\|\*\=\/\<\>\.\%])\s*/', '$1', $js);
    $js = preg_replace('/;\s*\}/', '}', $js);
    return trim($js);
}

function size($path) {
    return file_exists($path) ? filesize($path) : 0;
}

function format_size($bytes) {
    return round($bytes / 1024, 1) . ' KB';
}

$total_before = 0;
$total_after = 0;
$report = [];

// === BUILD PUBLIC CSS ===
echo "Building public CSS bundle...\n";

$public_css_files = [
    'assets/css/main.css',
    'assets/css/cards.css',
    'assets/css/header.css',
    'assets/css/home.css',
    'assets/css/explore.css',
    'assets/css/detail.css',
    'assets/css/about.css',
    'assets/css/contact.css',
    'assets/css/components.css',
    'assets/css/auth_modal.css',
    'assets/css/footer.css',
    'assets/css/rtl.css',
];

$public_css = '';
$public_css_size_before = 0;
foreach ($public_css_files as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $public_css .= file_get_contents($path) . "\n";
        $public_css_size_before += size($path);
    }
}
$minified = minify_css($public_css);
$bundle_path = $root . '/assets/css/public.min.css';
file_put_contents($bundle_path, $minified);
$public_css_size_after = size($bundle_path);
$total_before += $public_css_size_before;
$total_after += $public_css_size_after;
$report[] = ['Bundle', 'public.min.css', format_size($public_css_size_before), format_size($public_css_size_after), round((1 - $public_css_size_after/$public_css_size_before)*100) . '%'];

echo "  public.min.css: " . format_size($public_css_size_before) . " -> " . format_size($public_css_size_after) . "\n";

// === BUILD DASHBOARD CSS ===
echo "Building dashboard CSS bundle...\n";

$dashboard_css_files = [
    'assets/css/dashboard/dashboard-variables.css',
    'assets/css/dashboard/dashboard-layout.css',
    'assets/css/dashboard/dashboard-sidebar.css',
    'assets/css/dashboard/dashboard-header.css',
    'assets/css/dashboard/dashboard-buttons.css',
    'assets/css/dashboard/dashboard-utilities.css',
    'assets/css/dashboard/dashboard-alerts.css',
    'assets/css/dashboard/dashboard-overview.css',
    'assets/css/dashboard/dashboard-tables.css',
    'assets/css/dashboard/dashboard-forms.css',
    'assets/css/dashboard/dashboard-pagination.css',
    'assets/css/dashboard/dashboard-modals.css',
    'assets/css/dashboard/dashboard-notifications.css',
    'assets/css/dashboard/dashboard-editor.css',
    'assets/css/dashboard/dashboard-add-post.css',
];

$dash_css = '';
$dash_css_size_before = 0;
foreach ($dashboard_css_files as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $dash_css .= file_get_contents($path) . "\n";
        $dash_css_size_before += size($path);
    }
}
$minified = minify_css($dash_css);
$bundle_path = $root . '/assets/css/dashboard.min.css';
file_put_contents($bundle_path, $minified);
$dash_css_size_after = size($bundle_path);
$total_before += $dash_css_size_before;
$total_after += $dash_css_size_after;
$report[] = ['Bundle', 'dashboard.min.css', format_size($dash_css_size_before), format_size($dash_css_size_after), round((1 - $dash_css_size_after/$dash_css_size_before)*100) . '%'];

echo "  dashboard.min.css: " . format_size($dash_css_size_before) . " -> " . format_size($dash_css_size_after) . "\n";

// === BUILD PUBLIC JS (non-module) ===
echo "Building public JS bundle...\n";

$public_js_files = [
    'assets/js/main.js',
    'assets/js/image-fallback.js',
    'assets/js/auth_modal.js',
    'assets/js/contact.js',
];

$public_js = '';
$public_js_size_before = 0;
foreach ($public_js_files as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $public_js .= file_get_contents($path) . "\n";
        $public_js_size_before += size($path);
    }
}
$minified = minify_js($public_js);
$bundle_path = $root . '/assets/js/public.min.js';
file_put_contents($bundle_path, $minified);
$public_js_size_after = size($bundle_path);
$total_before += $public_js_size_before;
$total_after += $public_js_size_after;
$report[] = ['Bundle', 'public.min.js', format_size($public_js_size_before), format_size($public_js_size_after), round((1 - $public_js_size_after/$public_js_size_before)*100) . '%'];

echo "  public.min.js: " . format_size($public_js_size_before) . " -> " . format_size($public_js_size_after) . "\n";

// === BUILD ANIMATIONS MODULE ===
echo "Building animations ES module...\n";

$animations_js = <<<'JS'
import { animate, inView } from "motion";

(function() {
function parseCounterValue(text) {
    var raw = text.trim();
    var suffix = "";
    var multiplier = 1;
    var decimals = 0;
    if (raw.endsWith("%")) { suffix = "%"; raw = raw.slice(0, -1); }
    else if (/[Kk]$/.test(raw)) { suffix = "K"; raw = raw.slice(0, -1); multiplier = 1000; }
    else if (/[Mm]$/.test(raw)) { suffix = "M"; raw = raw.slice(0, -1); multiplier = 1000000; }
    else if (raw.endsWith("+")) { suffix = "+"; raw = raw.slice(0, -1); }
    var hasCommas = /,/.test(raw);
    raw = raw.replace(/,/g, "");
    if (raw.includes(".")) decimals = raw.split(".")[1].length;
    var num = parseFloat(raw);
    if (isNaN(num)) return null;
    return { target: num * multiplier, suffix: suffix, decimals: decimals, hasCommas: hasCommas };
}
function formatCounterValue(value, info) {
    var display;
    var effective = info.suffix === "K" ? value / 1000 : info.suffix === "M" ? value / 1000000 : value;
    if (info.decimals > 0) display = effective.toFixed(info.decimals);
    else display = Math.round(effective).toString();
    if (info.hasCommas) {
        var parts = display.split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        display = parts.join(".");
    }
    if (info.suffix) display += info.suffix;
    return display;
}
document.addEventListener("DOMContentLoaded", function() {
    var animOptions = { duration: 0.7, easing: [0.17, 0.55, 0.55, 1] };
    var scaleOptions = { duration: 0.6, easing: [0.17, 0.55, 0.55, 1] };
    document.querySelectorAll(".motion-reveal, .motion-reveal-left, .motion-reveal-right, .motion-reveal-scale").forEach(function(el) {
        var keyframes, options;
        if (el.classList.contains("motion-reveal-left")) { keyframes = { opacity: [0, 1], x: [-40, 0] }; options = animOptions; }
        else if (el.classList.contains("motion-reveal-right")) { keyframes = { opacity: [0, 1], x: [40, 0] }; options = animOptions; }
        else if (el.classList.contains("motion-reveal-scale")) { keyframes = { opacity: [0, 1], scale: [0.95, 1] }; options = scaleOptions; }
        else { keyframes = { opacity: [0, 1], y: [40, 0] }; options = animOptions; }
        inView(el, function() { return animate(el, keyframes, options); }, { amount: 0.2, once: true });
    });
    var counters = document.querySelectorAll("[data-counter]");
    if (counters.length) {
        counters.forEach(function(el) {
            var originalText = el.textContent.trim();
            var info = parseCounterValue(originalText);
            if (!info) return;
            el.textContent = "0";
            inView(el, function() {
                animate(0, info.target, { duration: 2, ease: "circOut", onUpdate: function(latest) { el.textContent = formatCounterValue(latest, info); } });
            }, { amount: 0.5, once: true });
        });
    }
});
})();
JS;

$animations_size_before = 0;
foreach (['scroll-animations.js', 'counters.js', 'detail.js'] as $f) {
    $p = $root . '/assets/js/' . $f;
    if (file_exists($p)) $animations_size_before += size($p);
}

$animations_minified = minify_js($animations_js);
$bundle_path = $root . '/assets/js/animations.min.js';
file_put_contents($bundle_path, $animations_minified);
$animations_size_after = size($bundle_path);
$total_before += $animations_size_before;
$total_after += $animations_size_after;
$report[] = ['Bundle', 'animations.min.js', format_size($animations_size_before), format_size($animations_size_after), round((1 - $animations_size_after/$animations_size_before)*100) . '%'];

echo "  animations.min.js: " . format_size($animations_size_before) . " -> " . format_size($animations_size_after) . "\n";

// === BUILD DASHBOARD JS ===
echo "Building dashboard JS bundle...\n";

$dashboard_js_files = [
    'assets/js/dashboard.js',
    'assets/js/posts-dropdown.js',
    'assets/js/dashboard-post-form.js',
    'assets/js/dashboard-editor.js',
];

$dash_js = '';
$dash_js_size_before = 0;
foreach ($dashboard_js_files as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $dash_js .= file_get_contents($path) . "\n";
        $dash_js_size_before += size($path);
    }
}
$minified = minify_js($dash_js);
$bundle_path = $root . '/assets/js/dashboard.min.js';
file_put_contents($bundle_path, $minified);
$dash_js_size_after = size($bundle_path);
$total_before += $dash_js_size_before;
$total_after += $dash_js_size_after;
$report[] = ['Bundle', 'dashboard.min.js', format_size($dash_js_size_before), format_size($dash_js_size_after), round((1 - $dash_js_size_after/$dash_js_size_before)*100) . '%'];

echo "  dashboard.min.js: " . format_size($dash_js_size_before) . " -> " . format_size($dash_js_size_after) . "\n";

echo "\n=== SUMMARY ===\n";
echo str_pad('Type', 22) . str_pad('File', 30) . str_pad('Before', 12) . str_pad('After', 12) . 'Reduction' . "\n";
echo str_repeat('-', 90) . "\n";
foreach ($report as $r) {
    echo str_pad($r[0], 22) . str_pad($r[1], 30) . str_pad($r[2], 12) . str_pad($r[3], 12) . $r[4] . "\n";
}
echo str_repeat('-', 90) . "\n";
echo str_pad('TOTAL', 22) . str_pad('', 30) . str_pad(format_size($total_before), 12) . str_pad(format_size($total_after), 12) . round((1 - $total_after/$total_before)*100) . '%' . "\n\n";

echo "Asset bundles generated successfully.\n";
echo "Run 'php tools/build-assets.php' to regenerate after modifying source files.\n";
