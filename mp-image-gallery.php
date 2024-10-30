<?php
/*
Plugin Name: MarketPress Product Image Gallery
Version: 0.3
Plugin URI: https://premium.wpmudev.org/project/e-commerce/
Description: Creates a nice image gallery for product single page.
Author: WPMU DEV (Ash)
Author URI: http://premium.wpmudev.org/
Text Domain: mp_gallery

Copyright 2009-2014 Incsub (http://incsub.com)
Author - WPMU DEV

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA	 02111-1307	 USA
*/


function this_plugin_last() {
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
        array_splice($active_plugins, $this_plugin_key, 1);
        array_push($active_plugins, $this_plugin);
        update_option('active_plugins', $active_plugins);
}
add_action("activated_plugin", "this_plugin_last");


    
    class MP_Image_Gallery  {
        
        public $domain;
        public $mp;
        public $options;
        public $zoom_view_width;
        public $zoom_view_height;
        
        public function __construct() {
            $this->domain = 'mp_gallery';
            $this->mp = $GLOBALS['mp'];
            $this->options = get_option( 'load_option' ); $this->options = $this->options == '' ? 'default' : $this->options;
            $this->zoom_view_width = get_option( 'zoom_view_width' ); $this->zoom_view_width = $this->zoom_view_width == '' ? 300 : $this->zoom_view_width;
            $this->zoom_view_height = get_option( 'zoom_view_height' ); $this->zoom_view_height = $this->zoom_view_height == '' ? 300 : $this->zoom_view_height;
            
            add_image_size( 'mp-large-view', 600, 880, true );
            add_image_size( 'mp-thumb', 220, 180, array( 'center', 'center' ) );
            
            add_action( 'admin_menu', array( $this, 'register_mp_gallery_submenu_page' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'mp_image_gallery_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'mp_image_gallery_scripts' ) );
            add_action( 'add_meta_boxes', array( $this, 'mp_image_gallery_meta_box' ) );
            add_action( 'save_post', array( $this, 'save_mp_image_gallery_meta_box' ), 1, 2 );
            if( $this->options == 'default' )
                add_filter( 'the_content', array( $this, 'mp_gallery_apply' ) );
            elseif( $this->options == 'shortcode' )
                add_shortcode( 'mp_product_gallery', array( $this, 'mp_product_gallery' ) );
        }
        
        public function register_mp_gallery_submenu_page() {
            add_options_page( __( 'MP Gallery Settings', $this->domain ), __( 'MP Gallery Settings', $this->domain ), 'manage_options', 'mp-gallery-settings', array($this, 'mp_gallery_settings_cb') );
        }
        
        public function mp_gallery_settings_cb() {
            if( isset($_POST['save_mp_settings'] ) ) {
                if ( !check_admin_referer( 'mp_gallery_settings_noncename', 'mp_gallery_settings_noncename' )){
                    _e( 'You are not allowed to save!', $this->domain );
                    return;
                }
                update_option( 'load_option', $_POST['load_option'] );
                update_option( 'zoom_view_width', $_POST['mp_width'] );
                update_option( 'zoom_view_height', $_POST['mp_height'] );
                wp_safe_redirect( admin_url( 'options-general.php?page=mp-gallery-settings' ) );
                exit();
            }
            ?>
            <div class="wrap">
                <form action="<?php echo admin_url( 'options-general.php?page=mp-gallery-settings' ) ?>&&noheader=true" method="post">
                    <?php wp_nonce_field('mp_gallery_settings_noncename','mp_gallery_settings_noncename'); ?>
                    <h2><?php _e( 'MP Gallery Settings', $this->domain ) ?></h2>
                    <table cellpadding="10" cellspacing="10">
                        <tr>
                            <td valign="top"><?php _e( 'How do you want to load the gallery?', $this->domain ) ?></td>
                            <td>
                                <input type="radio" <?php echo ($this->options == '' || $this->options == 'default') ? 'checked' : '' ?> name="load_option" value="default"> <?php _e( 'Default' ); ?><br>
                                <input type="radio" <?php echo ($this->options == 'shortcode') ? 'checked' : '' ?> name="load_option" value="shortcode"> <?php _e( 'Using Shortcode' ); ?><br>
                                <input type="radio" <?php echo ($this->options == 'template') ? 'checked' : '' ?> name="load_option" value="template"> <?php _e( 'Template Function' ); ?><br>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top"><?php _e( 'Zoom viewport size:', $this->domain ) ?></td>
                            <td>
                                <?php _e('Width', $this->domain) ?> <input type="text" value="<?php echo $this->zoom_view_width ?>" name="mp_width"><br>
                                <?php _e('Height', $this->domain) ?> <input type="text" value="<?php echo $this->zoom_view_height ?>" name="mp_height"><br>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <input type="submit" name="save_mp_settings" class="button button-primary button-large" value="<?php _e( 'Save Options', $this->domain ) ?>">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }
        
        public function mp_image_gallery_scripts() {
                wp_enqueue_style( 'style-mp-image-gallery', plugins_url( '/css/mp-image-gallery.css' , __FILE__ ) );
                wp_enqueue_style( 'style-multizoom', plugins_url( '/css/multizoom.css' , __FILE__ ) );
                wp_enqueue_script( 'script-zoom', plugins_url( '/js/multizoom.js' , __FILE__ ), array( 'jquery' ), '1.0.0', true );
                wp_localize_script( 'script-zoom', 'data', array( 'loading' => plugins_url( '/img/loader.gif' , __FILE__ ) ) );
                wp_enqueue_script( 'script-mp-image-gallery', plugins_url( '/js/mp-image-gallery.js' , __FILE__ ), array( 'jquery' ), '1.0.0', true );
                wp_localize_script( 'script-mp-image-gallery', 'data', array(
                                                                            'title' => __( 'Title', $this->domain ),
                                                                            'prod_title' => __( 'Product Title', $this->domain ),
                                                                            'upload' => __( 'Upload an image', $this->domain ),
                                                                            'path' => __( 'Path to image', $this->domain ),
                                                                            'confirm_msg' => __( 'Are you sure you want to remove this image?', $this->domain ),
                                                                            'zoom_view_width' => $this->zoom_view_width,
                                                                            'zoom_view_height' => $this->zoom_view_height
                                                                             ) );
        }
        
        
        
        public function mp_image_gallery_meta_box() {
            add_meta_box( 'mp_product_gallery', __('MarketPress Product Gallery', $this->domain), array( $this, 'mp_image_gallery_meta_box_cb' ), 'product', 'advanced', 'high' );
        }
        
        public function mp_image_gallery_meta_box_cb() {
            global $post;
            $mp_prod_image_title = explode( ',', get_post_meta( $post->ID, 'mp_prod_image_title', true ) );
            $mp_prod_image_paths = explode( ',', get_post_meta( $post->ID, 'mp_prod_image_path', true ) );
            $mp_prod_img_id = explode( ',', get_post_meta( $post->ID, 'mp_prod_img_id', true ) );
            
            // Noncename needed to verify where the data originated
            echo '<input type="hidden" name="mp_gallery_noncename" id="mp_gallery_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
            ?>
            <table cellpadding="5" cellspacing="5" width="100%" class="mp-gallery-meta-table">
                <?php if( count($mp_prod_image_paths) < 1 ) { ?>
                <tr>
                    <td width="5%"><?php _e( 'Title', $this->domain ) ?></td>
                    <td width="20%"><input type="text" name="mp_prod_image_title[]" placeholder="<?php _e( 'Product Title', $this->domain ) ?>"></td>
                    <td width="10%"><input class="mp-image-upload-btn" type="button" value="<?php _e( 'Upload an image', $this->domain ) ?>"></td>
                    <td width="40%"><input class="mp-image-upload-path" style="width: 100%" type="text" name="mp_prod_image_path[]" placeholder="<?php _e( 'Path to image', $this->domain ); ?>"></td>
                    <td width="10%"><span class="mp-more-icon plus">[ + ]</span></td>
                    <td width="15%">&nbsp;<input type="hidden" name="mp_prod_img_id[]"></td>
                </tr>
                <?php }else{ $i = 0; ?>
                    <?php foreach( $mp_prod_image_paths as $mp_prod_image_path ) { ?>
                        <tr>
                            <td width="5%"><?php _e( 'Title', $this->domain ) ?></td>
                            <td width="20%"><input type="text" name="mp_prod_image_title[]" value="<?php echo $mp_prod_image_title[$i] ?>" placeholder="<?php _e( 'Product Title', $this->domain ) ?>"></td>
                            <td width="10%"><input class="mp-image-upload-btn" type="button" value="<?php _e( 'Upload an image', $this->domain ) ?>"></td>
                            <td width="40%"><input class="mp-image-upload-path" style="width: 100%" type="text" name="mp_prod_image_path[]" value="<?php echo $mp_prod_image_path; ?>" placeholder="<?php _e( 'Path to image', $this->domain ); ?>"></td>
                            <td width="10%"><span class="mp-more-icon <?php echo ( $i > 0 ) ? 'minus' : 'plus' ?>"><?php echo ( $i > 0 ) ? '[ - ]' : '[ + ]' ?></span></td>
                            <td width="15"><img width="50" src="<?php echo $mp_prod_image_path; ?>"><input type="hidden" name="mp_prod_img_id[]" value="<?php echo $mp_prod_img_id[$i++] ?>"></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>
            <?php
        }
        
        public function save_mp_image_gallery_meta_box( $post_id, $post ) {
            global $post;
            if (isset($_POST['mp_gallery_noncename']) && !wp_verify_nonce($_POST['mp_gallery_noncename'], plugin_basename(__FILE__))) {
                return $post->ID;
            }
            if ($post->post_type == 'revision') return;
            
            $mp_prod_image_titles = $_POST['mp_prod_image_title'];
            $mp_prod_img_id = $_POST['mp_prod_img_id'];
            $mp_prod_image_paths = array_filter( $_POST['mp_prod_image_path'], 'strlen' );
            
            if( empty( $mp_prod_image_paths ) ){
                delete_post_meta( $post->ID, 'mp_prod_image_path' );
            }else {
                update_post_meta($post->ID, 'mp_prod_image_path', implode( ',', $mp_prod_image_paths ));
                update_post_meta($post->ID, 'mp_prod_image_title', implode( ',', $mp_prod_image_titles ));
                update_post_meta($post->ID, 'mp_prod_img_id', implode( ',', $mp_prod_img_id ));
            }
        }
        
        public function mp_gallery_apply( $content ) {
            
            global $post;
            if( 'product' == $post->post_type ) {
                $mp_prod_image_title = explode( ',', get_post_meta( $post->ID, 'mp_prod_image_title', true ) );
                $mp_prod_image_paths = explode( ',', get_post_meta( $post->ID, 'mp_prod_image_path', true ) );
                $mp_prod_img_id = explode( ',', get_post_meta( $post->ID, 'mp_prod_img_id', true ) );
                include plugin_dir_path( __FILE__ ) . 'includes/gallery.php';
                return $html . $content;
            }
            
            return $content;
            
        }
        
        public function mp_product_gallery( $atts ) {
            global $post;
            $atts = shortcode_atts( array(
                    'id' => $post->ID
            ), $atts );
            
            $mp_prod_image_title = explode( ',', get_post_meta( $atts['id'], 'mp_prod_image_title', true ) );
            $mp_prod_image_paths = explode( ',', get_post_meta( $atts['id'], 'mp_prod_image_path', true ) );
            $mp_prod_img_id = explode( ',', get_post_meta( $atts['id'], 'mp_prod_img_id', true ) );
            include plugin_dir_path( __FILE__ ) . 'includes/gallery.php';
            return $html;
        }
    
    }
    
    $mp_image = new MP_Image_Gallery();
    
    
    function mp_template_gallery($id = '') {
        global $mp_image;
        if( $mp_image->options == 'template' ) {
            global $post;
            $id = $id ? $id : $post->ID;
            $mp_prod_image_title = explode( ',', get_post_meta( $id, 'mp_prod_image_title', true ) );
            $mp_prod_image_paths = explode( ',', get_post_meta( $id, 'mp_prod_image_path', true ) );
            $mp_prod_img_id = explode( ',', get_post_meta( $id, 'mp_prod_img_id', true ) );
            include plugin_dir_path( __FILE__ ) . 'includes/gallery.php';
            return $html;
        }
    }
    
