<?php
include plugin_dir_path(__FILE__) . 'PixOps_ImgSrc.php';
include plugin_dir_path(__FILE__) . 'PixOps_Api.php';
class PixOps_Content
{
	public static $extensions = array(
            'jpg','jpeg','jpe',
            'png',
            'webp',
            'svg'
        );
    public function __construct()
    {
		
    }

    public static function pixops_images($content)
    {

        $images = array();
        $site_url = site_url();
        $site_u = parse_url($site_url);
        $regex = '/<img(((?!\<img).)+?)>/im';
        preg_match_all($regex, $content, $images, PREG_OFFSET_CAPTURE);


        if (preg_match_all($regex, $content, $images, PREG_OFFSET_CAPTURE)) {

            foreach ($images as $key => $unused) {
		
                // Simplify the output as much as possible, mostly for confirming test results.
                if (is_numeric($key) && $key > 0) {
                    unset($images[$key]);
                    continue;
                }

                foreach ($unused as $url_key => $url_value) {

                    $imgTag = $url_value[0];

                    preg_match('/src=("|\')([^("|\')]+)("|\')/i', $imgTag, $match);

                if ((strlen($match[2]) < 0 || strpos($match[2], $site_u['host']) <= 0 )|| strpos($match[2], "cdn.pixops.io")>0) {
						
                        unset($images[$key][$url_key]);
                        continue;
                    };
                    if ($key === 'img_url') {
                        $images[$key][$url_key] = rtrim($imgTag, '\\');
                        continue;
                    }
					
                    $images[$key][$url_key] = $imgTag;
                }
            }
           
            return self::pixops_process_image_tags($content, $images);
        }
		

        return $content;
    }

    public static function pixops_process_image_tags($content, $images = array())
    {
        $pixopsApi = new PixOps_Api;
        foreach ($images[0] as $index => $tag) {
            $pixopsImgSrc = new PixOps_ImgSrc;
			
            $arrayReturn = $pixopsImgSrc->pixops_getSrc($tag);
		
		    if ($arrayReturn['width'] != '') {
                $width = $arrayReturn['width'];
            } else {
                $width = $pixopsImgSrc->pixops_getImageWidth($arrayReturn['src']);
            }
            if ($arrayReturn['height'] != '') {
                $height = $arrayReturn['height'];
            } else {
                $height = $pixopsImgSrc->pixops_getImageHeight($arrayReturn['src']);
           }

            if (strpos($arrayReturn['class'], 'rev-slidebg') === false && in_array(pathinfo($arrayReturn['src'], PATHINFO_EXTENSION),PixOps_Content::$extensions)) {

                $imageTag = $pixopsApi->pixops_getImageTag($arrayReturn['src'], $arrayReturn['title'], $arrayReturn['alt'], $arrayReturn['class'], $width, $height,$arrayReturn['srcset'],$arrayReturn['sizes']);
            } else {
                $imageTag = $tag;
            }
            $content = str_replace($tag, $imageTag, $content);
        }

        return $content;
    }
}
