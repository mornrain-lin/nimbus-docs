/**
 * Nimbus Docs 代码复制脚本。
 *
 * 优先使用 Clipboard API；不可用时降级到 execCommand('copy')。
 * 非 HTTPS 环境下navigator.clipboard 为 undefined，此时走降级路径。
 */
( function () {
	'use strict';

	var l10n = window.nimbusL10n || {};

	/**
	 * 从按钮所在的代码块中取出纯代码文本。
	 *
	 * @param {HTMLElement} button 复制按钮。
	 * @return {string} 代码文本。
	 */
	function readCode( button ) {
		var block = button.closest( '.nb-code' );
		var pre = block ? block.querySelector( 'pre' ) : null;

		return pre ? pre.textContent : '';
	}

	/**
	 * 降级方案：临时 textarea + execCommand。
	 *
	 * @param {string} text 待复制文本。
	 * @return {boolean} 是否成功。
	 */
	function fallbackCopy( text ) {
		var textarea = document.createElement( 'textarea' );

		textarea.value = text;
		textarea.setAttribute( 'readonly', '' );
		textarea.style.position = 'fixed';
		textarea.style.top = '-1000px';
		textarea.style.opacity = '0';

		document.body.appendChild( textarea );
		textarea.select();

		var ok = false;

		try {
			ok = document.execCommand( 'copy' );
		} catch ( e ) {
			ok = false;
		}

		document.body.removeChild( textarea );

		return ok;
	}

	/**
	 * 执行复制。
	 *
	 * @param {string} text 代码文本。
	 * @return {Promise<boolean>} 是否成功。
	 */
	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text ).then(
				function () {
					return true;
				},
				function () {
					return fallbackCopy( text );
				}
			);
		}

		return Promise.resolve( fallbackCopy( text ) );
	}

	function initCopy() {
		var buttons = document.querySelectorAll( '[data-nb-copy]' );

		if ( ! buttons.length ) {
			return;
		}

		var resetTimer = null;

		Array.prototype.forEach.call( buttons, function ( button ) {
			button.addEventListener( 'click', function () {
				var text = readCode( button );

				if ( ! text ) {
					return;
				}

				var label = button.querySelector( '.nb-code__copy-text' );
				var original = label ? label.textContent : '';

				copyText( text ).then( function ( ok ) {
					if ( ! label ) {
						return;
					}

					if ( ok ) {
						button.classList.add( 'is-copied' );
						label.textContent = l10n.copied || '已复制';
					} else {
						button.classList.add( 'is-failed' );
						label.textContent = l10n.copyFailed || '复制失败';
					}

					if ( resetTimer ) {
						window.clearTimeout( resetTimer );
					}

					resetTimer = window.setTimeout( function () {
						button.classList.remove( 'is-copied', 'is-failed' );
						label.textContent = original;
					}, 2000 );
				} );
			} );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initCopy );
	} else {
		initCopy();
	}
} )();
