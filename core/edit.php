<?php

    class ABY_SS_Edit {
        public function __construct() {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }
        
        public function admin_init() {   
            add_action( 'add_meta_boxes', array( $this, 'add_link_meta_box' ) );
            add_action( 'save_post', array( $this, 'save_link' ) );           
            add_action( 'load-post.php', array( $this, 'init_type_page' ) );
            add_action( 'load-post-new.php', array( $this, 'init_type_page' ) );
            add_action( 'load-media-upload.php', array( $this, 'init_media_page' ) ); 
            add_action( 'load-async-upload.php', array( $this, 'init_async_page' ) ); 
            add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );      
        }
        
        public function add_link_meta_box() {
            add_meta_box( 'ss-slide-options', __( 'Slide Options' ), array( $this, 'get_meta_box' ), 'ss-slide', 'side', 'low' );
        }
        
        public function get_meta_box( $post ) {                       
            $use_caption = get_post_meta( $post->ID, 'ss-slide-use-caption', true );
            $use_caption_title = get_post_meta( $post->ID, 'ss-slide-use-caption-title', true );
            
            $link = get_post_meta( $post->ID, 'ss-slide-link', true );
            $link_new_window = get_post_meta( $post->ID, 'ss-slide-link-new-window', true );
            
            wp_nonce_field( 'ss-slide-edit', 'ss-slide-options-nonce' );
            ?>
                <p>
                    <strong>Caption</strong><br />
                    <label>
                        <input type="checkbox" name="ss-slide-use-caption" id="ss-slide-use-caption" value="1" <?php __checked_selected_helper( '1', $use_caption, true, 'checked' ); ?> />
                        Enable caption for this slide.
                    </label><br />
                    <label>
                        <input type="checkbox" name="ss-slide-use-caption-title" id="ss-slide-use-caption-title" value="1" <?php __checked_selected_helper( '1', $use_caption_title, true, 'checked' ); ?> />
                        Add slide title to caption.
                    </label>
                </p>
                <p>
                    <strong>Link</strong><br />
                    <input type="text" name="ss-slide-link" id="ss-slide-link" value="<?php echo $link; ?>" style="width: 100%;" /><br />
                    <label><input type="checkbox" name="ss-slide-link-new-window" id="ss-slide-link-new-window" value="1" <?php __checked_selected_helper( '1', $link_new_window, true, 'checked' ); ?> /> Open link in new window.</label>
                </p>
            <?php
        }
        
        public function save_link( $post_id ) {       
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
                return;

            if ( ! wp_verify_nonce( $_POST['ss-slide-options-nonce'], 'ss-slide-edit' ) )
                return;    
                
            if ( 'ss-slide' != $_POST['post_type'] || ! current_user_can( 'edit_post', $post_id ) )
                return;
            
            $use_caption = $_POST['ss-slide-use-caption'];
            $use_caption_title = $_POST['ss-slide-use-caption-title'];

            $link = $_POST['ss-slide-link'];
            $link_new_window = $_POST['ss-slide-link-new-window'];
            
            update_post_meta( $post_id, 'ss-slide-use-caption', $use_caption );
            update_post_meta( $post_id, 'ss-slide-use-caption-title', $use_caption_title );
            
            update_post_meta( $post_id, 'ss-slide-link', $link );
            update_post_meta( $post_id, 'ss-slide-link-new-window', $link_new_window );
        }
        
        public function init_type_page() {
            global $typenow;
            
            if ( 'ss-slide' == $typenow ) { 
                add_filter( 'gettext', array( $this, 'add_custom_text' ), 10, 2 );     
                add_filter( 'media_buttons_context', create_function( '', 'return;' ), 10, 0 );
            } 
        }
        
        public function init_media_page() {        
            $post = get_post( $_GET['post_id'] );
            
            if ( 'ss-slide' == $post->post_type ) {
                add_filter( 'gettext', array( $this, 'add_custom_text' ), 10, 2 );     
                add_filter( 'media_upload_tabs', array( $this, 'init_media_box' ), 99, 1 );   
            }
        }
        
        public function init_async_page() {                               
            if ( '1' == $_POST['short'] )
                return;                                         
                
            $attachment = get_post( $_POST['attachment_id'] );
            $post = get_post( $attachment->post_parent );     
        
            if ( 'ss-slide' == $post->post_type )
                add_filter( 'gettext', array( $this, 'add_custom_text' ), 99, 2 );     
        }
        
        public function init_media_box( $tabs ) {                                  
             unset( $tabs['gallery'] ); 
             return $tabs;
        }
        
        public function add_custom_text( $translated_text, $text ) {
            if ( 'Featured Image' == $text )
                $text = 'Slide Image';
                
            if ( 'Set featured image' == $text )
                $text = 'Set slide image';
                
            if ( 'Use as featured image' == $text )
                $text = 'Use as slide image';
                
            if ( 'Remove featured image' == $text )
                $text = 'Remove slide image';
                
            if ( 'Attributes' == $text )
                $text = 'Slide Attributes';
                
            return $text;
        }
        
        public function contextual_help( $contextual_help, $screen_id, $screen ) { 
            if ( 'ss-slide' == $screen->id ) {
                $contextual_help = '<p>Always remember to <strong><u>specify an image</u></strong> for each slide. The slide <strong><u>will not be displayed</u></strong> if it has no image.<br />For more infomation kindly read the <a target="_blank" href="http://apocalypseboy.com/simple-slides/">plugin documentation</a>.</p>';
            }   
            
            return $contextual_help;
        }
    }
    
    $ABY_SS_Edit = new ABY_SS_Edit();  
