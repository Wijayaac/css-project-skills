/**
 * Keep .carousel-faded slidesPerView in sync with CSS hero logic:
 * >1366 → 5 (hero = active+2, centeredSlides off)
 * 768–1366 → 3 (hero = active+1, centeredSlides off)
 * ≤767 → 1.3 (hero = active, centeredSlides on — side peeks)
 *
 * Elementor tablet settings alone often leave 5-up on small desktops,
 * which makes the “big” slide look off-center.
 */
( function () {
	'use strict';

	var BP_TABLET = 1366;
	var BP_MOBILE = 767;

	function configForWidth( width ) {
		if ( width <= BP_MOBILE ) {
			return { slidesPerView: 1.3, centeredSlides: true };
		}
		if ( width <= BP_TABLET ) {
			return { slidesPerView: 3, centeredSlides: false };
		}
		return { slidesPerView: 5, centeredSlides: false };
	}

	function applySlidesPerView( swiper ) {
		if ( ! swiper || ! swiper.params ) {
			return;
		}

		var next = configForWidth( window.innerWidth );
		var sameView =
			Number( swiper.params.slidesPerView ) === next.slidesPerView;
		var sameCenter = !! swiper.params.centeredSlides === next.centeredSlides;

		if ( sameView && sameCenter ) {
			return;
		}

		swiper.params.slidesPerView = next.slidesPerView;
		swiper.params.centeredSlides = next.centeredSlides;
		swiper.update();
	}

	function bindCarousel( scope ) {
		var root = scope.classList.contains( 'carousel-faded' )
			? scope
			: scope.closest( '.carousel-faded' );

		if ( ! root ) {
			return;
		}

		var swiperEl = root.querySelector( '.swiper' );
		if ( ! swiperEl ) {
			return;
		}

		var connect = function () {
			var swiper = swiperEl.swiper;
			if ( ! swiper ) {
				return false;
			}

			if ( swiperEl.dataset.carouselFadedBound === '1' ) {
				applySlidesPerView( swiper );
				return true;
			}

			swiperEl.dataset.carouselFadedBound = '1';
			applySlidesPerView( swiper );

			window.addEventListener(
				'resize',
				function () {
					applySlidesPerView( swiper );
				},
				{ passive: true }
			);

			return true;
		};

		if ( connect() ) {
			return;
		}

		var observer = new MutationObserver( function () {
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
			'frontend/element_ready/image-carousel.default',
			function ( $scope ) {
				bindCarousel( $scope[ 0 ] );
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
