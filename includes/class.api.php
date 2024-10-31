<?php
if(!defined('ABSPATH')) { exit; }
if( !class_exists( 'WTSales_API' ) ) {
    class WTSales_API {


        public $nameCustomer; 
        function __construct() {

            $actions = array(
                'ajax_product'
            );
            foreach($actions as $action) {
                add_action( 'wp_ajax_nopriv_'.$action, array($this, 'WTSales_'.$action) );
                add_action( 'wp_ajax_'.$action, array($this, 'WTSales_'.$action) );
            }
            
        }
        private function WTSales_names(){
            $names = explode(',', get_option('_wts_names_use'));

            $namesSelected = json_decode(stripslashes($_COOKIE['wts_names']));
            foreach($namesSelected as $name) {
                if(isset($names[$name])) {
                    unset($names[$name]);
                    break;
                }
            }
            $names = array_values($names);
            $res = array();
            shuffle($names);
            $numero = rand(1, 2);
            if($numero == 1) {
                $res['name'] = $names[0];
                $res['is_valid'] = true;
                return $res;
            }else{
                $res['name'] = get_option('_wts_name_default',__('Someone','wtsales'));
                $res['is_valid'] = false;
                return $res;
            }
        }
        private function WTSales_minutes(){
            $tiempos = array(__('minutes','wtsales'), __('seconds','wtsales'), __('hours','wtsales'), __('days','wtsales'));
            shuffle($tiempos);
            $numero = rand(2, 30);
            return $numero . ' ' . $tiempos[0];
        }
        private function WTSales_process() {

            
            $args = array(
                'post_type' => 'product',
                'orderby'   => 'rand',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => '_wts_product_priority',
                        'value' => "1",
                    )
                )
            );
            $post = get_posts( $args );
            if($post) {
                $orderSelected = $this->WTSales_get_order_itemmeta($post{0}->ID);

                if($orderSelected) {
                    $dataOrder = $this->WTSales_get_order($orderSelected['order_id']);
                    
                    $string = $this->WTSales_get_product($orderSelected['product_id'],$dataOrder['name'],$dataOrder['date']);
                    $res['html'] = $string;
                    $res['name'] = $dataOrder['name'];
                    $res['id_line'] = intval($orderSelected['id_line']);
                    return $res;
                }
            }
            

            $fakes = get_option('_wts_fake', '0');
            $dataOrder = $this->WTSales_get_order();
            $res = array();
            if($fakes == '0' and $dataOrder !== false) {
                $string = $this->WTSales_get_product($dataOrder['id'],$dataOrder['name'],$dataOrder['date']);
                $res['html'] = $string;
                $res['name'] = $dataOrder['name'];
                $res['id_line'] = intval($dataOrder['id_line']);
                return $res;
            }
            
            if($dataOrder) {
                $string = $this->WTSales_get_product($dataOrder['id'],$dataOrder['name'],$dataOrder['date']);
                $res['html'] = $string;
                $res['name'] = $dataOrder['name'];
                $res['id_line'] = intval($dataOrder['id_line']);
                return $res;
            }else if($fakes == '1') {
                
                if(!$post) {
                    $args = array(
                        'post_type' => 'product',
                        'orderby'   => 'rand',
                        'posts_per_page' => 1, 
                    );
                    $post = get_posts( $args );
                }
                $idProduct = $post{0}->ID;
                
                $string = $this->WTSales_get_product($idProduct);
    
                if(!$string) {
                    $string = $this->WTSales_get_product($dataOrder['id'],$dataOrder['name'],$dataOrder['date']);
                }
                $res['html'] = $string;
                $res['name'] = $dataOrder['name'];

                return $res;
            }
            
            return false;
        }
        
        private function WTSales_get_product($idProduct, $name = false, $date = false) {
            global $wp;
            $product = wc_get_product($idProduct);
            
            if($product) {
                $res= array(
                    'title' => $product->get_title(),
                    'description' => $product->get_description(),
                    'precio' =>  wc_get_price_including_tax($product),
                    'image' => wp_get_attachment_image_src( get_post_thumbnail_id( $idProduct ), 'medium' ),
                );
                if(!$res['image']) {
                    $imagen = wc_placeholder_img_src();
                }else{
                    $imagen = $res['image'][0];
                }
                $res['isOffer'] = $product->is_on_sale();
                $res['price_html'] = $product->get_price_html();
                
                if(!$name) {
                    $nombre = $this->WTSales_names();
                    $this->nameCustomer = null;
                    $customer_name = $nombre['name'];
                    if($nombre['is_valid']) {
                        $this->nameCustomer = $customer_name;
                    }
                    $date = $this->WTSales_minutes();
                }else{
                    $customer_name = $name;
                    $date = human_time_diff(strtotime($date));
                    $this->nameCustomer = null;
                }
                
                if(get_option('_wts_show_price') == '1') {
                    $price = '<div class="wts_price">'.$res['price_html'].'</div>';
                }else{ $price = ''; }
                $string = '<a href="'.get_permalink($product->get_id()).'?wts=true" rel="nofollow"><div class="wts_product">
                    <div class="wts_customer">'.sprintf(__('%s bought ago %s','wtsales'), $customer_name, $date).'</div>
                    <div class="wts_row">
                        <div class="col wts_image" style="background-image:url('.$imagen.');"></div>
                        <div class="col">
                            <div class="wts_title">'.$res['title'].'</div>
                            '.$price.'
                        </div>
                    </div>
                </div></a>';

                update_post_meta($product->get_id(),'_wts_shows',intval(get_post_meta($product->get_id(),'_wts_shows', true)) + 1);
                return $string;
            }else{
                return false;
            }

        }
        private function WTSales_get_order($orderID = false){
            
            if($orderID) {
                $order = wc_get_order( $orderID );
                $first_name = get_user_meta($order->get_customer_id(), 'first_name', true );
                $item_data = $this->WTSales_get_line_order($order);
                
                if(!$item_data) {
                    return false;
                }
                $res = array(
                    'id' => intval($item_data['product_id']),
                    'name' => $first_name,
                    'date' => $order->order_date,
                    'id_line' => intval($item_data['id'])
                );
                
                return $res;
            }
            
            $argsOrder = array(
                'limit' => 1,
                'orderby' => 'rand',
                'order' => 'DESC',
            );
            $query = new WC_Order_Query($argsOrder);
        
            $orders = $query->get_orders();
            if($orders) {
                $orderID = $orders[0];
                $order = wc_get_order( $orderID );
                $first_name = get_user_meta($order->get_customer_id(), 'first_name', true );
                $item_data = $this->WTSales_get_line_order($order);
                if(!$item_data) {
                    return false;
                }
                $res = array(
                    'id' => intval($item_data['product_id']),
                    'name' => $first_name,
                    'date' => $order->order_date,
                    'id_line' => intval($item_data['id'])
                );
                
                return $res;
            }else{
               return false;
            }
        }
        private function WTSales_get_line_order($order, $intents = 0){
            /**
             * Intents > 5 return false
             */
            if($intents > 5) {
                return false;
            }else{
                $intents++;
            }
            $item_data = array();
            foreach ( $order->get_items() as  $item_key => $item_values ) {
                $item_data[] = $item_values->get_data();
            }
            $totalLines = count($item_data);
            $rand = rand(0, $totalLines);
            if(intval($item_data[$rand]['product_id']) == 0) {
                return $this->WTSales_get_line_order($order, $intents);
            }

            $idSelected = json_decode(stripslashes($_COOKIE['wts_ids']));
            
            foreach($idSelected as $id) {
                if(intval($item_data[$rand]['id']) === intval($id)) {
                    return $this->WTSales_get_line_order($order, $intents);
                }
            }

            return $item_data[$rand];
        }

        private function WTSales_get_order_itemmeta($productID) {
            global $wpdb;
            $res = array();
            $result = $wpdb->get_var("SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '_product_id' and meta_value='$productID'");
            $idSelected = json_decode(stripslashes($_COOKIE['wts_ids']));
            if(in_array($result, $idSelected)) {
                return false;
            }
            $res['id_line'] = $result;
            $res['product_id'] = $productID;
            if($result) {
                $result = $wpdb->get_var("SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = '$result'");
                $res['order_id'] = $result;
                return $res;
            }
            return false;
        }
        function WTSales_ajax_product() {
            $res = $this->WTSales_process();
            wp_send_json(array(
                'html' => $res['html'],
                'id_line' => $res['id_line'],
                'names' => $this->nameCustomer
            ));
            wp_die();
        }
    }
}
$WTSales_API = new WTSales_API();
?>