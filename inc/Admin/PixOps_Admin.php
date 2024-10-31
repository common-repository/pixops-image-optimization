<?php

class PixOps_Admin
{

    public function __construct()
    {

    }
    public function pixops_enqueue_scripts()
    {
        wp_register_script(
            'ajaxHandle',
            plugins_url('assets/js/admin.js', __FILE__),
            array(),
            false,
            true
        );
        wp_enqueue_script('ajaxHandle');
        wp_localize_script(
            'ajaxHandle',
            'ajax_object',
            array('ajaxurl' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('_nonce'))
        );
    }

    public function pixops_handle_ajax_request()
    {
        unset($_POST['action']);
        if (
            isset($_POST['_ajax_nonce']) && wp_verify_nonce($_POST['_ajax_nonce'], '_nonce'))
       {
        $url = 'https://api.pixops.io/apicrud';
        $headers = array(
            'content-type' => 'application/json',
        );

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => wp_json_encode($_POST),
        ));
        $api_data = wp_remote_retrieve_body($response);
		 $data = json_decode($api_data);
        echo wp_json_encode($data->result);

        $status = $data->result;

        // Posting response in meta table code

        if ($status == 'Success') {
            $test = update_option('pixop_api', $api_data);
        }

        //End Posting response in meta table code
        exit;
    }
}
    // Delete the api
    public function clear_log_ajax()
    {
       if (isset($_POST['_ajax_nonce']) && wp_verify_nonce($_POST['_ajax_nonce'], '_nonce')
        ) {
        delete_option('pixop_api');
        exit(); // make sure to put a die() or exit() at the end of your ajax
    }
}
// Pixops setting option
    // function pix_setting_action{
    // }
    public function pixops_add_menu()
    {
        $page_title = 'Pixops';
        $menu_title = 'Pixops';
        $capability = 'manage_options';
        $menu_slug = 'pixops';
        $function = array($this, 'pixops');
        $icon_url = 'dashicons-format-image';
        $position = 10;
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

    public function pixops_admin_theme_style($hook)
    {
        if ($hook != 'toplevel_page_pixops') {
            return;
        }
        wp_enqueue_style('pixops-admin-theme', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css');
        wp_enqueue_script('pixops-admin-icon', plugin_dir_url(__FILE__) . 'assets/js/fontawesome.js');
        wp_enqueue_script('pixops-admin-boot1',plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js');
        wp_enqueue_script('pixops-admin-boot3', plugin_dir_url(__FILE__) . 'assets/js/popper.min.js');
        wp_enqueue_script('pixops-admin-theme', plugin_dir_url(__FILE__) . 'assets/js/admin.js');
        wp_enqueue_style('pixops-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
        wp_enqueue_script('pixops-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js');
    }
	
	public function pixops_admin_notice_not_registered(){
		global $pagenow;
		if ( $pagenow == 'index.php' || $pagenow == 'plugins.php' ) {
			 echo '<div class="notice notice-warning is-dismissible">
				 <p>Pixops runs by default when you activate the plugin without registration you get quota of 2K hits To register <a href="https://pixops.io/app">PixOps</a>.</p>
			 </div>';
		}
	}
    public function pixops()
    {

        ?>
<div class="container" id="Pixops">
    <div class="card">
            <div class="row">
                <div class="col-md-2 mt-4">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/Pixops_Logo.png' ?>" style="width: 100%;">
                </div>
                <div class="col-md-7 mt-4">
                    <h3 class="text-secondary">Image optimization service</h3>
                </div>
                <div class="col-md-3 mt-4">
                    <h5 class="text-secondary"><span class="status bg-dark">Status</span>
                      <?php
$pixop_api = get_option('pixop_api');
        $pix = json_decode($pixop_api);
        if ($pix == null) {
            ?>
                        <span class="status bg-danger">Not Connected</span>
                    <?php } else {?>
                       <span class="status bg-success">Connected</span>
                    <?php }?>
                </div>
            </div>
            <hr>
           <?php $site_url = site_url();
        $site_u = parse_url($site_url);?>

            <form id="api-form" action="" method="post">
                <input type="hidden" name="domain_name" class="form-control" id="domain" value="<?php echo $site_u['host']; ?>">

                <?php

        if ($pix == null) {?>
                  <div class="input-group mt-4 pt-5">
                       <input type="text" class="form-control" placeholder="Enter Your API Key" name="pixops_api_key" id="api-key">
                  </div>
                  <div class="form-group mt-2">
                          <input type="text" name="user_email" class="form-control" id="user_email" placeholder="Enter email">
                          <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                  </div>
                   <center><button class="btn btn-success" name="api-connect" id="api-submit" type="button"><span class="icon p-2"><i class="fa fa-key" style="
                         font-size: 20px;"></i></span><span>Connect to Pixops Service</span></button> </center>
                  <?php } else {?>
                  <div class="input-group mt-4 pt-5">
                    <input type="password" readonly="" class="form-control" placeholder="API Key" value="<?php echo $pix->api_key; ?>"name="pixops_api_key">
                  </div>
                  <div class="form-group mt-2">
                          <input type="text" readonly=""  name="user_email" class="form-control" id="user_email" value="<?php echo $pix->email; ?>">
                          <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                  </div>
                  <center><button class="btn btn-danger" name="api-disconnect" id="api-disconnect" type="button"><span class="icon p-2"><i class="fa fa-times" style="
                   font-size: 20px;"></i></span><span>Disconnect</span></button></center>
                      <?php }?>
            </form>

            <div id="result" class="text-center mb-2 text-white bg-success"></div>

            <div id="invalid" class="text-center mb-2 text-white bg-danger"></div>
            <hr>
            <?php if ($pix == null) {?>
                    <div class="row">
                <div class="col-md-1">
                    <div class="Pixops-icon">
                        <i class="fa fa-sign-in"></i>
                    </div>
                </div>
                <div class="col-md-5">
                   <p class="Pixops-title"><b>1. Enter your API key.</b></p>
                   <p class="Pixops-subtitle">Copy the API key you have received via email or you can get it from <a target="_blank" href="http://pixops.io/app">Pixops dashboard</a>.</p>
                </div>
                <div class="col-md-1">
                    <div class="Pixops-icon">
                        <i class="fa fa-key"></i>
                    </div>
                </div>
                <div class="col-md-5">
                <p class="Pixops-title"><b>2. Connect to Pixops.</b></p>
                <p class="Pixops-subtitle">Fill in the upper API key field and connect to Pixops service.</p>
                </div>
            </div>
               <?php } else {?>
                <?php
if (isset($_POST['pix-setting'])) {
            $lazy_load =sanitize_text_field($_POST['pix_lazy']);
            // For quality
            $quality = sanitize_text_field($_POST['pix_quality']);
            $pix_lazy_load = update_option('pixop_lazy_load', $lazy_load);

            $pix_quality = update_option('pixop_quality', $quality);
        }
            ?>
         <div class="row">
                    <div class="col-md-12">
                        <h3 class="text-dark mb-4">Pixops Settings</h3>
                         <form id="pixop-setting" action="" method="post">
                         <div class="row">
                            <div class="col-md-3">
                              <h6 class="text-dark">Lazy Load:</h6>
                            </div>
                            <div class="col-md-9">
                              <label class="switch">
                                <input type="hidden" name="pix_lazy" value="false">
                              <input type="checkbox" value="true"<?php checked('true', get_option('pixop_lazy_load'), true);?> id ="pix-lazy" name="pix_lazy">
                              <span class="slider round"></span>
                            </label>
                         </div>
                         <?php
$test_quality = get_option('pixop_quality');
            // foreach ($test_quality as $test)
            ?>
                         <div class="col-md-3">
                              <h6 class="text-dark pt-4">Quality:</h6>
                            </div>
                            <div class="col-md-9">
                            <div class="form-group">
                        <label for="Select"></label>
                        <select id="Select" name="pix_quality" class="form-control">
                            <option  <?php if ($test_quality == 'select') {
                echo 'selected';
            }
            ?>value='select'>Select Quality</option>
                                    <option <?php if ($test_quality == '1') {
                echo 'selected';
            }
            ?> value='1'>1</option>
                                    <option <?php if ($test_quality == '2') {
                echo 'selected';
            }
            ?> value='2'>2</option>
                                    <option <?php if ($test_quality == '3') {
                echo 'selected';
            }
            ?> value='3'>3</option>
                                    <option <?php if ($test_quality == '4') {
                echo 'selected';
            }
            ?> value='4'>4</option>
                                  </select>
                                </div>
                                   <button  class="btn mt-4 btn-outline-success" type="submit" name="pix-setting" id="pix-setting" style="width:20%;">Submit</button>
                            </div>


                        </div>
                    </form>
                </div>
            </div>
        <?php }?>
                    <!-- Privacy,term in footer section -->
        <div class="pixops-admin mt-4">
          <p class="px-a p-2"><a href="https://pixops.io/privacy-policy/">Privacy</a></p>
          <p class="px-a p-2"><a href="https://pixops.io/terms-and-conditions/">Terms</a></p>
          <p class="px-a p-2"><a href="https://pixops.io">Pixops</a></p>
        </div>  <!-- End Privacy,term in footer section -->
            </div> <!--End of connect section-->


       </div><!--pill Panel-->
    </div> <!--End of Card-->
</div><!--End of Container-->
        <?php

    }
}
function pixop_add_settings_link( $links ) {
    $url = esc_url( add_query_arg(
		'page',
		'pixops',
		get_admin_url() . 'admin.php'
	) );
	$links = array_merge( array(
		'<a href="' . $url. '">' . __( 'Settings', 'textdomain' ) . '</a>'
	), $links );

	return $links;

}
$pixops_Admin = new PixOps_Admin;
$pixop_api = get_option('pixop_api');
        $pix = json_decode($pixop_api);
        if ($pix == null) {
          
add_action('admin_notices',  array($pixops_Admin,'pixops_admin_notice_not_registered')); 
} 																	
add_action('admin_menu', array($pixops_Admin, 'pixops_add_menu'));

add_action('plugin_action_links_pixops-image-optimzation/pixops.php', 'pixops_add_settings_link');
add_action('admin_enqueue_scripts', array($pixops_Admin, 'pixops_admin_theme_style'));
add_action('admin_enqueue_scripts', array($pixops_Admin, 'pixops_enqueue_scripts'));
// ----------------------------------ajax action-------------------------------------------//
add_action('wp_ajax_pixops_ajax_request', array($pixops_Admin, 'pixops_handle_ajax_request'));
add_action('wp_ajax_nopriv_pixops_ajax_request', array($pixops_Admin, 'pixops_handle_ajax_request'));

// ----------------------------------Post meta-------------------------------------------//
add_action('wp_ajax_clear_log_action', array($pixops_Admin, 'clear_log_ajax'));