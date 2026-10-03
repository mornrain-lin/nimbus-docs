/**
 * Nimbus Docs 文档反馈脚本。
 *
 * 点击「有帮助 / 没帮助」后向 admin-ajax.php 提交，
 * 服务端 1 小时内对同一 IP + 文章只计一次，重复提交会返回已记录提示。
 */
( function () {
	'use strict';

	var container = document.querySelector( '[data-nb-feedback]' );

	if ( ! container || ! window.nimbusFeedback ) {
		return;
	}

	var config = window.nimbusFeedback;
	var result = container.querySelector( '[data-nb-feedback-result]' );
	var buttons = container.querySelectorAll( '[data-nb-feedback-value]' );
	var submitted = false;

	Array.prototype.forEach.call( buttons, function ( button ) {
		button.addEventListener( 'click', function () {
			if ( submitted ) {
				return;
			}

			var value = button.getAttribute( 'data-nb-feedback-value' );

			if ( 'up' !== value && 'down' !== value ) {
				return;
			}

			submitted = true;

			// 立即给出选中反馈，提升感知响应速度。
			Array.prototype.forEach.call( buttons, function ( item ) {
				item.classList.toggle( 'is-selected', item === button );
			} );

			var body = new URLSearchParams();

			body.append( 'action', 'nimbus_submit_feedback' );
			body.append( 'nonce', config.nonce || '' );
			body.append( 'postId', String( config.postId || 0 ) );
			body.append( 'value', value );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( payload ) {
					var data = payload && payload.data ? payload.data : {};

					if ( ! result ) {
						return;
					}

					if ( payload && payload.success ) {
						var counts = data.counts || {};
						var total = parseInt( counts.total || 0, 10 );

						result.classList.remove( 'nb-feedback__result--error' );
						result.textContent = total > 0
							? data.message + '（已有 ' + total + ' 位读者反馈。）'
							: data.message || config.savedText;
						return;
					}

					if ( result ) {
						result.classList.add( 'nb-feedback__result--error' );
						result.textContent = data.message || config.failedText;
					}

					// 失败时恢复按钮可点，允许重试。
					submitted = false;

					Array.prototype.forEach.call( buttons, function ( item ) {
						item.classList.remove( 'is-selected' );
					} );
				} )
				.catch( function () {
					if ( result ) {
						result.classList.add( 'nb-feedback__result--error' );
						result.textContent = config.failedText;
					}

					submitted = false;

					Array.prototype.forEach.call( buttons, function ( item ) {
						item.classList.remove( 'is-selected' );
					} );
				} );
		} );
	} );
} )();
