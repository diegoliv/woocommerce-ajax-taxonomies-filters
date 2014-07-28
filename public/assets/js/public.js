(function ( $ ) {
	"use strict";

	$(function () {

		var $filters = $( '.watf-filter' );

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

		$( document ).on( 'submit', '.woocommerce-atf-filters', function(){

			var query = [],
				request = '';

			$filters.each( function( i ){

				var $checked = $( this ).find( 'input[type="checkbox"]:checked' );

				if( $checked.length > 0 ){

					var taxonomy = $( this ).data( 'taxonomy' );

					var filters = $checked.map( function(){
						return this.value;
					} ).get().join(',');
					
					query.push( taxonomy + '=' + filters );
					
				}

			} );

			request = '?' + query.join('&');

			window.location = request;

			return false;
		} );

	});

}(jQuery));