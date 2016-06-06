( function ( $ ) {

	'use strict';

	var Dashboard_Directory_Size = {

		table: null,

		init: function() {

			this.table = $( '.dashboard-directory-size-table');

			this.bindEvents();

		},

		bindEvents: function() {

			var self = this;

			self.table.on( 'click', '.trimmed-path-expand', function( e ) {
				e.preventDefault();
				$( this ).parent().addClass('hidden').parent().find( '.full-path' ).removeClass('hidden');
			});

		},

	};

	Dashboard_Directory_Size.init();


} ) ( jQuery );
