<?php
if(!defined('ABSPATH')) { exit; }
$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_wts_product_priority',
            'value' => "1",
            )
            )
        );
        $posts = get_posts( $args );
        if(!$posts) {
            echo __('No priorized products','wtsales');
            return;
        }
        ?>
<h4><?=__('Your priority products','wtsales')?></h4>
<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>

            <th><?=__('Product Name', 'wtsales')?></th>
            <th><?=__('Impresions', 'wtsales')?></th>
            <th><?=__('Clics', 'wtsales')?></th>
            <th><?=__('%CTR', 'wtsales')?></th>

    </tr>
    </thead>

    <tbody>
        <?php
            foreach($posts as $post) {
                $totalShow = get_post_meta($post->ID, '_wts_shows', true);
                $clics = get_post_meta($post->ID, '_wts_clics', true) ?: 0;
                echo '<tr class="alternate">
                <th><a href="'.admin_url('post.php?post='.$post->ID.'&action=edit').'">'.$post->post_title.'</a></th>
                <td>'.$totalShow.'</td>
                <td>'.$clics.'</td>
                <td>'.number_format(intval($clics) * 100 / intval($totalShow),2).'%</td>
            </tr>';
            }
        ?>
    </tbody>
</table>