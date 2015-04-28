jQuery(document).ready(function() {

	jQuery( '.dashboard-directory-size-table .trimmed-path-expand' ).click( function( e ) {
		e.preventDefault();
		jQuery( this ).parent().removeClass('trimmed-path-visible').parent().find( '.full-path' ).addClass('full-path-visible');
	});

});