<?php

    class ABY_SS_Types {
        public function __construct() {
            add_action( 'init', array( $this, 'init' ) );
        }           
        
        public function init() {
            $this->register_types();   
        }                                                 
        
        public function register_types() {     
            $labels = array(
                'name'                  => __( 'Slides' ),
                'singular_name'         => __( 'Slide' ),
                'add_new'               => __( 'New Slide' ),
                'add_new_item'          => __( 'Add New Slide' ),
                'edit_item'             => __( 'Edit Slide' ),
                'new_item'              => __( 'New Slide' ),
                'all_items'             => __( 'All Slides' ),
                'view_item'             => __( 'View Slide' ),
                'search_items'          => __( 'Search Slides' ),
                'not_found'             => __( 'No slides found'),
                'not_found_in_trash'    => __( 'No slides found in trash' ), 
                'menu_name'             => 'Simple Slides'
            );
            
            $args = array(
                'labels'                => $labels,
                'public'                => false,
                'show_ui'               => true, 
                'query_var'             => false,
                'rewrite'               => false,
                'menu_position'         => 50,
                'menu_icon'             => null, // todo: Create an icon for the plugin.
                'supports'              => array( 
                    'title', 
                    'editor', 
                    'thumbnail',
                    'page-attributes',
                )
            );     
                       
            register_post_type( 'ss-slide', $args );
            
            $labels = array(
                'name'              => __( 'Slide Sets' ),
                'singular_name'     => __( 'Slide Set' ),
                'search_items'      => __( 'Search Slide Groups' ),
                'popular_items'     => __( 'Popular Slide Set' ),
                'all_items'         => __( 'All Slide Sets' ),
                'edit_item'         => __( 'Edit Slide Set' ), 
                'update_item'       => __( 'Update Slide Set' ),
                'add_new_item'      => __( 'Add New Slide Set' ),
                'new_item_name'     => __( 'New Slide Set' ),
                'separate_items_with_commas'    => __( 'Separate slide sets with commas.' ),
                'add_or_remove_items'           => __( 'Add or remove slide set.' ),
                'choose_from_most_used'         => __( 'Choose from the most used slide sets.' ),
                'menu_name'                     => __( 'Slide Sets' ),
            ); 

            register_taxonomy(
                'ss-slide-set',
                array( 'ss-slide' ),
                array(
                    'public'        => false,
                    'hierarchical'  => false,
                    'labels'        => $labels,         
                    'show_ui'       => true,
                    'show_tagcloud' => false,
                    'rewrite'       => false
                )
            );
        }      
    }
    
    $ABY_SS_Types = new ABY_SS_Types();