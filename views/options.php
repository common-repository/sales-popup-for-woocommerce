<?php
if(!defined('ABSPATH')) { exit; }

/**Acciones */
if(isset($_POST['action'])) {
    if ( wp_verify_nonce(  $_POST['save_option_nonce'], 'wts_nonce' ) ) {
        if($_POST['action'] == 'save_options') {
            update_option('_wts_timeout_limit',sanitize_text_field( $_POST['_wts_timeout_limit'] ));
            update_option('_wts_time_delay',sanitize_text_field( $_POST['_wts_time_delay'] ));
            update_option('_wts_position',sanitize_text_field( $_POST['_wts_position'] ));
            update_option('_wts_effect',sanitize_text_field( $_POST['_wts_effect'] ));
            update_option('_wts_effect_duration',sanitize_text_field( $_POST['_wts_effect_duration'] ));
            update_option('_wts_effect_delay',sanitize_text_field( $_POST['_wts_effect_delay'] ));
            update_option('_wts_names_use',sanitize_textarea_field( $_POST['_wts_names_use'] ));
            update_option('_wts_name_default',sanitize_textarea_field( $_POST['_wts_name_default'] ));
            update_option('_wts_exception_urls',sanitize_textarea_field( $_POST['_wts_exception_urls'] ));

            if(isset($_POST['_wts_fake'])) {
                update_option('_wts_fake','1');
            }else{
                update_option('_wts_fake','0');
            }
            if(isset($_POST['_wts_show_price'])) {
                update_option('_wts_show_price','1');
            }else{
                update_option('_wts_show_price','0');
            }
            if(isset($_POST['_wts_mobile'])) {
                update_option('_wts_mobile','1');
            }else{
                update_option('_wts_mobile','0');
            }
            if(isset($_POST['_wts_user_logged'])) {
                update_option('_wts_user_logged','1');
            }else{
                update_option('_wts_user_logged','0');
            }
        }
    }

    if ( isset($_POST['action']) && isset($_POST['add_sub_nonce']) && $_POST['action'] == 'adsub' && wp_verify_nonce(  $_POST['add_sub_nonce'], 'edw_nonce' ) ) {
        $sub = wp_remote_post( 'https://mailing.danielriera.net', [
            'method'      => 'POST',
            'timeout'     => 2000,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(),
            'body'        => array(
                'm' => $_POST['action'],
                'd' => base64_encode(json_encode($_POST))
            ),
            'cookies'     => array()
        ]);
        $result = json_decode($sub['body'],true);

        if($result['error']) {
            $class = 'notice notice-error';
            $message = __( 'An error has occurred, try again.', 'wtsales' );
            printf( '<div class="%s"><p>%s</p></div>', $class, $message );
        }else{
            $class = 'notice notice-success';
            $message = __( 'Welcome to newsletter :)', 'wtsales' );
            
            printf( '<div class="%s"><p>%s</p></div>', $class, $message );

            update_option('wts-newsletter' , '1');
        }
    }
    
}

$newsletterWTS = get_option('wts-newsletter', '0');
$user = wp_get_current_user();
?>
<style>
.wts_popup {
    position: fixed;
    width: 17%;
    height: 115px;
    top: 0;
    bottom: 0;
    right: 50px;
    margin: auto;
    background: #ffffff;
    z-index: 99999;
    border: 1px solid #ebebeb;
    border-radius: 50px;
    transition: all 0.5s ease;
    padding: 10px 40px;
    box-shadow: 0px 0px 20px 0px #d0d0d0;
}
form#new_subscriber {
    background: #FFF;
    padding: 10px;
    margin-bottom: 50px;
    border-radius: 12px;
    border: 1px solid #CCC;
    width: 23%;
    text-align: center;
}

form#new_subscriber input.email {
    width: 100%;
    text-align: center;
    padding: 10px;
}

form#new_subscriber input[type='submit'] {
    width: 100%;
    margin-top: 10px;
    border: 0;
    background: #3c853c;
    color: #FFF;
}
table th {
    min-width:350px
}
</style>

<div class="wrap wtspanel">
    <div class="animated wts_popup" id="demoEffect"></div>
    <h1><?=__('Sales Popup for Woocommerce', 'wtsales')?></h1>
    <div style="">
            <p><a href="https://www.paypal.com/donate/?hosted_button_id=EZ67DG78KMXWQ" target="_blank" style="text-decoration: none;
    font-size: 18px;
    border: 1px solid #333;
    padding: 10px;
    display: block;
    width: fit-content;
    border-radius: 10px;
    background: #FFF;">🍺 <?=__('You buy me a beer? Click here','wtsales')?> 🍺</a></p>
        </div>
        <?php
        if($newsletterWTS == '0') { ?>
            <form class="simple_form form form-vertical" id="new_subscriber" novalidate="novalidate" accept-charset="UTF-8" method="post">
                <input name="utf8" type="hidden" value="&#x2713;" />
                <input type="hidden" name="action" value="adsub" />
                <?php wp_nonce_field( 'edw_nonce', 'add_sub_nonce' ); ?>
                <h3><?=__('Do you want to receive the latest?','wtsales')?></h3>
                <p><?=__('Thank you very much for using our plugin, if you want to receive the latest news, offers, promotions, discounts, etc ... Sign up for our newsletter. :)', 'wtsales')?></p>
                <div class="form-group email required subscriber_email">
                    <label class="control-label email required" for="subscriber_email"><abbr title="<?=__('Required', 'wtsales')?>"> </abbr></label>
                    <input class="form-control string email required" type="email" name="e" id="subscriber_email" value="<?=$user->user_email?>" />
                </div>
                <input type="hidden" name="n" value="<?=bloginfo('name')?>" />
                <input type="hidden" name="w" value="<?=bloginfo('url')?>" />
                <input type="hidden" name="g" value="1,7" />
                <input type="text" name="anotheremail" id="anotheremail" style="position: absolute; left: -5000px" tabindex="-1" autocomplete="off" />
            <div class="submit-wrapper">
            <input type="submit" name="commit" value="<?=__('Submit', 'wtsales')?>" class="button" data-disable-with="<?=__('Processing', 'wtsales')?>" />
            </div>
        </form>
    <?php
        } //END Newsletter
    $tab = 'general';
    if($tab == 'general') {
        $currentPosition = get_option('_wts_position','bottom_left');
        $currentEffect = get_option('_wts_effect','fadeInUp');
        $currentDurationEffect =  get_option('_wts_effect_duration','');
        $currentDelayEffect =  get_option('_wts_effect_delay','delay-1s');
        ?>
        <form method="post">
            <input type="hidden" name="action" value="save_options" />
            <?php wp_nonce_field( 'wts_nonce', 'save_option_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?=__('Name of the ghost visitor', 'wtsales')?>
                        <p class="description"><?=__('There is a 50 / 100 chance of showing this name, so that the variety of names is higher instead of fake names','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="text" name="_wts_name_default" value="<?=get_option('_wts_name_default', __('Someone','wtsales'))?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Duration', 'wtsales')?>
                        <p class="description"><?=__('Time show popup in ms (miliseconds)','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="text" name="_wts_timeout_limit" value="<?=get_option('_wts_timeout_limit', '5000')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Delay', 'wtsales')?>
                        <p class="description"><?=__('Delay time to show popup in ms (miliseconds)','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="text" name="_wts_time_delay" value="<?=get_option('_wts_time_delay', '300')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Fake?', 'wtsales')?>
                        <p class="description"><?=__('Create customers name and time random, get random products','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" name="_wts_fake" value="1" <?=checked('1', get_option('_wts_fake', '0'))?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Fake Names', 'wtsales')?>
                        <p class="description"><?=__('If fake option is active','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <textarea rows="10" type="text" name="_wts_names_use"><?=get_option('_wts_names_use', '')?></textarea></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Show Price?', 'wtsales')?>
                        <p class="description"><?=__('Show price on popup','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" name="_wts_show_price" value="1" <?=checked('1', get_option('_wts_show_price', '0'))?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Position', 'wtsales')?></th>
                    <td>
                        <label>
                            <select name="_wts_position">
                                <option value="bottom_left" <?=selected('bottom_left',$currentPosition);?>><?=__('Bottom Left','wtsales')?></option>
                                <option value="bottom_right" <?=selected('bottom_right',$currentPosition);?>><?=__('Bottom Right','wtsales')?></option>
                                <option value="bottom_center" <?=selected('bottom_center',$currentPosition);?>><?=__('Bottom Center','wtsales')?></option>
                                <option value="top_left" <?=selected('top_left',$currentPosition);?>><?=__('Top Left','wtsales')?></option>
                                <option value="top_right" <?=selected('top_right',$currentPosition);?>><?=__('Top Right','wtsales')?></option>
                                <option value="top_center" <?=selected('top_center',$currentPosition);?>><?=__('Top Center','wtsales')?></option>
                            </select>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Effect', 'wtsales')?></th>
                    <td>
                        <label>
                            <select name="_wts_effect">
                                <optgroup label="<?=__('Attention Seekers','wtsales')?>">
                                    <option value="bounce" <?=selected('bounce',$currentEffect);?> >bounce</option>
                                    <option value="flash" <?=selected('flash',$currentEffect);?> >flash</option>
                                    <option value="pulse" <?=selected('pulse',$currentEffect);?> >pulse</option>
                                    <option value="rubberBand" <?=selected('rubberBand',$currentEffect);?> >rubberBand</option>
                                    <option value="shake" <?=selected('shake',$currentEffect);?> >shake</option>
                                    <option value="swing" <?=selected('swing',$currentEffect);?> >swing</option>
                                    <option value="tada" <?=selected('tada',$currentEffect);?> >tada</option>
                                    <option value="wobble"  <?=selected('wobble',$currentEffect);?> >wobble</option>
                                    <option value="jello"  <?=selected('jello',$currentEffect);?> >jello</option>
                                    <option value="heartBeat"  <?=selected('heartBeat',$currentEffect);?> >heartBeat</option>
                                </optgroup>

                                <optgroup label="<?=__('Bouncing Entrances','wtsales')?>">
                                    <option value="bounceIn"  <?=selected('bounceIn',$currentEffect);?> >bounceIn</option>
                                    <option value="bounceInDown"  <?=selected('bounceInDown',$currentEffect);?> >bounceInDown</option>
                                    <option value="bounceInLeft"  <?=selected('bounceInLeft',$currentEffect);?> >bounceInLeft</option>
                                    <option value="bounceInRight"  <?=selected('bounceInRight',$currentEffect);?> >bounceInRight</option>
                                    <option value="bounceInUp"  <?=selected('bounceInUp',$currentEffect);?> >bounceInUp</option>
                                </optgroup>

                                <optgroup label="<?=__('Fading Entrances','wtsales')?>">
                                    <option value="fadeIn"  <?=selected('fadeIn',$currentEffect);?> >fadeIn</option>
                                    <option value="fadeInDown"  <?=selected('fadeInDown',$currentEffect);?> >fadeInDown</option>
                                    <option value="fadeInDownBig"  <?=selected('fadeInDownBig',$currentEffect);?> >fadeInDownBig</option>
                                    <option value="fadeInLeft"  <?=selected('fadeInLeft',$currentEffect);?> >fadeInLeft</option>
                                    <option value="fadeInLeftBig"  <?=selected('fadeInLeftBig',$currentEffect);?> >fadeInLeftBig</option>
                                    <option value="fadeInRight"  <?=selected('fadeInRight',$currentEffect);?> >fadeInRight</option>
                                    <option value="fadeInRightBig"  <?=selected('fadeInRightBig',$currentEffect);?> >fadeInRightBig</option>
                                    <option value="fadeInUp"  <?=selected('fadeInUp',$currentEffect);?> >fadeInUp</option>
                                    <option value="fadeInUpBig"  <?=selected('fadeInUpBig',$currentEffect);?> >fadeInUpBig</option>
                                </optgroup>

                                <optgroup label="<?=__('Flippers','wtsales')?>">
                                    <option value="flip"  <?=selected('flip',$currentEffect);?> >flip</option>
                                    <option value="flipInX"  <?=selected('flipInX',$currentEffect);?> >flipInX</option>
                                    <option value="flipInY"  <?=selected('flipInY',$currentEffect);?> >flipInY</option>
                                </optgroup>

                                <optgroup label="<?=__('Lightspeed','wtsales')?>">
                                    <option value="lightSpeedIn"  <?=selected('lightSpeedIn',$currentEffect);?> >lightSpeedIn</option>
                                </optgroup>

                                <optgroup label="<?=__('Rotating Entrances','wtsales')?>">
                                    <option value="rotateIn"  <?=selected('rotateIn',$currentEffect);?> >rotateIn</option>
                                    <option value="rotateInDownLeft"  <?=selected('rotateInDownLeft',$currentEffect);?> >rotateInDownLeft</option>
                                    <option value="rotateInDownRight"  <?=selected('rotateInDownRight',$currentEffect);?> >rotateInDownRight</option>
                                    <option value="rotateInUpLeft"  <?=selected('rotateInUpLeft',$currentEffect);?> >rotateInUpLeft</option>
                                    <option value="rotateInUpRight"  <?=selected('rotateInUpRight',$currentEffect);?> >rotateInUpRight</option>
                                </optgroup>

                                <optgroup label="<?=__('Sliding Entrances','wtsales')?>">
                                    <option value="slideInUp"  <?=selected('slideInUp',$currentEffect);?> >slideInUp</option>
                                    <option value="slideInDown"  <?=selected('slideInDown',$currentEffect);?> >slideInDown</option>
                                    <option value="slideInLeft"  <?=selected('slideInLeft',$currentEffect);?> >slideInLeft</option>
                                    <option value="slideInRight"  <?=selected('slideInRight',$currentEffect);?> >slideInRight</option>
                                </optgroup>
                                    
                                <optgroup label="<?=__('Zoom Entrances','wtsales')?>">
                                    <option value="zoomIn"  <?=selected('zoomIn',$currentEffect);?> >zoomIn</option>
                                    <option value="zoomInDown"  <?=selected('zoomInDown',$currentEffect);?> >zoomInDown</option>
                                    <option value="zoomInLeft"  <?=selected('zoomInLeft',$currentEffect);?> >zoomInLeft</option>
                                    <option value="zoomInRight"  <?=selected('zoomInRight',$currentEffect);?> >zoomInRight</option>
                                    <option value="zoomInUp"  <?=selected('zoomInUp',$currentEffect);?> >zoomInUp</option>
                                </optgroup>

                                <optgroup label="<?=__('Specials','wtsales')?>">
                                    <option value="jackInTheBox"  <?=selected('jackInTheBox',$currentEffect);?> >jackInTheBox</option>
                                    <option value="rollIn"  <?=selected('rollIn',$currentEffect);?> >rollIn</option>
                                </optgroup>
                            </select>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Duration Effect', 'wtsales')?>
                        <p class="description"><?=__('Time Duration Effect','wtsales')?></p>
                    </th>
                    <td>
                        <select name="_wts_effect_duration">
                            <option value="">1s</option>
                            <option value="slow" <?=selected('slow',$currentDurationEffect);?>>2s</option>
                            <option value="slower" <?=selected('slower',$currentDurationEffect);?>>3s</option>
                            <option value="fast" <?=selected('fast',$currentDurationEffect);?>>800ms</option>
                            <option value="faster" <?=selected('faster',$currentDurationEffect);?>>500ms</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Delay Effect', 'wtsales')?>
                        <p class="description"><?=__('Time Delay Effect','wtsales')?></p>
                    </th>
                    <td>
                        <select name="_wts_effect_delay">
                            <option value="delay-1s" <?=selected('delay-1s',$currentDelayEffect);?>>1s</option>
                            <option value="delay-2s" <?=selected('delay-2s',$currentDelayEffect);?>>2s</option>
                            <option value="delay-3s" <?=selected('delay-3s',$currentDelayEffect);?>>3s</option>
                            <option value="delay-4s" <?=selected('delay-4s',$currentDelayEffect);?>>4s</option>
                            <option value="delay-5s" <?=selected('delay-5s',$currentDelayEffect);?>>5s</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Disable on mobile', 'wtsales')?>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" name="_wts_mobile" value="1" <?=checked('1', get_option('_wts_mobile', '0'))?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Hide for logged user', 'wtsales')?>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" name="_wts_user_logged" value="1" <?=checked('1', get_option('_wts_user_logged', '0'))?>" /></label>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?=__('URL where not to show', 'wtsales')?>
                        <p class="description"><?=__('One per line','wtsales')?></p>
                    </th>
                    <td>
                        <label>
                        <textarea rows="10" type="text" name="_wts_exception_urls"><?=get_option('_wts_exception_urls', '')?></textarea></label>
                    </td>
                </tr>
            </table>
            <input type="submit" class="button" value="<?=__('Save','wtsales')?>" />
        </form>
        <script>
            currentEffect = '<?=$currentEffect?>';
            currentDelayEffect = '<?=$currentDelayEffect?>';
            currentDurationEffect = '<?=$currentDurationEffect?>';
            jQuery(document).ready(function($) {
                function wts_demo_effect(){
                    $("#demoEffect").removeAttr('class');
                    $("#demoEffect").attr('class', 'animated wts_popup');
                    $('#demoEffect')[0].className = 'animated wts_popup';
                    setTimeout(() => {
                        $("#demoEffect").addClass(currentEffect+' '+currentDelayEffect+' '+currentDurationEffect);
                    }, 100);
                    
                }
                $(document).on("change", "select[name='_wts_effect']", function(){
                    currentEffect = $(this).val();
                    wts_demo_effect();
                });
                $(document).on("change", "select[name='_wts_effect_duration']", function(){
                    currentDurationEffect = $(this).val();
                    wts_demo_effect()
                });
                $(document).on("change", "select[name='_wts_effect_delay']", function(){
                    currentDelayEffect = $(this).val();
                    wts_demo_effect();
                });
            });
        </script>
        <h2><?=__('Need style?', 'wtsales')?></h2>
        <p><?=__('Enjoy! Paste this CSS code into your Customizer and edit as you like','wtsales')?></p>
<pre>
.wts_popup {} //<?=__('Notification style','wtsales');?><br>
.wts_customer {} //<?=__('Customer name style','wtsales');?><br>
.wts_title {} //<?=__('Product title style','wtsales');?><br>
.wts_image {} //<?=__('Product image style','wtsales');?><br>

</pre>
    <?php
    }
    
    ?>

</div>