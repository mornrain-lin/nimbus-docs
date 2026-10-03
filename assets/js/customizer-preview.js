/**
 * Nimbus Docs Customizer 实时预览脚本。
 */
( function ( $ ) {
	'use strict';

	if ( ! window.wp || ! window.wp.customize ) {
		return;
	}

	var api = window.wp.customize;

	api( 'blogname', function ( value ) {
		value.bind( function ( to ) {
			$( '.nb-brand__name' ).text( to );
		} );
	} );
} )( window.jQuery );
