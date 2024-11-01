jQuery( document ).ready( function( $ ) {
    $( '.simple_slides' ).each( function( i, v ) {
        var id = $( v ).attr( 'id' );
        var settings = window[id];
        
        if ( 'object' == typeof settings ) {    
            if ( 'function' == typeof $( v )[settings.f] )
                $( v )[settings.f]( settings );
        }  
    } );
} );