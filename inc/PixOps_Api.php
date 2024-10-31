<?php
class PixOps_Api
{

    private $apiUrl;
    private $apiBlurURl;
    private $lazyLoad;
    private $quality;

    public function __construct()
    {

        $this->lazyLoad = get_option('pixop_lazy_load');
        $this->quality = get_option('pixop_quality','auto');
        $url = 'https://cdn.pixops.io/v1/';
        $this->apiUrl = $url;

        if ($this->lazyLoad == 'true') {
            $this->apiBlurURl = 'https://cdn.pixops.io/v1/w:auto/h:auto/q:eco/';
        } else {
            $this->apiBlurURl = $url;
        }
    }
	
    public function pixops_getImageTag($src = '', $title = '', $alt = '', $class = 'wp-pixops', $width = 'auto', $height = 'auto',$srcset='',$sizes='')
    {
		
        $img = '<img ';
        $img .= 'data-pixops-src="' . $this->apiUrl . 'w:' . $width . '/h:' . $height . '/q:' . $this->quality . '/' . $src . '" ';
        if ($this->lazyLoad == 'true') {
            $img .= 'src="' . $this->apiBlurURl . $src . '" ';
        } else {
            $img .= 'src="' . $this->apiBlurURl . 'w:' . $width . '/h:' . $height . '/q:' . $this->quality . '/' . $src . '" ';
        }
        $img .= $class == ''?'':'class="' . $class . '" ';
        $img .= $title == ''?'':'title="' . $title . '" ';
        $img .= $alt == ''?'':'alt="' . $alt . '" ';
        $img .= 'width="' . $width . '" ';
		        $img .= 'height="' . $height . '" ';
        $img .=$srcset==''?'': 'srcset="' . $srcset . '" ';
		$img .= $sizes==''?'':'sizes="' . $sizes . '" ';

		$img .= ' >';
        return $img;
    }
    public function pixops_getImageUrl($src,$srcset, $width = 'auto', $height = 'auto')
    {
		
        $array = array(); 
	
        if ($this->lazyLoad == 'true') {
            $array = array(
                'pixopsSrc' => $this->apiUrl . 'w:' . $width . '/h:' . $height . '/q:' . $this->quality . '/' . $src,
                'src' => $this->apiBlurURl . $src,
				'srcset'=>$srcset
            );
        } else {
            $array = array(
                'pixopsSrc' => $this->apiUrl . 'w:' . $width . '/h:' . $height . '/q:' . $this->quality . '/' . $src,
                'src' => $this->apiBlurURl . 'w:' . $width . '/h:' . $height . '/q:' . $this->quality . '/' . $src,
				'srcset'=>$srcset
            );
        }
        return $array;

    }

}
