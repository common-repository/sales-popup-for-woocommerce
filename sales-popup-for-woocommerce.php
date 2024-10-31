<?php
/**
 * Plugin Name: Sales Popup for Woocommerce
 * Description: Show pop-ups of WooCommerce products sold to increase sales by showing products that may interest the user 
 * Version: 1.0.2
 * Author: Daniel Riera
 * Author URI: https://danielriera.net
 * Text Domain: wtsales
 * WC requires at least: 3.0
 * WC tested up to: 8.3.1
 * Required WP: 5.0
 * Tested WP: 6.4.2
 */


if(!defined('ABSPATH')) { exit; }

define('WTSales_PATH', dirname(__FILE__).'/');
define('WTSales_POSITION_SHOW', get_option('_wcs_position', 'woocommerce_after_add_to_cart_button'));

require_once WTSales_PATH . 'includes/class.api.php';

if( !class_exists( 'WTSales_MAIN' ) ) {
    class WTSales_MAIN {

        function __construct(){
            add_action('admin_menu', array($this, 'WTSales_menu'));
            add_action('plugins_loaded', array($this, 'WTSales_load_textdomain'));
            add_action('wp_enqueue_scripts', array($this, 'WTSales_load_style') );
            add_action('admin_enqueue_scripts', array($this, 'WTSales_load_style_admin')  );
            add_action('add_meta_boxes', array($this, 'WTSales_add_custom_box'));
            add_action('save_post', array($this, 'WTSales_save_post'));
            add_action('wp_dashboard_setup', array($this, 'WTSales_widget_dashboard'));
        }
        function WTSales_widget_dashboard() {
            wp_add_dashboard_widget('wts_widget_dashboard', __('Priorized Products','wtsales'), array($this, 'WTSales_content_dashboard_widget'));
        }
             
        function WTSales_content_dashboard_widget() {
            
            require_once(WTSales_PATH . 'views/dashboard_widget.php');

        }
        function WTSales_save_post($post_id){
            global $post;
            if(!$post) {
                return;
            }
            $post = get_post($post_id);
            if($post->post_type == 'product') {
                if(isset($_POST['_wts_product_priority'])){
                    update_post_meta($post_id, '_wts_product_priority', '1');
                }else{
                    update_post_meta($post_id, '_wts_product_priority', '0');
                }
            }
        }
        function WTSales_load_textdomain(){
            load_plugin_textdomain( 'wtsales', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
        }
        function WTSales_menu() {
            add_submenu_page('woocommerce',__('Sales Popups','wtsales'),__('Sales Popups','wtsales') , 'manage_options', 'wtsales-options', array($this, 'WTSales_view_page_options') , plugins_url('/images/icon.png', __FILE__) );
        }
        function WTSales_add_custom_box() {
            add_meta_box(
                'wtsales_product_box',
                __('Sales Popup for Woocommerce','wtsales'),
                array($this, 'WTSales_metabox_product'),
                'product',
                'side'
            );
        }
        function WTSales_metabox_product(){
            require_once(WTSales_PATH . 'views/meta-box-product.php');
        }
        function WTSales_view_page_options() {
            require_once(WTSales_PATH . 'views/options.php');
        }
        function WTSales_load_style_admin() {
            wp_enqueue_style( 'wtsales-animate', plugins_url('assets/animate.css', __FILE__) );
        }
        private function WTSales_isRegularExpression($string) {
            set_error_handler(function() {}, E_WARNING);
            $isRegularExpression = preg_match($string, "") !== FALSE;
            restore_error_handler();
            return $isRegularExpression;
        }
        function WTSales_load_style() {
            
            if(get_option('_wts_mobile') == '1' and wp_is_mobile()) {
                return;
            }
            if(get_option('_wts_user_logged', '0') == "1") {
                if(is_user_logged_in()) {
                    return;
                }
            }
            global $wp;
            $urlDontShow = get_option('_wts_exception_urls','');
            $arr = explode("\n", $urlDontShow);
            $current_url = $wp->request;

            if(in_array($current_url, $arr) or
                in_array('/'.$current_url.'/', $arr) or
                in_array($current_url.'/', $arr) or
                in_array('/'.$current_url, $arr)) {
                return;
            }

            
            if(isset($_GET['wts']) and sanitize_text_field( $_GET['wts'] ) == 'true') {
                global $post;
                update_post_meta($post->ID,'_wts_clics',intval(get_post_meta($post->ID,'_wts_clics', true) + 1));

                echo "<script>if (history.pushState) {
                    window.history.pushState('', '', window.location['origin']+window.location['pathname']);
                } else {
                    document.location.href = window.location['origin']+window.location['pathname'];
                }</script>";
                return;
            }
            wp_enqueue_style( 'wtsales-animate', plugins_url('assets/animate.css', __FILE__) );
            wp_enqueue_style( 'wtsales-style', plugins_url('assets/style.css', __FILE__) );
            wp_enqueue_script( 'wtsales-scripts', plugins_url('assets/scripts.js', __FILE__), array('jquery'));

            wp_localize_script( 'wtsales-scripts', 'WTConfig', array(
                'url'    => admin_url( 'admin-ajax.php' ),
                'position' => get_option('_wts_position', 'bottom_left'),
                'effect' => get_option('_wts_effect','fadeInUp'),
                'delay' => get_option('_wts_effect_delay','delay-1s'),
                'duration' =>  get_option('_wts_effect_duration',''),
                'timeout' =>  get_option('_wts_timeout_limit','5000'),
                'delayPopup' => get_option('_wts_time_delay','300')
            ) );
        }
        

    }

    $WTSales_MAIN = new WTSales_MAIN();
}

?>