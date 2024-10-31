<?php
if(!defined('ABSPATH')) { exit; }
global $post;
if(!$post) { exit; }
$totalShow = get_post_meta($post->ID,'_wts_shows', true);
$clics = get_post_meta($post->ID, '_wts_clics', true);
if(intval($totalShow) > 0) {
    $ctr = number_format(intval($clics) * 100 / intval($totalShow),2);
}else{
    $ctr = 0;
}
echo '<p>'.sprintf(__('Impresions %d','wtsales'), intval($totalShow)).'</p>';
echo '<p>'.sprintf(__('Clics: %d','wtsales'), intval($clics)).'</p>';
echo '<p>'.sprintf(__('CTR: %d','wtsales'), $ctr).'</p>';

echo '<label for="postexcerpt-hide"><input name="_wts_product_priority" type="checkbox" value="1" '.checked(get_post_meta($post->ID,'_wts_product_priority', true), "1", false).'><strong>'.__('Show this product priority','wtsales').'</strong></label>';
echo '<p class="description">'.__('This product will be used with priority, it will only be shown once per session','wtsales').'</p>';
?>