<?php

    /**
    * Plugin Name: Simple Slides
    * Version: 1.4.1
    * Description: Create slideshows that uses the world's most awesome slider, the <a href="http://nivo.dev7studios.com/">Nivo Slider</a>. Uses shortcodes with a number of attributes to allow customized embedding in widgets, posts, pages, or theme files. Supports the following browsers: Internet Explorer v7+, Firefox v3+, Google Chrome v4+, Safari v4+, and Opera v10+. <strong>Please don't hesitate to contact us for questions, suggestions, etc... anything.</strong>
    * Author: ApocalypseBoy
    * Author URI: http://apocalypseboy.com/
    * Plugin URI: http://apocalypseboy.com/simple-slides/
    * License: GPLv2 or later
    */

    /*
        This program is free software; you can redistribute it and/or
        modify it under the terms of the GNU General Public License
        as published by the Free Software Foundation; either version 2
        of the License, or (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
    */
    
    define( 'ABY_SS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    define( 'ABY_SS_PLUGIN_FOLDER', array_shift( explode( '/',  ABY_SS_PLUGIN_BASENAME ) ) ); 
    define( 'ABY_SS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . ABY_SS_PLUGIN_FOLDER );
    define( 'ABY_SS_PLUGIN_URL', WP_PLUGIN_URL . '/' . ABY_SS_PLUGIN_FOLDER );
    
    require_once( ABY_SS_PLUGIN_DIR . '/core/types.php' );
    require_once( ABY_SS_PLUGIN_DIR . '/core/listtable.php' );
    require_once( ABY_SS_PLUGIN_DIR . '/core/edit.php' );
    
    class ABY_SimpleSlides {  
        var $settings_name = 'simple_slides_settings';
         
        var $global_settings = array(
            'script' => array(
                'script_function' => 'nivoSlider',
                'script_url' => array( 'includes/nivo/jquery.nivo.js' ),
                'style_url' => array(                
                    'includes/nivo/themes/default/default.css', 
                    'includes/nivo/themes/bar/bar.css', 
                    'includes/nivo/themes/dark/dark.css', 
                    'includes/nivo/themes/light/light.css',
                    'includes/nivo/themes/clean/clean.css',
                    'includes/nivo/style.css', 
                ),          
                'script_settings_nojs' => array(
                    'theme'         => 'default',
                    'height'        => 'auto',
                    'width'         => 'auto',
                    'image_size'    => null
                ),
                'script_settings' => array(
                    'effect'            => 'fade',
                    'slices'            => 13,
                    'boxCols'           => 7,
                    'boxRows'           => 5,
                    'animSpeed'         => 500,
                    'pauseTime'         => 3000,
                    'directionNav'      => 1,
                    'controlNav'        => 1,
                    'pauseOnHover'      => 1,
                    'prevText'          => 'Prev',
                    'nextText'          => 'Next',
                    'randomStart'       => 0,
                    'startSlide'        => 0
                ),
                'script_settings_map' => array(
                    'effect'            => 'effect',
                    'slices'            => 'slices',
                    'boxCols'           => 'box_cols',
                    'boxRows'           => 'box_rows',
                    'animSpeed'         => 'anim_speed',
                    'pauseTime'         => 'pause_time',
                    'directionNav'      => 'direction_nav',
                    'controlNav'        => 'control_nav',
                    'pauseOnHover'      => 'pause_on_hover',
                    'prevText'          => '0',
                    'nextText'          => 'next_text',
                    'randomStart'       => 'random_start',
                    'startSlide'        => 'start_slide',
                )
            )
        );
        
        public function __construct() {                                
            add_action( 'init', array( $this, 'init' ) );       
            add_action( 'admin_init', array( $this, 'admin_init' ) );  
            add_action( 'admin_menu', array( $this, 'add_menu' ) ) ;   
            add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) ); 
            add_image_size( 'ss_preview', 175, 100, false );
        }      
        
        public function init() {                              
            add_shortcode( 'simple_slides', array( $this, 'get' ) );
            
            foreach ( $this->global_settings['script']['style_url'] as $number => $url ) {   
                wp_enqueue_style( 'ss_style_' . $number, ABY_SS_PLUGIN_URL . '/' . $url );
            }
            
            foreach ( $this->global_settings['script']['script_url'] as $number => $url ) {
                wp_enqueue_script( 'ss_script_' . $number, ABY_SS_PLUGIN_URL . '/' . $url, array( 'jquery' ), null, true );     
            }
           
            wp_enqueue_script( 'ss_main', ABY_SS_PLUGIN_URL . '/scripts/ss_main.js', array( 'jquery' ), null, true );
        }  
        
        public function admin_init() {
            register_setting( $this->settings_name, $this->settings_name, array( $this, 'clean_settings' ) ); 
        }      
        
        public function add_theme_support() {
            $supported_types = get_theme_support( 'post-thumbnails' );
            
            if ( false === $supported_types )
                add_theme_support( 'post-thumbnails', array( 'ss-slide' ) );
                
            elseif ( is_array( $supported_types ) ) {
                $supported_types[0][] = 'ss-slide';
                add_theme_support( 'post-thumbnails', $supported_types[0] );
            }
        }   
                                                                                                               
        public function get( $atts ) {
            static $count = 0; $count ++;
            $settings = $this->get_settings();
            $script_settings = $settings['script'];
            
            extract( shortcode_atts( 
                array( 
                    'name'          => $count,
                    'theme'         => $script_settings['script_settings_nojs']['theme'],
                    'height'        => $script_settings['script_settings_nojs']['height'],
                    'width'         => $script_settings['script_settings_nojs']['width'],
                    'image_size'    => $script_settings['script_settings_nojs']['image_size'],
                    'wrapper'       => 'div',
                    'container'     => 'div',
                    'caption'       => 'div',  
                    'tax'           => null,
                    'set'           => null
                ),
                $atts
            ) );
            
            $slides = new WP_Query( array(
                'posts_per_page'    => -1,
                'post_type'         => 'ss-slide',
                'post_status'       => 'publish',
                'order'             => 'ASC',
                'orderby'           => 'menu_order date',
                'ss-slide-set'      => $set ? $set : $tax
            ) );                                    
            
            $local_settings = shortcode_atts( 
                array_combine( 
                    array_values( $script_settings['script_settings_map'] ), 
                    array_values( $script_settings['script_settings'] ) 
                ), 
                $atts 
            );
            
            $local_settings = array_combine( 
                array_keys( $script_settings['script_settings_map'] ), 
                array_values( $local_settings ) 
            ); 
            
            $html = '';
            
            $local_settings['f'] = $script_settings['script_function'];    
            
            $script_str = '';
            
            foreach ( $local_settings as $f => $v ) {
                if ( is_numeric( $v ) ) 
                    $script_str .= sprintf( '%s:%s,', $f, $v );
                
                else 
                    $script_str .= sprintf( '%s:"%s",', $f, $v );
            }
                
            $html .= sprintf( '<script type="text/javascript">var ss_%s={%s};</script>', $name, trim( $script_str, ',' ) ) ;
            $html .= sprintf( '<%s class="slider-wrapper simple_slides_wrapper simple_slides_wrapper-%s theme-%s" style="height: %s; width: %s;">', $wrapper, $name, $theme, $height, $width );
            $html .= sprintf( '<%s id="ss_%s" class="simple_slides simple_slides-%s nivoSlider nivoSlider-%s">', $container, $name, $name, $name, $height, $width );
            
            $captions = array();
            
            while ( $slides->have_posts() ) {
                $slides->the_post();                         
                
                /**
                * Only slides with thumbnails will be processed.
                */
                
                if ( has_post_thumbnail() ) {
                    
                    /**
                    * Get captions 
                    */          
                    
                    $use_caption = get_post_meta( get_the_ID(), 'ss-slide-use-caption', true );
                    $use_caption_title = get_post_meta( get_the_ID(), 'ss-slide-use-caption-title', true );
                    
                    if ( $use_caption ) {
                        $caption_id = sprintf( 'simple_slides_caption_%s_%s', $name, get_the_ID() );
                        $captions[$caption_id] = array( 'content' => get_the_content() );
                        
                        if ( $use_caption_title )
                            $captions[$caption_id]['title'] = get_the_title();
                    }
                    
                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id(), $image_size );
                    
                    $link = get_post_meta( get_the_ID(), 'ss-slide-link', true );         
                    $link_new_window = get_post_meta( get_the_ID(), 'ss-slide-link-new-window', true );
                    
                    if ( $link )
                        $html .= sprintf( '<a href="%s" %s>', $link,  '1' == $link_new_window ? '  target="_blank"' : '' );

                    $html .= sprintf( '<img src="%s" %s />', $thumb[0], $use_caption ? sprintf( 'title="#%s"', $caption_id ) : '' );     
                    
                    if ( $link )
                        $html .= '</a>';
                }
            }                                                    
            
            $html .= sprintf( '</%s>', $container ) ;
            
            foreach ( $captions as $caption_id => $caption_arr ) {
                $html .= sprintf( '<%s id="%s" class="nivo-html-caption">', $caption, $caption_id );
                
                if ( isset( $caption_arr['title'] ) )
                    $html .= sprintf( '<div class="simple_slides_title">%s</div>', $caption_arr['title'] );
                    
                $html .= sprintf( '<div class="simple_slides_content">%s</div>', $caption_arr['content'] );
                
                $html .= sprintf( '</%s>', $caption );
            }   
            
            $html .= sprintf( '</%s>', $wrapper );
            
            wp_reset_postdata();
                
            return $html;
        }   

        // todo: Add data validation for different types of data.
        public function clean_settings( $data ) {  
            $data = $this->clean_settngs_helper( $data );
            return $data;
        }   
        
        public function clean_settngs_helper( $data ) {
            if ( is_array( $data ) ) {
                foreach ( $data as $f => $v ) {
                    $data[$f] = $this->clean_settngs_helper( $v );
                }     
                
                return $data;
            }      
                
            elseif ( is_string( $data ) ) {
                return trim( $data );                    
            }
            
            else {
                return $data;
            }
        } 
        
        public function add_menu() {
            $hk = add_submenu_page( 
                'edit.php?post_type=ss-slide', 
                'Default Settings &lsaquo; Simple Slides', 
                'Default Settings', 
                'manage_options', 
                'ss-settings', 
                array( $this, 'page_settings' ) 
            );
            
            add_action( 'load-' . $hk, array( $this, 'page_settings_load' ) );                        
        }   
        
        public function page_settings_load() {
            wp_enqueue_style( 'ss-settings', ABY_SS_PLUGIN_URL . '/styles/settings.css' );
        } 
        
        public function page_settings() { ?>
            <div class="wrap">
                <div class="icon32" id="icon-options-general"></div>
                <h2>Default Settings</h2>       
                <?php if ( 'true' == $_GET['settings-updated'] ) : ?>   
                    <div id="message" class="updated below-h2">
                        <p>Simple Slides default settings has been succesfully updated.</p>
                    </div>                                             
                <?php endif; ?>    
                <p>
                    Use <strong>shortcode attributes</strong> to override these default settings. Shortcode attribute names are indicated below each setting label.
                </p>
                <p>
                    For more information, please refer to the <a target="_blank" href="http://apocalypseboy.com/simple-slides/">plugin documentation</a>.
                </p>
                <form action="options.php" method="post">
                    <?php 
                        $settings = $this->get_settings();
                        echo settings_fields( $this->settings_name ); 
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Theme</label><br />
                                <span class="description">[theme]</span>
                            </th>
                            <td>
                                <select name="<?php echo $this->settings_name; ?>[script][script_settings_nojs][theme]">
                                    <option value="default" <?php __checked_selected_helper( 'default', $settings['script']['script_settings_nojs']['theme'], true, 'selected' ); ?>>Default</option>
                                    <option value="clean" <?php __checked_selected_helper( 'clean', $settings['script']['script_settings_nojs']['theme'], true, 'selected' ); ?>>Clean</option>
                                    <option value="bar" <?php __checked_selected_helper( 'bar', $settings['script']['script_settings_nojs']['theme'], true, 'selected' ); ?>>Bar</option>
                                    <option value="dark" <?php __checked_selected_helper( 'dark', $settings['script']['script_settings_nojs']['theme'], true, 'selected' ); ?>>Dark</option>
                                    <option value="light" <?php __checked_selected_helper( 'light', $settings['script']['script_settings_nojs']['theme'], true, 'selected' ); ?>>Light</option>
                                </select>
                                <span class="description"></span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Image Size</label><br />
                                <span class="description">[image_size]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings_nojs][image_size]" value="<?php echo $settings['script']['script_settings_nojs']['image_size']; ?>" /><br />
                                <span class="description"></span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Width</label><br />                   
                                <span class="description">[width]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings_nojs][width]" value="<?php echo $settings['script']['script_settings_nojs']['width']; ?>" /><br />
                                <span class="description">Can be any css value that's appropiate for width. (e.g. auto, 300px, 95%)</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Height</label><br />                   
                                <span class="description">[height]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings_nojs][height]" value="<?php echo $settings['script']['script_settings_nojs']['height']; ?>" /><br />
                                <span class="description">Can be any css value that's appropiate for width. (e.g. auto, 300px, 95%)</span> 
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Effect</label><br />
                                <span class="description">[effect]</span>
                            </th>
                            <td>
                                <select name="<?php echo $this->settings_name; ?>[script][script_settings][effect]">
                                    <option value="sliceDown" <?php __checked_selected_helper( 'sliceDown', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slice Down</option>
                                    <option value="sliceDownLeft" <?php __checked_selected_helper( 'sliceDownLeft', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slice Down Left</option>
                                    <option value="sliceUp" <?php __checked_selected_helper( 'sliceUp', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slice Up</option>
                                    <option value="sliceUpLeft" <?php __checked_selected_helper( 'sliceUpLeft', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slice Up Left</option>
                                    <option value="sliceUpDown" <?php __checked_selected_helper( 'sliceUpDown', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slice Up Down</option>
                                    <option value="sliceUpDownLeft" <?php __checked_selected_helper( 'sliceUpDownLeft', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slice Up Down Left</option>
                                    <option value="fold" <?php __checked_selected_helper( 'fold', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Fold</option>
                                    <option value="fade" <?php __checked_selected_helper( 'fade', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Fade</option>
                                    <option value="random" <?php __checked_selected_helper( 'random', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Random</option>
                                    <option value="slideInRight" <?php __checked_selected_helper( 'slideInRight', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slide In Right</option>
                                    <option value="slideInLeft" <?php __checked_selected_helper( 'slideInLeft', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Slide In Left</option>
                                    <option value="boxRandom" <?php __checked_selected_helper( 'boxRandom', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Box Random</option>
                                    <option value="boxRain" <?php __checked_selected_helper( 'boxRain', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Box Rain</option>
                                    <option value="boxRainReverse" <?php __checked_selected_helper( 'boxRainReverse', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Box Rain Reverse</option>
                                    <option value="boxRainGrow" <?php __checked_selected_helper( 'boxRainGrow', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Box Rain Grow</option>
                                    <option value="boxRainGrowReverse" <?php __checked_selected_helper( 'boxRainGrowReverse', $settings['script']['script_settings']['effect'], true, 'selected' ); ?>>Box Rain Grow Reverse</option>
                                </select><br />
                                <span class="description">Effect to be used for all slides.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Slices</label><br />
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['slices']; ?>]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][slices]" value="<?php echo $settings['script']['script_settings']['slices']; ?>" /><br />
                                <span class="description">Number of silces to animate. For slice animations only.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Box Columns</label><br />             
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['boxCols']; ?>]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][boxCols]" value="<?php echo $settings['script']['script_settings']['boxCols']; ?>" /><br />
                                <span class="description">Number of columns to animate. For box animations only.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Box Rows</label><br />                   
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['boxRows']; ?>]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][boxRows]" value="<?php echo $settings['script']['script_settings']['boxRows']; ?>" /><br />
                                <span class="description">Number of rows to animate. For box animations only.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Animation Speed</label><br />            
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['animSpeed']; ?>]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][animSpeed]" value="<?php echo $settings['script']['script_settings']['animSpeed']; ?>" /><br />
                                <span class="description">Slide transition speed in milliseconds.</span>
                            </td>
                        </tr> 
                        <tr>
                        <tr>
                            <th scope="row">
                                <label>Pause Time</label><br />                   
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['pauseTime']; ?>]</span>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][pauseTime]" value="<?php echo $settings['script']['script_settings']['pauseTime']; ?>" /><br />
                                <span class="description">Slide pause time in milliseconds.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Direction Navigation</label><br />        
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['directionNav']; ?>]</span>
                            </th>
                            <td>
                                <select name="<?php echo $this->settings_name; ?>[script][script_settings][directionNav]">
                                    <option value="1" <?php __checked_selected_helper( '1', $settings['script']['script_settings']['directionNav'], true, 'selected' ); ?>>Yes</option>
                                    <option value="0" <?php __checked_selected_helper( '0', $settings['script']['script_settings']['directionNav'], true, 'selected' ); ?>>No</option>
                                </select><br />
                                <span class="description">Show the next and prev navigation buttons.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Control Navigation</label><br />         
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['controlNav']; ?>]</span>
                            </th>
                            <td>
                                <select name="<?php echo $this->settings_name; ?>[script][script_settings][controlNav]">
                                    <option value="1" <?php __checked_selected_helper( '1', $settings['script']['script_settings']['controlNav'], true, 'selected' ); ?>>Yes</option>
                                    <option value="0" <?php __checked_selected_helper( '0', $settings['script']['script_settings']['controlNav'], true, 'selected' ); ?>>No</option>
                                </select><br />
                                <span class="description">Show the control navigation buttons.</span>  
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Pause On Hover</label><br />                
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['pauseOnHover']; ?>]</span>
                            </th>
                            <td>
                                <select name="<?php echo $this->settings_name; ?>[script][script_settings][pauseOnHover]">
                                    <option value="1" <?php __checked_selected_helper( '1', $settings['script']['script_settings']['pauseOnHover'], true, 'selected' ); ?>>Yes</option>
                                    <option value="0" <?php __checked_selected_helper( '0', $settings['script']['script_settings']['pauseOnHover'], true, 'selected' ); ?>>No</option>
                                </select><br />
                                <span class="description">Stop slide transition if the mouse is hovered.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Previous Text</label><br />              
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['prevText']; ?>]</span>
                            </th>
                            <td>        
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][prevText]" value="<?php echo $settings['script']['script_settings']['prevText']; ?>" /><br />
                                <span class="description">Text for the prev navigation button. May not be visible if button is styled.</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Next Text</label><br />                   
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['nextText']; ?>]</span>
                            </th>
                            <td>        
                                <input class="regular-text" type="text" name="<?php echo $this->settings_name; ?>[script][script_settings][nextText]" value="<?php echo $settings['script']['script_settings']['nextText']; ?>" /><br />
                                <span class="description">Text for the next navigation button. May not visible if button is styled.</span>
                            </td>
                        </tr> 
                        <tr>
                            <th scope="row">
                                <label>Random Start</label><br />                 
                                <span class="description">[<?php echo $this->global_settings['script']['script_settings_map']['randomStart']; ?>]</span>
                            </th>
                            <td>
                                <select name="<?php echo $this->settings_name; ?>[script][script_settings][randomStart]">
                                    <option value="1" <?php __checked_selected_helper( '1', $settings['script']['script_settings']['randomStart'], true, 'selected' ); ?>>Yes</option>
                                    <option value="0" <?php __checked_selected_helper( '0', $settings['script']['script_settings']['randomStart'], true, 'selected' ); ?>>No</option>
                                </select><br />
                                <span class="description">Start slider on a random slide.</span>
                            </td>
                        </tr> 
                    </table>
                    <p class="submit"><input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit"></p>
                </form>                                                                                                          
            </div>
        <?php } 
        
        /**
        * Get plugins settings.
        * 
        * @return array
        */
        
        private function get_settings() {
            $settings = (array) get_option( $this->settings_name );   
            return $this->array_merge( $this->global_settings, $settings );
        }
        
        /**
        * Merge two arrays with key comparison and value overwrite.
        * 
        * @param array $array1
        * @param array $array2
        * @return array
        */
        
        private function array_merge( $array1, $array2 ) {
            foreach ( $array2 as $key => $value ) {
                if ( ! array_key_exists( $key, $array1 ) ) 
                    break;
                
                if ( is_array( $value ) && is_array( $array1[$key] ) )
                    $array1[$key] = $this->array_merge( $array1[$key], $array2[$key] );

                elseif ( 0 < strlen( $value ) )
                    $array1[$key] = $value;
            }

            return $array1;
        }
    }
    
    $ABY_SimpleSlides = new ABY_SimpleSlides(); 