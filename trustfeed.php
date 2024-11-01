<?php
/**
 * Plugin Name:       TrustFeed Reviews and Customer Feedback for WooCommerce
 * Plugin URI:        https://www.trustfeed.co
 * Description:       Seamlessly collect and display customer feedback, reviews and testimonials to boost sales and social proof.
 * Version:           1.1.3
 * Author:            TrustFeed
 * Author URI:        https://www.trustfeed.co
 * Developer:         TrustFeed
 * Developer URI:     https://www.trustfeed.co
 * Text Domain:       trustfeed
 * Domain Path:       /languages
 *
 * WC requires at least: 2.2
 * WC tested up to: 3.6.5
 *
 * Copyright:         © 2019 TrustFeed.
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
*/

// Define TF_PLUGIN_FILE.

if ( ! defined( 'TF_PLUGIN_FILE' ) ) {
	define( 'TF_PLUGIN_FILE', __FILE__ );
} 

/*
 * Plugin constants  TRUSTFEED
 */

if(!defined('TRUSTFEED_PLUGIN_VERSION'))
	define('TRUSTFEED_PLUGIN_VERSION', '1.1.2');

if(!defined('TRUSTFEED_URL'))
	define('TRUSTFEED_URL', plugin_dir_url( __FILE__ ));

if(!defined('TRUSTFEED_PATH'))
	define('TRUSTFEED_PATH', plugin_dir_path( __FILE__ ));

if(!defined('TRUSTFEED_ENDPOINT'))
	define('TRUSTFEED_ENDPOINT', 'trustfeed.com');

if(!defined('TRUSTFEED_PROTOCOL'))
	define('TRUSTFEED_PROTOCOL', 'https');

if(!defined('TF_ABSPATH')) 
	define( 'TF_ABSPATH', dirname( TF_PLUGIN_FILE ) . '/' );

if(!defined('TF_PLUGIN_BASENAME')) 
	define( 'TF_PLUGIN_BASENAME', plugin_basename( TF_PLUGIN_FILE ) );

if(!defined('TF_MIN_WP_VERSION')) 
	define( 'TF_MIN_WP_VERSION', '3.7' );

if(!defined('TF_SUPPORTED_WP_VERSION')) 
	define( 'TF_SUPPORTED_WP_VERSION', version_compare( get_bloginfo( 'version' ), TF_MIN_WP_VERSION, '>=' ) );

/**
 * Defines the minimum version of WordPress that will be officially supported.
 *
 * @var string TF_MIN_WP_VERSION_SUPPORT_TERMS The version number
 */

	define( 'TF_MIN_WP_VERSION_SUPPORT_TERMS', '4.9' );

/*
 * Main class
 *
 *
 * Class TrustFeed
 *
 * This class creates the option page and add the web app script
 */
if ( ! class_exists( 'TrustFeed' ) ) :

class TrustFeed {

	/**
	* The security nonce
	*
	* @var string
	*/

	private $_nonce = 'trustfeed_admin';
	
	/**
	 * The option name
	 *
	 * @var string
	 */
	private $option_name = 'trustfeed_data';

	/**
	 * TrustFeed constructor.
     *
	 * The main plugin actions registered for WordPress
	 */
	public function __construct(){

		// Admin Top Level and sub page calls
		add_action( 'admin_menu', array($this,'tf_register_top_level_menu')); 
		add_action( 'admin_menu', array($this,'tf_register_sub_menu'));  
		add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));
		add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
		add_action( 'wp_enqueue_scripts', array( $this, 'trustfeed_enqueue_styles' ) );

		// Check background tasks for the system report
		add_action( 'wp_ajax_gf_check_background_tasks', array( $this, 'check_background_tasks' ) );
		
		// WC integration
		// NOTIFY TRANSACTION TO TRUSTFEED WHEN ORDER IS UPDATED TO COMPLETE
		add_action( 'woocommerce_order_status_completed', array( $this, 'trustfeed_woocommerce_complete' ), 10, 1);				
		add_filter( 'woocommerce_product_review_comment_form_args', array( $this, 'trustfeed_review_form' ), 10, 1);
		add_action( 'comment_form', array( $this, 'trustfeed_more_comments' ) ); 
		
		remove_action( 'woocommerce_product_tabs', 'woocommerce_product_reviews_tab',10, 1);
		remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_reviews_panel',10, 1);
		add_shortcode('reviewcounts', array( $this, 'trustfeed_wc_display_reviews_counts' ) );

		$trustfeeddata = $this->getData();
		$apiKey = (isset($trustfeeddata['tf_api_key'])) ? $trustfeeddata['tf_api_key'] : '';
		$tf_trustfeed_review = $trustfeeddata['tf_trustfeed_review'];
		if($apiKey && $tf_trustfeed_review){
			add_filter( 'woocommerce_product_tabs', array( $this, 'trustfeed_woo_remove_reviews_tab' ), 98 ); 
		}

		add_filter('https_ssl_verify', '__return_false');
		add_filter('https_local_ssl_verify', '__return_false');
		add_action( 'admin_init', array($this, 'trustfeed_add_privacy_policy_content')); 


		add_shortcode('trustfeed-reviews-shop', array($this, 'trustfeed_reviews_shop_loop_star_rating'));
		add_shortcode('trustfeed-reviews-single', array($this, 'trustfeed_reviews_single_star_rating'));
		
		add_filter( 'plugin_action_links_' . TF_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) ); 
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}
	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return array
	 */

	public static function plugin_action_links( $links ) {
		
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=tf-settings' ) . '" aria-label="' . esc_attr__( 'View TrustFeed settings', 'trustfeed' ) . '">' . esc_html__( 'Settings', 'trustfeed' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 *
	 * @return array
	 */

	public static function plugin_row_meta( $links, $file ) {
		
		if ( TF_PLUGIN_BASENAME === $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'trustfeed_docs_url', 'https://admin.trustfeed.co' ) ) . '" aria-label="' . esc_attr__( 'View TrustFeed Dashboard', 'trustfeed' ) . '">' . esc_html__( 'TrustFeed Dashboard', 'trustfeed' ) . '</a>',
				'apidocs' => '<a href="' . esc_url( apply_filters( 'trustfeed_apidocs_url', 'https://www.trustfeed.co/ratingandreviews/documentation/' ) ) . '" aria-label="' . esc_attr__( 'View API docs', 'trustfeed' ) . '">' . esc_html__( 'Documentation', 'trustfeed' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'trustfeed_support_url', 'https://www.trustfeed.co/slack-community/' ) ) . '" aria-label="' . esc_attr__( 'Visit community customer support', 'trustfeed' ) . '">' . esc_html__( 'Community Support', 'trustfeed' ) . '</a>',
				'email' => '<a href="' . esc_url( apply_filters( 'trustfeed_support_email', 'mailto: hello@trustfeed.co' ) ) . '" aria-label="' . esc_attr__( 'Visit email support support', 'trustfeed' ) . '">' . esc_html__( 'Email Support', 'trustfeed' ) . '</a>',
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	public function trustfeed_woo_remove_reviews_tab($tabs) {
		
		global $product, $post;
		
		$review_count =  self::trustfeed_wc_display_reviews_counts();
		$tabs['reviews'] = array(
			'title'    => sprintf( __( 'Reviews (%d)', 'woocommerce' ), $review_count ),
			'priority' => 10,
			'callback' => array($this,'trustfeed_product_reviews')
		); 
		return $tabs;
	}
	public function trustfeed_enqueue_styles() {
		
		wp_register_style('trustfeed_review_style', plugins_url('assets/css/style.css',__FILE__ ));
		wp_enqueue_style('trustfeed_review_style');
		
		//wp_register_style('trustfeed_review_bootstrap', plugins_url('css/bootstrap.min.css',__FILE__ ));
		//wp_enqueue_style('trustfeed_review_bootstrap');

		//wp_enqueue_script('trustfeed-script',  plugins_url('js/bootstrap.min.js',__FILE__ ));
		//wp_enqueue_script('trustfeed-script');
	}

	/**
	* Adds the Trustfeed Top label to the WordPress Admin Sidebar Menu
	*/
	public function tf_register_top_level_menu(){
		add_menu_page(
			'TrustFeed',
			'TrustFeed',
			'manage_options',
			'tf-info',
			array($this, 'tf_display_top_level_menu_page'),
			TRUSTFEED_URL. 'assets/images/icon.png',
			57
		);
	}

	/**
	* Adds the Trustfeed Sub label to the WordPress Admin Sidebar Menu
	*/
	public function tf_register_sub_menu() {
		add_submenu_page(
			'tf-info',
			'Settings',
			'Settings',
			'manage_options',
			'tf-settings',
			array($this, 'tf_display_setting_page')
		);
		add_submenu_page(
			'tf-info',
			'System Requirement',
			'System Requirement',
			'manage_options',
			'tf-status',
			array($this, 'tf_display_status_page')
		);
	}

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts(){
		wp_enqueue_style('trustfeed-admin', TRUSTFEED_URL. 'assets/css/admin.css', false, time());
		wp_enqueue_script('trustfeed-admin', TRUSTFEED_URL. 'assets/js/admin.js', array(), time());
		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);
		wp_localize_script('trustfeed-admin', 'trustfeed_exchanger', $admin_options);
	}

	/**
	 * Returns the saved options data as an array
	 *
     * @return array
	 */
	private function getData(){
		return get_option($this->option_name, array());
	}
	
	/**
	 * Callback for the Ajax request
	 *
	 * Updates the options data
	 *
     * @return void
	 */
	public function storeAdminData(){
		if (wp_verify_nonce($_POST['tf_security'], $this->_nonce ) === false)
			die('Invalid Request! Reload your page please.');
				
		$data = $this->getData();
		
		$_POST['trustfeed_tf_dev_credits'] = isset( $_POST['trustfeed_tf_dev_credits'] ) ? $_POST['trustfeed_tf_dev_credits'] : '0';				
		
		$_POST['trustfeed_tf_trustfeed_review'] = isset( $_POST['trustfeed_tf_trustfeed_review'] ) ? $_POST['trustfeed_tf_trustfeed_review'] : '0';				
		
		foreach ($_POST as $field=>$value) {
			if (substr($field, 0, 10) !== "trustfeed_")
				continue;
			
			if (empty($value))
				unset($data[$field]);
				// We remove the trustfeed_ prefix to clean things up
			
			$field = substr($field, 10);
			$data[$field] = esc_attr__($value);
		}
		update_option($this->option_name, $data);
		echo __('Saved!', 'trustfeed');
		die();
	}

	/**
	 * Get a Dashicon for a given status
     *
	 * @param $valid boolean
     *
     * @return string
	 */
	private function getStatusIcon($valid){
		return ($valid) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>';
	}	
	public function check_background_tasks(){
		check_ajax_referer( 'tf_check_background_tasks', 'nonce' );
		echo 'ok';
		die();
	}
	/**
	 * Get a TRUST Feed API KEY Status
     *
	 * @param $valid boolean
     *
     * @return string
	 */
	private function getTrustFeedResponseStatus(){
		
		$trustfeeddata = $this->getData();			
		$apiKey = (isset($trustfeeddata['tf_api_key'])) ? $trustfeeddata['tf_api_key'] : '';
		if($apiKey){
			$url = 'https://api.trustfeed.co/v1/transactions';
			$body = array(
					"completionDate" => "",
					'consumer' => array(
						"active" => false,
						"email" =>"string",
						"firstname" => "string",
						"id" => "string",
						"lastname" => "string",
						"userImageUrl" => "string"
					),
					"currency" => "string",
					"id" => 0,
					"listingDate" => "",
					"price" => 0,
					'products' => array(
						array(
							"date_created" =>"",
							"date_modified" => "",
							"dimensions" => "string",
							"featured" => "string",
							"id" => 0,
							"name" => "string",
							"price" => 0,
							"productId" => "string",
							"productImageUrl" => "string",
							"sale_price" => 0,
							"short_description" => "string",
							"sku" => "string",
							"status" => "string",
							"type" => "string"
						)
					),
					"serviceType" => "crowdfunding"
				);
			
			$response = wp_remote_post(
				$url,
				array(
					'body' => json_encode($body),
					'headers' => array(
						'apikey' => $apiKey, 
						'Content-Type' => 'application/json'
					)
				)
			);	
			if($response['response']['code'] == 200){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}	 
	}
	/**
	 * Make an API call to the TrustFeed API and returns the response
     *
     * @return array
	 */
	private function getTrustFeedResponse(){
		global $wpdb,$woocommerce,$product;
		$args = array (
			'post_type' => 'product', 
			'post_ID' => $product->get_id(),  // Product Id
			'status' => "approve", // Status you can also use 'hold', 'spam', 'trash', 
			'number' => 100  // Number of comment you want to fetch I want latest approved post soi have use 1
		);
		$trustfeeddata = $this->getData();
		$apiKey = (isset($trustfeeddata['tf_api_key'])) ? $trustfeeddata['tf_api_key'] : '';
		if($apiKey){
			$url = 'https://api.trustfeed.co/v1/reviews/' . $product->get_id().'?page=0&size=10&direction=asc';
			$response = wp_remote_get(
				$url,
				array(
					'headers' => array(
						'apikey' => $apiKey,
						'Content-Type' => 'application/json'
					)
				)
			);
			return $response;
		}
	}
	
	/**
	* Adds the Trustfeed Top label meu info page
	*/
	public function tf_display_top_level_menu_page(){
	?>
		<div class="wrap">
			<h1><?php _e('TrustFeed Reviews and Customer Feedback for WooCommerce', 'trustfeed'); ?></h1>
			<p><strong>You can start for free!</strong></p>
			<ul>
				<li>To learn more about TrustFeed you can visit us at <a href="https://www.trustfeed.co/" title="TrustFeed Reviews and Customer Feedback for WooCommerce" rel="nofollow">TrustFeed</a></li>
				<li>To view our admin panel, just login at <a href="https://admin.trustfeed.co/" title="TrustFeed Reviews and Customer Feedback for WooCommerce" rel="nofollow">TrustFeed</a>  and create a free account.</li>
			</ul>
			<div class="go-dashboard"><a title="TrustFeed Reviews Dashboard" target="_blank" href="https://admin.trustfeed.co">Go to Dashboard</a></div>  
		</div>
		<?php 
	}

	/**
	* Adds the Trustfeed System Requirement page
	*/
	public function tf_display_status_page(){
		global $wpdb, $wp_version;
		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );
		$tf_version = $plugin_data['Version'];
		$TrustFeed_API_status = $this->getTrustFeedResponseStatus();
		if($TrustFeed_API_status){
			$trustfeed_api_status = 'No Problems';
			$status_class = 'active';
			$status_mark_class = 'dashicons-yes success-message';
		}else{
			$trustfeed_api_status = 'Not Activated';
			$status_class = 'noactive';
			$status_mark_class = 'dashicons-no-alt error-message';
		}
		echo '<div class="wrap">
				<h1>API KEY Activation</h1>
				<table class="api-status wp-list-table">
					<tbody>
						<tr>
							<td class="'.$status_class.'"><label class="status-title">API KEY Activation</label></td>
							<td class="'.$status_class.'"><label class="status-value"><span class="dashicons '.$status_mark_class.'"></span>'. $trustfeed_api_status .'</label></td>
						</tr>
					</tbody>
				</table>
			</div>';
			echo '<h3><span>Server Environment</span></h3>';
			echo '<table class="tf_system_report wp-list-table widefat fixed striped">
					<thead>
						<tr><th colspan="2">WordPress Environment</th></tr>
					</thead>
					<tbody id="the-list" data-wp-lists="list:feed">
						<tr><td>Site URL</td><td>'. get_site_url() .'</td></tr>
						<tr><td>WordPress Version</td><td>'. $wp_version .'</td></tr>
						<tr><td>TrustFeed Version</td><td>'. TRUSTFEED_PLUGIN_VERSION .'</td></tr>
						<tr><td>WooCommerce Version</td><td>'. WC_VERSION .'</td></tr>
						<tr><td>PHP Version</td><td>'. esc_html( phpversion() ) .'</td></tr>
						<tr><td>Memory Limit (memory_limit)</td><td>'. esc_html( ini_get( 'memory_limit' ) ) .'</td></tr>
						<tr><td>Maximum File Upload Size (upload_max_filesize)</td><td>'. esc_html( ini_get( 'upload_max_filesize' ) ) .'</td></tr>
						<tr><td>Maximum File Uploads (max_file_uploads)</td><td>'. esc_html( ini_get( 'max_file_uploads' ) ) .'</td></tr>
						<tr><td>Maximum Post Size (post_max_size)</td><td>'. esc_html( ini_get( 'post_max_size' ) ) .'</td></tr>
					</tbody>
				</table>';
	}

	/**
	* Outputs the Admin Dashboard layout containing the form with all its options
	*
    * @return void
	*/
	public function tf_display_setting_page(){

		$data = $this->getData();
		$not_ready = (empty($data['api_key']));
	    $has_engager_preview = (isset($_GET['trustfeed-demo-engager']) && $_GET['trustfeed-demo-engager'] === 'go');
	    $has_wc = (class_exists('WooCommerce'));
		$signup = 'https://admin.trustfeed.co';
		$start = 'https://www.trustfeed.co/';
		$documentation = 'https://www.trustfeed.co/ratingandreviews/documentation/';
		$communitybased = 'https://www.trustfeed.co/slack-community';
		?>
		<div class="wrap">
			<h1><?php _e('Trustfeed Settings - Start Reviews and Customer Feedback!', 'trustfeed'); ?></h1>
			<form id="trustfeed-admin-form" class="postbox">
				<div class="form-group inside">
					<?php 
	                /*
					 * --------------------------
					 * API Settings
					 * --------------------------
					 */
	                ?>
					<h3><?php _e('Trustfeed API Settings', 'trustfeed'); ?></h3>
					<?php if ($not_ready): ?>
						<p>
							<?php _e('Make sure you have a Trustfeed account first, it\'s free! 👍', 'trustfeed'); ?>
                            <?php _e('You can <a href="https://admin.trustfeed.co" target="_blank">create an account here</a>.', 'trustfeed'); ?>
                        </p>
                    <?php else: ?>
		                <?php _e('Access your <a href="https://dashboard.trustfeed.com" target="_blank">Trustfeed dashboard here</a>.', 'trustfeed'); ?>
                    <?php endif; ?>
                    <table class="form-table">
						<tbody>
							<tr>
								<td scope="row">
									<label><?php _e( 'API Key', 'trustfeed' ); ?></label>
								</td>
								<td>
									<input name="trustfeed_tf_api_key"
                                           id="trustfeed_api_key"
                                           class="regular-text"
                                           type="text"
                                           value="<?php echo (isset($data['tf_api_key'])) ? $data['tf_api_key'] : ''; ?>"/> <span><i>This is your Marketplace API Key</i></span>
										   <p><?php echo sprintf( __( '<a target="_blank" href="%1$s">Sign up</a> to get you API key and <a target="_blank" href="%2$s">start</a> for free! Need help? Check out our <a target="_blank" href="%3$s">documentation</a> or join our <a target="_blank" href="%4$s">community-based support forum</a> to learn, share and troubleshoot.' ), $signup, $start,$documentation,$communitybased ); ?>
								</td>
							</tr>
							<tr>
								<td scope="row">
									<label><?php _e( 'Enable plugin developer credits', 'trustfeed' ); ?></label>
								</td>
								<td>
									<input name="trustfeed_tf_dev_credits"
                                           id="trustfeed_dev_credits"
                                           class=""
                                           type="checkbox"
                                           value="1"<?php (isset($data['tf_api_key'])) ? checked(1, $data['tf_dev_credits'], true) : '';  ?> /><span>Enable plugin developer credits.</span>
								</td>
							</tr>							
							<tr>
								<td scope="row"><label><?php _e( 'Enable review', 'trustfeed' ); ?></label></td>
								<td>									
									<input  name="trustfeed_tf_trustfeed_review"                                           
											id="trustfeed_trustfeed_review"                                           
											class=""                                           
											type="checkbox"                                           
											value="1"<?php (isset($data['tf_api_key'])) ? checked(1, $data['tf_trustfeed_review'], true) : '';  ?> /><span>Enable Reviews in product page and catalogue.</span>								
								</td>							 
							</tr>
						</tbody>
					</table>
				</div>
				<hr>
				<div class="inside">
					<button class="button button-primary" id="trustfeed-admin-save" type="submit"><?php _e( 'Save', 'trustfeed' ); ?></button>
				</div> 
			</form>
		</div>
		<?php 
	}	

	/* 
	* NOTIFY TRANSACTION TO TRUSTFEED WHEN ORDER IS UPDATED TO COMPLETE
	*   
	*/
	public function trustfeed_woocommerce_complete( $order_id ) {
		
		/*** Check if WooCommerce is active***/
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			error_log( "Payment has been received for order ".$order_id );
			
			// Get an instance of the WC_Order object
			$order = wc_get_order( $order_id );
			$order_data = $order->get_data(); // The Order data						
			$trustfeeddata = $this->getData();			
			$apiKey = (isset($trustfeeddata['tf_api_key'])) ? $trustfeeddata['tf_api_key'] : '';			 			
			$url = 'https://api.trustfeed.co/v1/transactions';						
			
			error_log( 'APIKEY: '.$apiKey);
			
			foreach ($order->get_items() as $item_key => $item_values):
				$item_data = $item_values->get_data();
				$product = wc_get_product( $item_data['product_id'] );
				$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $item_data['product_id'] ), 'single-post-thumbnail' );
				$transaction_data = array(
					'id' => '',
					'consumer' => array(
						'email' => $order_data['billing']['email'], //username
						'firstname' => $order_data['billing']['first_name'],
						'lastname' => $order_data['billing']['last_name']
					),
					'products' => array(
						array(
							'productId' => $product->get_id(),
							'name' => $product->get_name(),
							'type' => $product->get_type(),
							'date_created' => $product->get_date_created()->getTimestamp(),
							'date_modified' => $product->get_date_modified()->getTimestamp(),
							'status' => $product->get_status(),
							'featured' => $product->get_featured(),
							'short_description' => $product->get_short_description(),
							'sku' => $product->get_sku(),
							'price' => $product->get_price(),
							'sale_price' => $product->get_sale_price(),
							'dimensions' => '',
							'productImageUrl' => $image_url[0]
						)
					),
					'listingDate' => '',
					'completionDate' => '',
					'serviceType' => 'crowdfunding',
					'price' => $item_data['total'], //$order_data['shipping_total'],
					'currency' => get_woocommerce_currency()
				);
				error_log('TRANS BODY - ' . json_encode($transaction_data));
				$response = wp_remote_post(
					$url,
					array(
						'body' => json_encode($transaction_data),
						'headers' => array(
							'apikey' => $apiKey, 
							'Content-Type' => 'application/json'
						)
					)
				);
				error_log( 'Response Code: ' . wp_remote_retrieve_response_code( $response ) );
				error_log( 'Response Message: ' . wp_remote_retrieve_response_message( $response ) );
			endforeach;
		}
	}

	public function trustfeed_review_form( $review_form ) {
		$trustfeeddata = $this->getData();
		$formDisable = (isset($trustfeeddata['tf_form_disable'])) ? $trustfeeddata['tf_form_disable'] : '';
		error_log('Form Disable:: ' . $formDisable);
		if($formDisable != 'yes'){
			$review_form['comment_field'] = '';
			$review_form['label_submit'] = '';
			$review_form['class_form'] = '';
			$review_form['class_submit'] = '';
		}
		return $review_form;
	}
	public function trustfeed_more_comments( $post_id ) {
		$trustfeeddata = $this->getData();
		$formDisable = (isset($trustfeeddata['tf_form_disable'])) ? $trustfeeddata['tf_form_disable'] : '';
		if($formDisable != 'yes'){
			echo '<script> jQuery("#review_form_wrapper").remove(); </script>';
		}
	}
	
	public function trustfeed_wc_display_product_reviews(){
		$trustfeeddata = $this->getData();
		$trustfeed_dev_credits = $trustfeeddata['tf_dev_credits'];
		$response = $this->getTrustFeedResponse();
		if($response['response']['code'] == 200){
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response ); 
				$data = json_decode( $body );
				//echo "<pre>"; print_r($data); echo "</pre>"; 
				if( ! empty( $data ) && ! empty($data->reviews) ) {
					$review_list = array();
					$response_total 	= $data->total;
					$response_size 		= $data->size;
					$response_records 	= $data->records;
					$response_page 		= $data->page;
					foreach( $data->reviews as $datasort ){
						$orderby = strtotime($datasort->date);
						$review_list[$orderby] = $datasort;
					}
					rsort($review_list);
					$comment_rating = array();
					$five=0;
					$four=0;
					$three=0;
					$two=0;
					$one=0;
					$count=0;
					$tagQuestions_array = array();
					$reviewer_array = array();
					$totalstarRating = array();
					foreach( $review_list as $review ){
						$totalRating = 0;
						$totalElements = 0;
						foreach( $review->starRating as $starRating ){
							$totalstarRating[] = $starRating->ratingStars;	
							$totalRating = $totalRating + $starRating->ratingStars;
							$totalElements = $totalElements + 1;
							$rating = $starRating->ratingStars;
							switch($rating) {
								case "5":
									$five++;
									break;
								case "4":
									$four++;
									break;
								case "3":
									$three++;
									break;
								case "2":
									$two++;
									break;
								case "1":
									$one++;
									break;
							}
						}
						$comment_rating[] = (int) esc_attr( $totalRating/$totalElements );
						$tagQuestions_array[] = $review->tagQuestions;
						$review_array = array(
							'id'	=>	$review->id,
							'date'	=>	$review->date,
							'reviewer'		=>	$review->reviewer,
							'starRating'	=>	$review->starRating,
							'textQuestions'	=>	$review->textQuestions
						);
						if($review_array){
							$reviewer_array[] = $review_array;
						}
					}
					$average = array_sum($totalstarRating)/count($totalstarRating); 
					//$average = array_sum($comment_rating)/count($comment_rating);
					$rating =  round($average, 2);
					$ratingstars = $rating / 5; 
					$ratingstarwidth = $ratingstars*100;
					$starfive 		= round(($five/count($totalstarRating))*100);
					$starfour 		= round(($four/count($totalstarRating))*100); 
					$starthree 		= round(($three/count($totalstarRating))*100);
					$startwo 		= round(($two/count($totalstarRating))*100);
					$starone 		= round(($one/count($totalstarRating))*100);
					$review_count 	= count($data->reviews);
					$reviews_array 	= array(
						'dev_credits'			=>	$trustfeed_dev_credits,
						'total'					=>	$response_total,
						'size'					=>	$response_size,
						'records'				=>	$response_records,
						'page'					=>	$response_page,
						'review_count'			=>	$review_count,
						'average'				=>	$average,
						'rating'				=>	$rating,
						'ratingstars'			=>	$ratingstars,
						'ratingstarwidth'		=>	$ratingstarwidth,
						'starfive'				=>	$starfive,
						'starfour'				=>	$starfour,
						'starthree'				=>	$starthree,
						'startwo'				=>	$startwo,
						'starone'				=>	$starone,
						'tagQuestions_array'	=>	$tagQuestions_array,
						'reviewer_array'		=>	$reviewer_array 
					);
					return $reviews_array; 
				}
			}
		}
	} 
	public function trustfeed_product_reviews(){
		global $wpdb,$woocommerce,$product;
		$reviewdata = self::trustfeed_wc_display_product_reviews();
		wc_get_template( 'product-reviews.php', array( 'review' => $reviewdata ), 'trustfeedreview', plugin_dir_path( __FILE__ ) . '/templates/' );
	}
	public function trustfeed_reviews_shop_loop_star_rating(){
		global $woocommerce, $product;
		
		$trustfeeddata = $this->getData();
		$tf_trustfeed_review = $trustfeeddata['tf_trustfeed_review'];
		if($tf_trustfeed_review){
			$reviewdata = self::trustfeed_wc_display_product_reviews();
			$average  = $reviewdata['average'];
			$rating  = $reviewdata['rating'];
			$ratingstars  = $reviewdata['ratingstars']; 
			$ratingstarwidth  = $reviewdata['ratingstarwidth']; 
			echo '<div class="pradeep trustfeed star-rating"><span style="width:'.( ( $rating / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>';
			?>
			<style type='text/css'>
			.woocommerce .trustfeed.star-rating span::before,.woocommerce .trustfeed.star-rating::before{
				font-family: "star";
				content: '\53\53\53\53\53'; 
			}
			</style>
		<?php 
		}
	}
	public function trustfeed_reviews_single_star_rating() {
		global $woocommerce, $product;
		
		$trustfeeddata = $this->getData();
		$tf_trustfeed_review = $trustfeeddata['tf_trustfeed_review'];
		if($tf_trustfeed_review){
			$reviewdata = self::trustfeed_wc_display_product_reviews();
			$review_count = $reviewdata['review_count'];
			$average  = $reviewdata['average'];
			$rating  = $reviewdata['rating'];
			$ratingstars  = $reviewdata['ratingstars']; 
			$ratingstarwidth  = $reviewdata['ratingstarwidth']; 
			?>
			<div class="pradeep woocommerce-product-rating">
				<?php 
				echo '<div class="trustfeed star-rating"><span style="width:'.( ( $rating / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>';
				if($review_count > 0){}else{$review_count=0;}
				?>
				<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a> 
			</div>
			<style type='text/css'>
			.woocommerce .woocommerce-product-rating .star-rating span::before,.woocommerce .woocommerce-product-rating .star-rating::before{
				font-family: "star";
				content: '\53\53\53\53\53'; 
				position: inherit;
			}
			</style>
		<?php
		}
	}
	public function trustfeed_wc_display_reviews_counts(){
		$response = $this->getTrustFeedResponse();
		if($response['response']['code'] == 200){
			$count = 0;
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response ); // use the content
				$data = json_decode( $body );
				if( ! empty( $data ) && ! empty($data->reviews) ) {
					$count = count($data->reviews); 
				}
			}
			return $count;
		}
	}
	public function trustfeed_add_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		$content = sprintf(
			__( ' This site uses TrustFeed to improve customer experience and to leverage user generated content. TrustFeed.co acts as a service provider to our site to help us collect, drive and display customer reviews and user generated content. As a customer, you agree to receive an email after completing a purchase in our website. We use TrustFeed to send you an invitation/request to leave a review in our site. When you make a purchase on this site and when you complete a satisfaction survey, we might send your first name, last name, ratings, reviews and other information about your purchase, such as: product ID, name and price to TrustFeed.co, its subsidiaries and affiliates to collect, process, store and use your personal data, and if/when applicable may be passed to and processed by TrustFeed.co. TrustFeed.co will not sell, exchange or transfer your Personal Information with any third party company or person that is not affiliated to TrustFeed.co , unless (I) this is necessary for the provision of TrustFeed.co�s services or products requested by our site. TrustFeed.co process your data following strict security measures and protocols to ensure the safety of your personal data. To better protect your privacy, we, "TrustFeed" and our affiliates and subsidiaries (collectively, �TrustFeed�, �we�, �us�, or �our�), provide this Privacy Policy explaining how we collect, use, and disclose Information (defined below) that we obtain about visitors to our website (www.trustfeed.co (the �Site�) and the services available through our Site (the �Services�). You agree to be bound by TrustFeed Terms, Privacy Policy and Data Processing Agreement, The TrustFeed privacy policy is <a href="https://www.trustfeed.co/ratingandreviews/privacy-policy/" target="_blank">here</a>.',
				'trustfeed' ),
				'https://www.trustfeed.co/privacy-policy' 
		);
		wp_add_privacy_policy_content(
			'Reviews and Customer Feedback TrustFeed',
			wp_kses_post( wpautop( $content, false ) )
	   );
	}
}
endif;	



/*
 * Starts our plugin class, easy!
 */

new TrustFeed();

if(class_exists('TrustFeed')){	
	if ( ! function_exists( 'woocommerce_template_loop_rating' ) ) {
		/**
		 * Display the average rating in the loop.
		 */
		function woocommerce_template_loop_rating() {
			//wc_get_template( 'loop/rating.php' );
			$trustfeedoutput = do_shortcode('[trustfeed-reviews-shop]');
			if($trustfeedoutput){
				echo do_shortcode('[trustfeed-reviews-shop]'); 
			}
		}
	}
	if ( ! function_exists( 'woocommerce_template_single_rating' ) ) {
		/**
		 * Output the product rating.
		 */
		function woocommerce_template_single_rating() {
			if ( post_type_supports( 'product', 'comments' ) ) {
				//wc_get_template( 'single-product/rating.php' );
				$trustfeedoutput = do_shortcode('[trustfeed-reviews-single]');
				if($trustfeedoutput){
					echo do_shortcode('[trustfeed-reviews-single]'); 
				}
			}
		}
	}
}


 