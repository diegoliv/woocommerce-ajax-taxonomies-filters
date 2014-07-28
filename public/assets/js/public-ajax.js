(function ( $ ) {
	"use strict";

	$(function () {

		// caching some vars
		var $filters = $( '.watf-filter' ),
			$container = $( watf_vars.wc_shop_container );

		var WATF_Sender = {
			query: {},
			request: '',
			post: function( $container ){

				// preparing for submit
				$( watf_vars.wc_shop_container ).hide(); 
				var request = this.request;
				
				$container
					.addClass( 'watf-loading' )
					.html( '<div class="watf-loading-spinner"><span class="watf-spinner"></span>' + watf_vars.loading_text + '</div>' );

				$.post( request )
		            .done( function( response ){
		                $container.removeClass( 'watf-loading' );

		                if( $( response ).find( watf_vars.wc_shop_container ).length > 0 ) {
		                    $container.html( $( response ).find( watf_vars.wc_shop_container ) );
		                } else {
		                    $container.html( $( response ).find( '.woocommerce-info' ) );
		                }

		                //result count
		                if( $( response ).find( watf_vars.wc_count_container ).length > 0 ) {
		                    $( watf_vars.wc_count_container ).html( $( response ).find( watf_vars.wc_count_container ).html() );
		                }

	                    //update browser history (IE doesn't support it)
		                if ( !navigator.userAgent.match( /msie/i ) ) {
		                    window.history.pushState({ "pageTitle": response.pageTitle },"", request );
		                }

		                console.log( this.request );
		                console.log( request );

			        } ).fail( function(){
		                $container
		                	.removeClass( 'watf-loading' )
		                	.html( '<div class="woocommerce-info">' + watf_vars.error_text + '</div>' );
			        } );
			},
			isEmpty: function( obj ) {

			    // null and undefined are "empty"
			    if (obj == null) return true;

			    // Assume if it has a length property with a non-zero value
			    // that that property is correct.
			    if (obj.length > 0)    return false;
			    if (obj.length === 0)  return true;

			    // Otherwise, does it have any properties of its own?
			    // Note that this doesn't handle
			    // toString and valueOf enumeration bugs in IE < 9
			    for (var key in obj) {
			        if (hasOwnProperty.call(obj, key)) return false;
			    }

			    return true;
			},
			getURL: function(){
				return window.location.protocol + "//" + ( window.location.host + "/" + window.location.pathname ).replace( '//', '/' );
			},
			getQuery: function(){
				var queryString = [];

				$.each( this.query, function( key, value ){
					queryString.push( value );
				} );

				return queryString.join( '&' );
			},
			PrepareRequest: function(){
				this.request = this.isEmpty( this.query ) ? this.getURL() : this.getURL() + '?' + this.getQuery();
				console.log( this.request );
			}
		}

		var sender = WATF_Sender;

		// count number of selected terms for each taxonomy
		$( '.watf-filter' ).on( 'click', 'li', function(){
		
			var $filter = $( this ).closest( '.watf-filter' ),
				filter_count = $filter.find( 'input:checkbox:checked' ).length,
				count_html = filter_count > 0 ? '<span>' + filter_count + '</span>': '';

			$filter.find( '.watf-tax-count' ).html( count_html );

		} );

		$( '.woocommerce-atf-filters' ).on( 'click', '.reset-filters', function(){
			$filters.find( '.watf-tax-count' ).html('');
		} );

		// wrap the products container for ajax response 
		$container.wrap('<div class="watf-shop-container"></div>');

		// on form submit
		$( document ).on( 'submit', '.woocommerce-atf-filters', function(){

			// vars for build url
			var href = $( this ).attr( 'action' ),
				$watf_container = $( '.watf-shop-container' );

			$filters.each( function( i ){

				var $checked = $( this ).find( 'input[type="checkbox"]:checked' );

				if( $checked.length > 0 ){

					var taxonomy = $( this ).data( 'taxonomy' );

					var filters = $checked.map( function(){
						return this.value;
					} ).get().join(',');
					
					sender.query[ taxonomy ] = taxonomy + '=' + filters;
					
				}

			} );

			sender.PrepareRequest();

			// submit form and insert the response
			sender.post( $watf_container );

			return false;
		} );

		$( document ).on( 'submit', '.woocommerce-ordering', function(){

			var $watf_container = $( '.watf-shop-container' );

			sender.query.orderby = 'orderby=' + $( '.orderby' ).val();
			sender.PrepareRequest();

			// submit form and insert the response
			sender.post( $watf_container );

			return false;
		} );

	});

}(jQuery));