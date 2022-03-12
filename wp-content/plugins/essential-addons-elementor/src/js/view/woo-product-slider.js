ea.hooks.addAction("init", "ea", () => {
	const wooProductSlider = function ($scope, $) {
		ea.hooks.doAction("quickViewAddMarkup",$scope,$);
		var $wooProductSlider = $scope.find(".eael-woo-product-slider").eq(0),
			$type = $wooProductSlider.data("type"),
			$autoplay =
				$wooProductSlider.data("autoplay") !== undefined
					? $wooProductSlider.data("autoplay")
					: 999999,
			$pagination =
				$wooProductSlider.data("pagination") !== undefined
					? $wooProductSlider.data("pagination")
					: ".swiper-pagination",
			$arrow_next =
				$wooProductSlider.data("arrow-next") !== undefined
					? $wooProductSlider.data("arrow-next")
					: ".swiper-button-next",
			$arrow_prev =
				$wooProductSlider.data("arrow-prev") !== undefined
					? $wooProductSlider.data("arrow-prev")
					: ".swiper-button-prev",
			$speed =
				$wooProductSlider.data("speed") !== undefined
					? $wooProductSlider.data("speed")
					: 400,
			$loop =
				$wooProductSlider.data("loop") !== undefined
					? $wooProductSlider.data("loop")
					: 0,
			$grab_cursor =
				$wooProductSlider.data("grab-cursor") !== undefined
					? $wooProductSlider.data("grab-cursor")
					: 0,
			$pause_on_hover =
				$wooProductSlider.data("pause-on-hover") !== undefined
					? $wooProductSlider.data("pause-on-hover")
					: "",
			$content_effect =
				$wooProductSlider.data("animation") !== undefined
					? $wooProductSlider.data("animation")
					: "zoomIn",
			$showEffect = $wooProductSlider.data("show-effect") !== undefined
				? $wooProductSlider.data("show-effect")
				: "";

		const $sliderOptions = {
			direction: "horizontal",
			speed: $speed,
			//effect: "slide",
			centeredSlides: true,
			grabCursor: $grab_cursor,
			autoHeight: true,
			loop: $loop,
			//slidesPerGroup: 3,
			loopedSlides: 3,
			autoplay: {
				delay: $autoplay,
				disableOnInteraction: false
			},
			pagination: {
				el: $pagination,
				clickable: true
			},
			navigation: {
				nextEl: $arrow_next,
				prevEl: $arrow_prev
			},
			slidesPerView: 1,
			spaceBetween: 30,
		};

		if ($showEffect === 'yes') {
			// $carouselOptions.slidesPerView = 'auto';
			$sliderOptions.on = {
				init: function () {
					$wooProductSlider.find('.swiper-slide-active .product-details-wrap').addClass('animate__animated' +
						' animate__'+$content_effect);
				},
				transitionStart: function() {
					$wooProductSlider.find('.product-details-wrap').removeClass('animate__animated animate__'+$content_effect);
				},
				transitionEnd: function(swiper) {
					$wooProductSlider.find('.swiper-slide-active .product-details-wrap').addClass('animate__animated' +
						' animate__'+$content_effect);
				}
			}
		}

		swiperLoader($wooProductSlider, $sliderOptions).then((eaelwooProductSlider) => {
			if ($autoplay === 0) {
				eaelwooProductSlider.autoplay.stop();
			}

			if ($pause_on_hover && $autoplay !== 0) {
				$wooProductSlider.on("mouseenter", function () {
					eaelwooProductSlider.autoplay.stop();
				});
				$wooProductSlider.on("mouseleave", function () {
					eaelwooProductSlider.autoplay.start();
				});
			}

			//gallery pagination
			const $paginationGallerySelector = $scope
			.find('.eael-woo-product-slider-container .eael-woo-product-slider-gallary-pagination')
			.eq(0)
			if ($paginationGallerySelector.length > 0) {
				swiperLoader($paginationGallerySelector, {
					spaceBetween: 20,
					centeredSlides: true,
					touchRatio: 0.2,
					slideToClickedSlide: true,
					loop: $loop,
					//slidesPerGroup: 1,
					loopedSlides: 3,
					slidesPerView: 3,
					freeMode: true,
					watchSlidesVisibility: true,
					watchSlidesProgress: true,
				}).then(($paginationGallerySlider) => {
					eaelwooProductSlider.controller.control = $paginationGallerySlider
					$paginationGallerySlider.controller.control = eaelwooProductSlider
				});
			}
		});
		ea.hooks.doAction("quickViewPopupViewInit",$scope,$);
		
		if (isEditMode) {
			$(".eael-product-image-wrap .woocommerce-product-gallery").css(
				"opacity",
				"1"
			);
		}

	};

	const swiperLoader = (swiperElement, swiperConfig) => {
		if ('undefined' === typeof Swiper) {
			const asyncSwiper = elementorFrontend.utils.swiper;
			return new asyncSwiper(swiperElement, swiperConfig).then((newSwiperInstance) => {
				return newSwiperInstance;
			});
		} else {
			return swiperPromise(swiperElement, swiperConfig);
		}
	}

	const swiperPromise = (swiperElement, swiperConfig) => {
		return new Promise((resolve, reject) => {
			const swiperInstance = new Swiper(swiperElement, swiperConfig);
			resolve(swiperInstance);
		});
	}

	elementorFrontend.hooks.addAction(
		"frontend/element_ready/eael-woo-product-slider.default",
		wooProductSlider
	);
});
