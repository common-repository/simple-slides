<?php 

    class ABY_SS_List_Table {
        public function __construct() {     
            add_action( 'admin_head', array( $this, 'admin_head' ) );
            add_action( 'admin_init', array( $this, 'admin_init' ) );       
        }
        
        public function admin_init() {      
            add_action( 'manage_posts_custom_column', array( $this, 'manage_table_column' ) );
            add_filter( 'manage_edit-ss-slide_columns', array( $this, 'add_table_column' ) );                                             
            add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) ); 
            add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
            add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
        }     
        
        public function admin_head() {
            global $pagenow, $typenow;
            
            if ( 'edit.php' == $pagenow && 'ss-slide' == $typenow )
                wp_enqueue_style( 'ss.admin.tablelist', ABY_SS_PLUGIN_URL . '/styles/listtable.css' );
        }
        
        public function remove_row_actions( $actions, $post ) {
            global $current_screen;
            
            if ( 'ss-slide' != $current_screen->post_type )
                return $actions;
            
            unset( $actions['inline hide-if-no-js'] );
            return $actions;
        }
        
        public function restrict_manage_posts() {
            global $typenow;
            
            if ( 'ss-slide' != $typenow ) 
                return;
                
            $filters = array( 'ss-slide-set' );

            foreach ( $filters as $tax_slug ) {
                $tax_obj = get_taxonomy( $tax_slug );
                $tax_name = $tax_obj->labels->name;
                $terms = get_terms( $tax_slug );
                
                echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
                echo "<option value=''>Show All $tax_name</option>";
                foreach ( $terms as $term ) {
                    echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
                }
                echo "</select> ";
            }
        }
        
        public function add_table_column( $columns ) {      
            $columns = array_merge( 
                array_slice( $columns, 0, 2 ),
                array( 
                    'thumb'      => 'Preview', 
                    'full_size'  => 'Full Size',
                    'slide_set'  => 'Slide Set',
                    'menu_order' => 'Order' 
                ),
                array_slice( $columns, 2 )
            );
            
            return $columns;
        }
        
        public function manage_table_column( $name ) {
            global $post;
            
            if ( 'ss-slide' != $post->post_type )
                return;
                
            switch ( $name ) {
                case 'thumb':
                    echo get_the_post_thumbnail( $post->ID, 'ss_preview' );
                    break;
                    
                case 'full_size':
                    $img_id = get_post_thumbnail_id( $post->id );
                    $img = wp_get_attachment_image_src( $img_id, 'full' );
                    
                    if ( $img )
                        echo sprintf( '%s x %s', $img[1], $img[2] );
                        
                    break;
                    
                case 'slide_set':
                    $terms =  wp_get_post_terms( $post->ID, 'ss-slide-set' );     
                    $html = '';    
                    
                    foreach ( $terms as $term ) {
                        $html .= '<a href="' . admin_url( 'edit.php?post_type=ss-slide&ss-slide-set=' . $term->slug ) . '" title="slug:' . $term->slug . '">'; 
                        $html .= $term->name;
                        $html .= '</a> ';
                    }
                    
                    echo trim( $html, ' ' );
                    break;
                    
                case 'menu_order':
                    echo $post->menu_order;
                    break;
            }
        }  
        
        // todo: Improve update messages.                          
        public function post_updated_messages( $messages ) {
            global $post, $post_ID;

            $messages['ss-slide'] = array(
                0   => '',
                1   => __( 'Slide updated' ),
                2   => __( 'Custom field updated' ),
                3   => __( 'Custom field deleted' ),
                4   => __( 'Slide updated' ),
                6   => __( 'Slide added' ),
                7   => __( 'Slide saved' ),
                8   => __( 'Slide submitted' ),
                10  => __( 'Slide draft updated' )
            );

            return $messages;
        }
    }
    
    $ABY_SS_List_Table = new ABY_SS_List_Table();
