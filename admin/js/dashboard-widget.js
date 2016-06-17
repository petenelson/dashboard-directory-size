( function ( $ ) {

	'use strict';

	var Dashboard_Directory_Size = {

		widget: null,
		table: null,

		init: function() {

			// only run this if the dashboard widget is present
			if ( ! document.getElementById( 'dashboard-directory-size-dashboard-widget' ) ) {
				return;
			}

			// there is a Dashboard_Directory_Size_Settings object localized by the widget code

			this.widget = $( document.getElementById( 'dashboard-directory-size-dashboard-widget' ) );
			this.table = this.widget.find( '.dashboard-directory-size-table' );

			this.bindEvents();
			this.populateSizes( false );

		},

		bindEvents: function() {

			var self = this;

			// expand trimmed paths
			self.widget.on( 'click', '.trimmed-path-expand', function( e ) {
				e.preventDefault();
				$( this ).parent().addClass('hidden').parent().find( '.full-path' ).removeClass('hidden');
			} );

			// refresh the sizes
			self.widget.on ( 'click', '.refresh', function( e ) {
				e.preventDefault();
				self.populateSizes( true );
			} );

		},

		populateSizes: function( refresh ) {
			var self = this;
			self.table.find( '.cell-size-needed' ).each( function() {
				var el = $( this );
				el.find( '.spinner' ).addClass( 'is-active' );
				el.find( '.size' ).html( '' );
				self.getSize( el, refresh );
			} );

		},

		getSize: function( el, refresh ) {
			var self = this;

			var data = {
				path: el.data( 'path' ),
				refresh: refresh ? 1 : 0
			};

			$.ajax( {
				url: Dashboard_Directory_Size_Settings.endpoints.size,
				method: 'GET',
				data: data,
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', Dashboard_Directory_Size_Settings.nonce );
				}
			} ).done( function( response ) {
				self.populateSize( el, response );
			} );

		},

		populateSize: function( el, response ) {
			if ( response ) {
				el.find( '.spinner' ).removeClass( 'is-active' );
				el.find( '.size' ).html( response.size_friendly );
			}
		}

	};

	Dashboard_Directory_Size.init();


} ) ( jQuery );
