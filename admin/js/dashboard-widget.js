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
				$( this ).parent().addClass('hidden').parent().find( '.full-path' ).removeClass( 'hidden' );
			} );

			// refresh the sizes
			self.widget.on ( 'click', '.refresh', function( e ) {
				e.preventDefault();
				self.populateSizes( true );
			} );

		},

		populateSizes: function( refresh ) {
			var self = this;

			var cellSum = this.table.find( '.cell-sum' );
			cellSum.find( '.spinner' ).addClass( 'is-active' ).removeClass( 'hidden' );
			cellSum.find( '.size' ).text( '' );

			self.table.find( '.cell-size-data' ).each( function() {
				var el = $( this );
				el.find( '.spinner' ).addClass( 'is-active' ).removeClass( 'hidden' );
				el.find( '.size' ).text( '' );
				el.removeClass( 'cell-has-size' ).addClass( 'cell-size-needed' );
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
				el.find( '.spinner' ).removeClass( 'is-active' ).addClass( 'hidden' );
				el.find( '.size' ).text( response.size_friendly ).data( 'size', response.size );
				el.addClass( 'cell-has-size' ).removeClass( 'cell-size-needed' );
				Dashboard_Directory_Size.updateTotalSize();
			}
		},

		updateTotalSize: function() {

			if ( this.table.find( '.cell-size-needed' ).length > 0 )  {
				return;
			}

			var totalSize = 0;
			this.table.find( '.cell-has-size' ).each( function() {
				totalSize += parseInt( $(this).find( '.size' ).data( 'size' ) );
			} );

			var self = this;

			$.ajax( {
				url: Dashboard_Directory_Size_Settings.endpoints.size_format,
				method: 'GET',
				data: { "size":totalSize },
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', Dashboard_Directory_Size_Settings.nonce );
				}
			} ).done( function( response ) {
				var cellSum = self.table.find( '.cell-sum' );
				cellSum.find( '.spinner' ).removeClass( 'is-active' ).addClass( 'hidden' );
				cellSum.find( '.size' ).text( response.size_friendly );
			} );
		}

	};

	Dashboard_Directory_Size.init();


} ) ( jQuery );
