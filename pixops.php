<?php
/**
 * Plugin Name:       Pixops Image Optimizer
 * Description:       Image optimizer with CDN service from 200 locations.
 * Version:           1.14
 * Author:            Pixops - The Best Image Optimization Plugin
 * Author URI:        https://pixops.io
 * WordPress Available:  yes
 */
if (!defined('ABSPATH')) {
    die;
}

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

if (file_exists(dirname(__FILE__) . '/inc/Admin/PixOps_Admin.php')) {
    require_once dirname(__FILE__) . '/inc/Admin/PixOps_Admin.php';
}

use Inc\Base\PixOps_Activate;
use Inc\Base\PixOps_Deactivate;

if (file_exists(dirname(__FILE__) . '/inc/PixOps_Image.php')) {
    require_once dirname(__FILE__) . '/inc/PixOps_Image.php';
}

if (!class_exists('PixOps')) {
    class PixOps
    {
        public static $extensions_list = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        );
        public function __construct()
        {
        }

        public function pixops_activate()
        {
            PixOps_Activate::pixops_activate();

        }

        public function pixops_deactivate()
        {
            PixOps_Deactivate::pixops_deactivate();
        }

        public function pixops_remove_size_from_url($url)
        {
            $upload_data = wp_upload_dir();
            $this->upload_resource = array(
                'url' => str_replace(array('https://', 'http://'), '', $upload_data['baseurl']),
                'directory' => $upload_data['basedir'],
            );
            $this->upload_resource['url_length'] = strlen($this->upload_resource['url']);

            $content_parts = parse_url(content_url());

            $this->upload_resource['content_path'] = $content_parts['path'];
            $this->upload_resource['content_folder'] = ltrim($content_parts['path'], '/');
            $this->upload_resource['content_folder_length'] = strlen($this->upload_resource['content_folder']);
            $this->upload_resource['content_host'] = $content_parts['scheme'] . '://' . $content_parts['host'];

            if (preg_match('#(-\d+x\d+(?:_c)?|(@2x))\.(' . implode('|', array_keys(PixOps::$extensions_list)) . '){1}$#i', $url, $src_parts)) {
                $stripped_url = str_replace($src_parts[1], '', $url);
                // Extracts the file path to the image minus the base url
                $file_path = substr($stripped_url, strpos($stripped_url, $this->upload_resource['url']) + $this->upload_resource['url_length']);
                if (file_exists($this->upload_resource['directory'] . $file_path)) {
                    $url = $stripped_url;
                }
            }

            return $url;
        }

        protected function pixops_get_dimensions_from_url($src)
        {
            $width_height_string = array();
            $extensions = array_keys(PixOps::$extensions_list
            );
            if (preg_match('#-(\d+)x(\d+)(:?_c)?\.(?:' . implode('|', $extensions) . '){1}$#i', $src, $width_height_string)) {
                $width = (int) $width_height_string[1];
                $height = (int) $width_height_string[2];
                $crop = (isset($width_height_string[3]) && $width_height_string[3] === '_c');
                if ($width && $height) {
                    return array($width, $height, $crop);
                }
            }

            return array(false, false, false);
        }
        public function pixops_filter_srcset($sources = array(), $size_array = array(), $image_src = '', $image_meta = array(), $attachment_id = 0)
        {
            if (!is_array($sources)) {
                return $sources;
            }
            $original_url = null;
            $cropping = null;
            foreach ($sources as $i => $source) {
                $url = $source['url'];
                list($width, $height, $file_crop) = $this->pixops_get_dimensions_from_url($url);

                if (empty($width)) {
                    $width = $image_meta['width'];
                }

                if (empty($height)) {
                    $height = $image_meta['height'];
                }

                if ($original_url === null) {
                    if (!empty($attachment_id)) {
                        $original_url = wp_get_attachment_url($attachment_id);
                    } else {
                        $original_url = $this->pixops_remove_size_from_url($source['url']);
                    }
                }
                $args = array();
                if ('w' === $source['descriptor']) {
                    if ($height && ($source['value'] == $width)) {
                        $args['width'] = $width;
                        $args['height'] = $height;
                    } else {
                        $args['width'] = $source['value'];
                    }
                }

                $sources[$i]['url'] = PixOps_Feature::pixops_returnImageUrl($original_url, $sources)['pixopsSrc'];

            }
            return $sources;
        }

        public function pixops_body_classes($classes)
        {
            $classes[] = 'pixops_has_js';
            return $classes;
        }

        public function pixops_content($content)
        { //Wordpress post content

            $return = PixOps_Image::pixops_content($content);

            return $return;
        }
        public function pixops_process_template_redirect_content()
        {remove_filter('the_content', array($this, 'pixops_content'), PHP_INT_MAX);

            ob_start(
                array(&$this, 'pixops_replace_content')
            );
        }

        public function pixops_the_post_thumbnail()
        { // post feature image
            $imageUrl = get_the_post_thumbnail_url();
            $return = PixOps_Image::pixops_post_thumbnail($imageUrl);
            return $return;
        }

        public function pixops_attachment_image($attr)
        {

            $return = PixOps_Image::pixops_post_attachment_tag($attr['src'], $attr['srcset']);
            $attr['data-pixops-src'] = $return['pixopsSrc'];
            $attr['src'] = $return['src'];
            $attr['srcset'] = $return['srcset'];
            return $attr;
        }
        public function pixops_replace_content($html)
        {
            if (defined('REST_REQUEST') && REST_REQUEST && is_user_logged_in()) {
                return $html;
            }
            $html = PixOps_Image::pixops_content($html);
            return $html;
        }
    } // Class End
} // If Class exists End
function is_ajax_request()
{
    if (apply_filters('pixops_force_replacement_on', false) === true) {

        return true;
    }
    if (!function_exists('is_user_logged_in')) {
        return false;
    }
    // Disable for logged in users to avoid unexpected results.
    if (is_user_logged_in()) {
        return false;
    }

    if (!function_exists('wp_doing_ajax')) {
        return false;
    }
    if (!wp_doing_ajax()) {
        return false;
    }
    if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'wpmdb') !== false) {
        return false;
    }

    return true;
}
$pixops = new PixOps();
$status = get_option('pixop_api');

    $check = json_decode($status);

        //Class Object
        //add_filter('post_thumbnail_html', array($pixops, 'pixops_the_post_thumbnail'));
        add_filter('wp_calculate_image_srcset', array($pixops, 'pixops_filter_srcset'), PHP_INT_MAX, 5);

        add_filter('the_content', array($pixops, 'pixops_content'), PHP_INT_MAX);

        // add_filter( 'wp_get_attachment_image_attributes', array( $pixops, 'pixops_attachment_image' ));
        add_action(is_ajax_request() ? 'init' : 'template_redirect', array($pixops, 'pixops_process_template_redirect_content'));

        add_action('rest_api_init', array($pixops, 'pixops_process_template_redirect_content'), -21);

        add_filter('widget_custom_html_content', array($pixops, 'content'));

        add_filter('body_class', array($pixops, 'pixops_body_classes'));

        function pixops_hook_javascript()
        {
            ?>
        <script>

					document.documentElement.className += "pixops_has_js";
					(function(w, d){
						var b = d.getElementsByTagName("head")[0];
						var s = d.createElement("script");
						s.async = true;
						s.src = "https://pixops.io/apijs/code.min.js";
						b.appendChild(s);
					}(window, document));
		</script>
    <?php
}

        $lazyLoad = get_option('pixop_lazy_load');
        if ($lazyLoad == 'true') {
            add_action('wp_head', 'pixops_hook_javascript');
        }

// activation
register_activation_hook(__FILE__, array($pixops, 'pixops_activate'));

// deactivation
register_deactivation_hook(__FILE__, array($pixops, 'pixops_deactivate'));
