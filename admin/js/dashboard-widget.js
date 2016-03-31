( function ( $ ) {

	$( '.dashboard-directory-size-table').on( 'click', '.trimmed-path-expand', function( e ) {
		e.preventDefault();
		$( this ).parent().addClass('hidden').parent().find( '.full-path' ).removeClass('hidden');
	});

} ) ( jQuery );
