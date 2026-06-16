<?php

function optimize_uploaded_image($source_path, $max_width = 1200, $quality = 80) {
    if (!file_exists($source_path)) {
        return false;
    }

    list($width, $height, $type) = @getimagesize($source_path);
    if (!$width || !$height) {
        return false;
    }

    // resize if wider than max
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = (int)($height * $max_width / $width);
    } else {
        $new_width = $width;
        $new_height = $height;
    }

    // create source image based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src_img = @imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $src_img = @imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_WEBP:
            $src_img = @imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }

    if (!$src_img) {
        return false;
    }

    // create resampled image
    $dst_img = imagecreatetruecolor($new_width, $new_height);
    imagefill($dst_img, 0, 0, imagecolorallocate($dst_img, 255, 255, 255));
    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // save as JPEG (or WebP if supported)
    if (function_exists('imagewebp')) {
        $webp_path = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.webp', $source_path);
        imagewebp($dst_img, $webp_path, $quality);
    }

    // overwrite original
    imagejpeg($dst_img, $source_path, $quality);

    imagedestroy($src_img);
    imagedestroy($dst_img);

    return [
        'original' => $source_path,
        'webp' => isset($webp_path) ? $webp_path : null,
        'width' => $new_width,
        'height' => $new_height,
        'size_before' => $width . 'x' . $height,
        'size_after' => $new_width . 'x' . $new_height
    ];
}
