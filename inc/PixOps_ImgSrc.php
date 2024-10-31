<?php
class PixOps_ImgSrc
{
    public function __construct()
    {

    }
    public function pixops_getSrc($imgTag)
    {

        preg_match('/src=("|\')([^("|\')]+)("|\')/i', $imgTag, $match);
        $match_copy = $match;
        $src = count($match_copy) >= 3 ? $match[2] : '';

        preg_match('/data-src=("|\')([^("|\')]+)("|\')/i', $imgTag, $datasrcMatch);
        $datasrcMatch_copy = $datasrcMatch;
        $datasrc = count($datasrcMatch_copy) >= 3 ? $datasrcMatch_copy[2] : '';

        preg_match('/title=("|\')([^("|\')]+)("|\')/i', $imgTag, $titleMatch);
        $titleMatch_copy = $titleMatch;
        $title = count($titleMatch_copy) >= 3 ? $titleMatch_copy[2] : '';

        preg_match('/alt=("|\')([^("|\')]+)("|\')/i', $imgTag, $altMatch);
        $altMatch_copy = $altMatch;
        $alt = count($altMatch) >= 3 ? $altMatch[2] : '';

        preg_match('/class=("|\')([^("|\')]+)("|\')/i', $imgTag, $classMatch);
        $classMatch_copy = $classMatch;
        $class = count($classMatch_copy) >= 3 ? $classMatch_copy[2] : '';

        preg_match('/width=("|\')([^("|\')]+)("|\')/i', $imgTag, $widthMatch);
        $widthMatch_copy = $widthMatch;
        $width = count($widthMatch_copy) >= 3 ? $widthMatch_copy[2] : '';

        preg_match('/height=("|\')([^("|\')]+)("|\')/i', $imgTag, $heightMatch);
        $heightMatch_copy = $heightMatch;
        $height = count($heightMatch_copy) >= 3 ? $heightMatch_copy[2] : '';

        preg_match('/sizes=("|\')([^("|\')]+)("|\')/i', $imgTag, $sizesMatch);
        $sizesMatch_copy = $sizesMatch;
        $sizes = count($sizesMatch_copy) >= 3 ? $sizesMatch_copy[2] : '';

        preg_match('/srcset=("|\')([^("|\')]+)("|\')/i', $imgTag, $srcsetMatch);
        $srcsetMatch_copy = $srcsetMatch;
        $srcset = count($srcsetMatch_copy) >= 3 ? $srcsetMatch_copy[2] : '';

        $array = array('src' => $src, 'title' => $title, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height, 'srcset' => $srcset, "sizes" => $sizes, "datasrc" => $datasrc);

        return $array;
    }
    public function pixops_getImageHeight($src)
    {
        return "auto";
    }

    public function pixops_getImageWidth($src)
    {
        return "auto";
    }
}
