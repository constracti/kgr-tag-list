jQuery(document).on( 'click', '.kgr-tag-list-widget-backend-toggle', function() {

var button = jQuery( this );

var rows = button.parent().siblings( '.kgr-tag-list-widget-backend-container' ).children();

if ( button.html() === 'Show' ) {
	button.html( 'Hide' );
	rows.show();
} else {
	button.html( 'Show' );
	rows.filter( ':has(input[type="checkbox"]:not(:checked))' ).hide();
}

} );
