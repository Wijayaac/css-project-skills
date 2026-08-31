/**
 * Wire header nav buttons to a .container-swiper-offset loop carousel.
 * Buttons: .swiper-offset-prev / .swiper-offset-next inside the same section.
 */
( function () {
	'use strict';

	function bindNav( scope ) {
		const container = scope.closest( '.container-swiper-offset' ) || scope;
		if ( ! container.classList.contains( 'container-swiper-offset' ) ) {
			return;
		}

		const swiperEl = scope.querySelector( '.swiper' );
		if ( ! swiperEl ) {
			return;
		}

		const connect = function () {
			const swiper = swiperEl.swiper;
			if ( ! swiper ) {
				return false;
			}

			const prevBtn = container.querySelector( '.swiper-offset-prev' );
			const nextBtn = container.querySelector( '.swiper-offset-next' );

			if ( prevBtn && ! prevBtn.dataset.swiperOffsetBound ) {
				prevBtn.dataset.swiperOffsetBound = '1';
				prevBtn.addEventListener( 'click', function () {
					swiper.slidePrev();
				} );
			}

			if ( nextBtn && ! nextBtn.dataset.swiperOffsetBound ) {
				nextBtn.dataset.swiperOffsetBound = '1';
				nextBtn.addEventListener( 'click', function () {
					swiper.slideNext();
				} );
			}

			return true;
		};

		if ( connect() ) {
			return;
		}

		const observer = new MutationObserver( function () {
			if ( connect() ) {
				observer.disconnect();
			}
		} );

		observer.observe( swiperEl, {
			attributes: true,
			attributeFilter: [ 'class' ],
		} );

		window.setTimeout( function () {
			observer.disconnect();
			connect();
		}, 3000 );
	}

	function initElementor() {
		if (
			typeof elementorFrontend === 'undefined' ||
			! elementorFrontend.hooks
		) {
			return;
		}

		elementorFrontend.hooks.addAction(
			'frontend/element_ready/loop-carousel.post',
			function ( $scope ) {
				bindNav( $scope[ 0 ] );
			}
		);
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initElementor );
	} else {
		initElementor();
	}

	window.addEventListener( 'elementor/frontend/init', initElementor );
} )();
