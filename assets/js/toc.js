/**
 * Nimbus Docs 目录树脚本。
 *
 * 负责：
 * - 折叠 / 展开目录条目，状态记入 localStorage
 * - 整棵目录树的收起 / 展开，同样记忆
 * - 当前页面高亮，展开其所有祖先
 * - 把当前页面的 h2 - h4 标题追加为页内锚点条目
 * - 移动端侧栏开合
 * - `/` 快捷键聚焦搜索框
 * - 搜索结果页关键词高亮
 *
 * 纯原生 JS，无第三方依赖。
 */
( function () {
	'use strict';

	var toc = document.querySelector( '[data-nb-toc]' );
	var sidebar = document.querySelector( '[data-nb-sidebar]' );
	var l10n = window.nimbusL10n || {};

	/**
	 * 读取 localStorage 中的目录状态。
	 *
	 * @return {Object} 状态对象；解析失败时返回空对象。
	 */
	function readState() {
		if ( ! l10n.storageKey ) {
			return {};
		}

		try {
			var raw = window.localStorage.getItem( l10n.storageKey );

			return raw ? JSON.parse( raw ) || {} : {};
		} catch ( e ) {
			return {};
		}
	}

	/**
	 * 写入 localStorage。
	 *
	 * @param {Object} state 状态对象。
	 */
	function writeState( state ) {
		if ( ! l10n.storageKey ) {
			return;
		}

		try {
			window.localStorage.setItem( l10n.storageKey, JSON.stringify( state ) );
		} catch ( e ) {
			// 隐私模式下可能不可写，忽略。
		}
	}

	/**
	 * 目录树的展开 / 折叠逻辑。
	 */
	function initTocState() {
		if ( ! toc ) {
			return;
		}

		var state = readState();

		// 整棵树收起状态。
		var collapsedDefault = !! document.body.classList.contains( 'nb-toc-collapsed' );

		if ( typeof state.collapsed === 'boolean' ) {
			collapsedDefault = state.collapsed;
		}

		toc.setAttribute( 'data-collapsed', collapsedDefault ? 'true' : 'false' );

		var collapseBtn = toc.querySelector( '[data-nb-collapse]' );

		if ( collapseBtn ) {
			var label = collapseBtn.querySelector( 'span' );

			function syncCollapseLabel() {
				var collapsed = toc.getAttribute( 'data-collapsed' ) === 'true';

				if ( label ) {
					label.textContent = collapsed
						? l10n.expandLabel || '展开'
						: l10n.collapseLabel || '收起';
				}
			}

			syncCollapseLabel();

			collapseBtn.addEventListener( 'click', function () {
				var collapsed = toc.getAttribute( 'data-collapsed' ) !== 'true';

				toc.setAttribute( 'data-collapsed', collapsed ? 'true' : 'false' );
				state.collapsed = collapsed;
				writeState( state );
				syncCollapseLabel();
			} );
		}

		// 单项展开状态。
		var toggles = toc.querySelectorAll( '[data-nb-toggle]' );

		Array.prototype.forEach.call( toggles, function ( toggle ) {
			var item = toggle.closest( '.nb-toc__item' );

			if ( ! item ) {
				return;
			}

			var id = item.getAttribute( 'data-id' );

			if ( id && state[ id ] ) {
				item.classList.add( 'is-expanded' );
				toggle.setAttribute( 'aria-expanded', 'true' );
			}

			toggle.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				var expanded = item.classList.toggle( 'is-expanded' );

				toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );

				if ( id ) {
					state[ id ] = expanded;
					writeState( state );
				}
			} );
		} );
	}

	/**
	 * 高亮当前页面，并展开它的所有祖先。
	 */
	function initCurrentHighlight() {
		if ( ! toc ) {
			return;
		}

		var links = toc.querySelectorAll( '[data-nb-toc-link]' );
		var currentPath = window.location.pathname;

		Array.prototype.forEach.call( links, function ( link ) {
			var linkPath = link.getAttribute( 'href' );

			if ( ! linkPath ) {
				return;
			}

			// 去掉可能的尾部斜杠差异。
			if ( linkPath.replace( /\/$/, '' ) !== currentPath.replace( /\/$/, '' ) ) {
				return;
			}

			link.classList.add( 'is-active' );
			link.setAttribute( 'aria-current', 'page' );

			// 向上展开所有祖先。
			var parent = link.closest( '.nb-toc__item' );

			while ( parent ) {
				if ( parent.classList.contains( 'nb-toc__item' ) ) {
					parent.classList.add( 'is-expanded' );

					var toggle = parent.querySelector( ':scope > [data-nb-toggle]' );

					if ( toggle ) {
						toggle.setAttribute( 'aria-expanded', 'true' );
					}
				}

				parent = parent.parentElement ? parent.parentElement.closest( '.nb-toc__item' ) : null;
			}
		} );
	}

	/**
	 * 把当前页面的 h2 - h4 标题追加为页内锚点条目。
	 */
	function initInPageAnchors() {
		if ( ! toc || ! sidebar ) {
			return;
		}

		var content = document.querySelector( '.nb-content' );

		if ( ! content ) {
			return;
		}

		var headings = content.querySelectorAll( 'h2[id], h3[id], h4[id]' );

		if ( ! headings.length ) {
			return;
		}

		// 给没有 id 的标题补id。
		Array.prototype.forEach.call( headings, function ( heading ) {
			if ( heading.id ) {
				return;
			}

			var slug = ( heading.textContent || '' )
				.trim()
				.toLowerCase()
				.replace( /[\s\u3000]+/g, '-' )
				.replace( /[^\w\u4e00-\u9fff-]/g, '' )
				.replace( /-+/g, '-' )
				.replace( /^-|-$/g, '' );

			heading.id = slug || 'section-' + Math.floor( Math.random() * 10000 );
		} );

		// 补齐标题锚点链接。
		Array.prototype.forEach.call( headings, function ( heading ) {
			if ( heading.querySelector( '.nb-heading-anchor' ) ) {
				return;
			}

			var anchor = document.createElement( 'a' );
			anchor.className = 'nb-heading-anchor';
			anchor.href = '#' + heading.id;
			anchor.setAttribute( 'aria-hidden', 'true' );
			anchor.tabIndex = -1;
			anchor.textContent = '#';

			heading.appendChild( anchor );
		} );

		// 构造页内目录，插入到页面树之后。
		var nav = document.createElement( 'nav' );
		nav.className = 'nb-toc nb-toc--inpage';
		nav.setAttribute( 'data-nb-toc-inpage', '' );

		var heading = document.createElement( 'h2' );
		heading.className = 'nb-toc__heading';
		heading.textContent = l10n.onThisPage || '本页内容';
		nav.appendChild( heading );

		var list = document.createElement( 'ul' );
		list.className = 'nb-toc__list';

		var currentId = null;
		var currentItem = null;

		Array.prototype.forEach.call( headings, function ( heading ) {
			var level = heading.tagName.charAt( 1 );

			// h2 出现时结束上一组。
			if ( '2' === level ) {
				currentItem = null;
			}

			var item = document.createElement( 'li' );
			item.className = 'nb-toc__item';

			var link = document.createElement( 'a' );
			link.className = 'nb-toc__link nb-toc__link--h' + level;
			link.href = '#' + heading.id;
			link.setAttribute( 'data-nb-inpage-link', '' );
			link.textContent = ( heading.textContent || '' ).replace( /#$/, '' ).trim();

			item.appendChild( link );

			if ( '2' === level ) {
				list.appendChild( item );
				currentItem = item;
			} else if ( currentItem ) {
				var sub = currentItem.querySelector( '.nb-toc__sublist' );

				if ( ! sub ) {
					sub = document.createElement( 'ul' );
					sub.className = 'nb-toc__sublist';
					currentItem.appendChild( sub );
				}

				sub.appendChild( item );
			} else {
				list.appendChild( item );
			}

			heading.setAttribute( 'data-nb-inpage-target', currentId || '' );
			currentId = heading.id;
		} );

		nav.appendChild( list );
		toc.parentNode.insertBefore( nav, toc.nextSibling );
	}

	/**
	 * 移动端侧栏开合。
	 */
	function initSidebarToggle() {
		if ( ! sidebar ) {
			return;
		}

		var toggle = document.querySelector( '[data-nb-sidebar-toggle]' );
		var backdrop = document.querySelector( '[data-nb-backdrop]' );

		if ( ! toggle ) {
			return;
		}

		function open() {
			sidebar.classList.add( 'is-open' );
			document.body.classList.add( 'nb-sidebar-open' );
			toggle.setAttribute( 'aria-expanded', 'true' );

			if ( backdrop ) {
				backdrop.hidden = false;

				// 强制重排，保证 transition 生效。
				void backdrop.offsetWidth;
				backdrop.classList.add( 'is-open' );
			}
		}

		function close() {
			sidebar.classList.remove( 'is-open' );
			document.body.classList.remove( 'nb-sidebar-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );

			if ( backdrop ) {
				backdrop.classList.remove( 'is-open' );
				backdrop.hidden = true;
			}
		}

		toggle.addEventListener( 'click', function () {
			if ( sidebar.classList.contains( 'is-open' ) ) {
				close();
			} else {
				open();
			}
		} );

		if ( backdrop ) {
			backdrop.addEventListener( 'click', close );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				close();
			}
		} );

		// 视口变宽到桌面布局时自动清除状态。
		window.addEventListener( 'resize', function () {
			if ( window.matchMedia( '(min-width: 861px)' ).matches ) {
				close();
			}
		} );

		// 点击侧栏内的链接后自动收起。
		sidebar.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a' ) && window.matchMedia( '(max-width: 860px)' ).matches ) {
				close();
			}
		} );
	}

	/**
	 * 搜索框快捷键与焦点管理。
	 */
	function initSearchShortcuts() {
		document.addEventListener( 'keydown', function ( event ) {
			if ( '/' === event.key && ! event.metaKey && ! event.ctrlKey && ! event.altKey ) {
				var active = document.activeElement;
				var tag = active ? active.tagName : '';

				if ( 'INPUT' === tag || 'TEXTAREA' === tag || 'SELECT' === tag || ( active && active.isContentEditable ) ) {
					return;
				}

				var field = document.querySelector( '[data-nb-search]' );

				if ( field ) {
					event.preventDefault();
					field.focus();
					field.select();
				}
			}
		} );

		// 头部搜索框回车提交时带上搜索路径。
		var headerField = document.querySelector( '[data-nb-search]' );

		if ( headerField && ! headerField.form ) {
			headerField.addEventListener( 'keydown', function ( event ) {
				if ( 'Enter' !== event.key ) {
					return;
				}

				var value = headerField.value.trim();

				if ( '' === value ) {
					return;
				}

				event.preventDefault();
				window.location.href = headerField.getAttribute( 'data-nb-search-base' ) || '/?s=' + encodeURIComponent( value );
			} );
		}

		// 404 页按钮：聚焦头部搜索框。
		var focusButton = document.querySelector( '[data-nb-focus-search]' );

		if ( focusButton ) {
			focusButton.addEventListener( 'click', function () {
				var field = document.querySelector( '[data-nb-search]' );

				if ( field ) {
					field.focus();
				}
			} );
		}
	}

	/**
	 * 搜索结果页关键词高亮。
	 */
	function initSearchHighlight() {
		if ( ! window.nimbusSearchQuery ) {
			return;
		}

		var raw = String( window.nimbusSearchQuery ).trim();

		if ( raw.length < 2 ) {
			return;
		}

		var terms = raw
			.split( /\s+/ )
			.map( function ( item ) {
				return item.trim().toLowerCase();
			} )
			.filter( function ( item ) {
				return item.length >= 2 && item.length <= 20;
			} );

		if ( ! terms.length ) {
			return;
		}

		var nodes = document.querySelectorAll( '[data-nb-highlight]' );

		if ( ! nodes.length ) {
			return;
		}

		Array.prototype.forEach.call( nodes, function ( node ) {
			var text = node.textContent;
			var lower = text.toLowerCase();
			var ranges = [];

			terms.forEach( function ( term ) {
				var from = 0;
				var index = lower.indexOf( term, from );

				while ( index !== -1 ) {
					ranges.push( { start: index, end: index + term.length } );
					from = index + term.length;
					index = lower.indexOf( term, from );
				}
			} );

			if ( ! ranges.length ) {
				return;
			}

			ranges.sort( function ( a, b ) {
				return a.start - b.start;
			} );

			var merged = [ ranges[ 0 ] ];

			for ( var i = 1; i < ranges.length; i++ ) {
				var last = merged[ merged.length - 1 ];

				if ( ranges[ i ].start <= last.end ) {
					last.end = Math.max( last.end, ranges[ i ].end );
				} else {
					merged.push( ranges[ i ] );
				}
			}

			var fragment = document.createDocumentFragment();
			var cursor = 0;

			merged.forEach( function ( range ) {
				if ( range.start > cursor ) {
					fragment.appendChild( document.createTextNode( text.slice( cursor, range.start ) ) );
				}

				var mark = document.createElement( 'mark' );
				mark.textContent = text.slice( range.start, range.end );
				fragment.appendChild( mark );

				cursor = range.end;
			} );

			if ( cursor < text.length ) {
				fragment.appendChild( document.createTextNode( text.slice( cursor ) ) );
			}

			node.textContent = '';
			node.appendChild( fragment );
		} );
	}

	function boot() {
		initTocState();
		initCurrentHighlight();
		initInPageAnchors();
		initSidebarToggle();
		initSearchShortcuts();
		initSearchHighlight();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
