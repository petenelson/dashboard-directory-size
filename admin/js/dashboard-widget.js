jQuery(document).ready(function() {

	jQuery( '.dashboard-directory-size-table .trimmed-path-expand' ).click( function( e ) {
		e.preventDefault();
		jQuery( this ).hide().parent().parent().find( '.full-path' ).show();
	});

});