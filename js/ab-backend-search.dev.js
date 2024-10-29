jQuery( document ).ready( function( $ ) {
	$( '#adminbar-search' ).on( 'focusin', function() {
		$( '#adminbarsearch' ).addClass( 'focus' );
	} );

	$( '#adminbar-search' ).on( 'focusout', function() {
		if( $( '#adminbarsearch' ).is( ':hover' ) )
			return;

		$( '#adminbarsearch' ).removeClass( 'focus' );
	} );

	$( '#adminbarsearch' ).on( 'mouseleave', function() {
		if( $( '#adminbar-search' ).is( ':focus' ) )
			return;

		$( '#adminbarsearch' ).removeClass( 'focus' );
		$( '#adminbarsearch' ).removeClass( 'show' );
	} );

	$( '.search-arrow' ).on( 'click', function() {
		$( '#adminbarsearch' ).addClass( 'show' );
		$( '#adminbar-search' ).focus();
	} );

	$( '.search-options' ).on( 'mouseleave', function() {
		$( '#adminbarsearch' ).removeClass( 'show' );
	} );

	$( '.search-options input[type="radio"]' ).on( 'click', function() {
		$( '#adminbarsearch input[type="hidden"]' ).remove();
		hidden = $(this).data();

		$.each( hidden, function( name, value ) {
			if ( name == 'url' ) {
				$( '#adminbarsearch' ).attr( 'action', value );
			} else {
				$( '#adminbarsearch' ).append( '<input type="hidden" name="' + name + '" value="' + value + '"/>' );
			}
		} );

		$( '#adminbar-search' ).focus();
	} );

	$( '#adminbarsearch' ).on( 'submit', function() {
		$( '#adminbarsearch' ).removeClass( 'show' );
		$( '#adminbarsearch input[type="radio"]' ).remove();
	} );
} );
