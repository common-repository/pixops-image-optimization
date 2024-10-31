<?php
class PixOps_Feature
{
    public function __construct()
    {

    }

    public static function pixops_process_image_feature($src)
    {

        $imageDim = new PixOps_ImgSrc();
        $height = $imageDim->pixops_getImageHeight($src);
        $width = $imageDim->pixops_getImageWidth($src);

        $pixopsApi = new PixOps_Api;
        $imageTag = $pixopsApi->pixops_getImageTag($src, '', '', '', $width, $height);
        return $imageTag;
    }

    public static function pixops_returnImageUrl($src,$srcset)
    {
        $imageDim = new PixOps_ImgSrc();
        $height = $imageDim->pixops_getImageHeight($src);
        $width = $imageDim->pixops_getImageWidth($src);

        $pixopsApi = new PixOps_Api;
       $imageUrl = $pixopsApi->pixops_getImageUrl($src,$srcset, $width, $height);
         return $imageUrl;
    }
}
