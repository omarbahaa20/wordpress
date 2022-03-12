
var PostCarouselHandler = function($scope, $) {
	var $postCarousel = $scope.find(".eael-post-carousel").eq(0),
		$autoplay =
			$postCarousel.data("autoplay") !== undefined
				? $postCarousel.data("autoplay")
				: 999999,
		$pagination =
			$postCarousel.data("pagination") !== undefined
				? $postCarousel.data("pagination")
				: ".swiper-pagination",
		$arrow_next =
			$postCarousel.data("arrow-next") !== undefined
				? $postCarousel.data("arrow-next")
				: ".swiper-button-next",
		$arrow_prev =
			$postCarousel.data("arrow-prev") !== undefined
				? $postCarousel.data("arrow-prev")
				: ".swiper-button-prev",
		$items =
			$postCarousel.data("items") !== undefined
				? $postCarousel.data("items")
				: 3,
		$items_tablet =
			$postCarousel.data("items-tablet") !== undefined
				? $postCarousel.data("items-tablet")
				: 3,
		$items_mobile =
			$postCarousel.data("items-mobile") !== undefined
				? $postCarousel.data("items-mobile")
				: 3,
		$margin =
			$postCarousel.data("margin") !== undefined
				? $postCarousel.data("margin")
				: 10,
		$margin_tablet =
			$postCarousel.data("margin-tablet") !== undefined
				? $postCarousel.data("margin-tablet")
				: 10,
		$margin_mobile =
			$postCarousel.data("margin-mobile") !== undefined
				? $postCarousel.data("margin-mobile")
				: 10,
		$effect =
			$postCarousel.data("effect") !== undefined
				? $postCarousel.data("effect")
				: "slide",
		$speed =
			$postCarousel.data("speed") !== undefined
				? $postCarousel.data("speed")
				: 400,
		$loop =
			$postCarousel.data("loop") !== undefined
				? $postCarousel.data("loop")
				: 0,
		$grab_cursor =
			$postCarousel.data("grab-cursor") !== undefined
				? $postCarousel.data("grab-cursor")
				: 0,
		$pause_on_hover =
			$postCarousel.data("pause-on-hover") !== undefined
				? $postCarousel.data("pause-on-hover")
				: "",
		$centeredSlides = $effect == "coverflow" ? true : false;

	var $carouselOptions = {
		direction: "horizontal",
		speed: $speed,
		effect: $effect,
		centeredSlides: $centeredSlides,
		grabCursor: $grab_cursor,
		autoHeight: true,
		loop: $loop,
		autoplay: {
			delay: $autoplay
		},
		pagination: {
			el: $pagination,
			clickable: true
		},
		navigation: {
			nextEl: $arrow_next,
			prevEl: $arrow_prev
		}
	};

	if($effect === 'slide' || $effect === 'coverflow') {
		$carouselOptions.breakpoints = {
			1024: {
				slidesPerView: $items,
				spaceBetween: $margin
			},
			768: {
				slidesPerView: $items_tablet,
				spaceBetween: $margin_tablet
			},
			320: {
				slidesPerView: $items_mobile,
				spaceBetween: $margin_mobile
			}
		};
	}else {
		$carouselOptions.items = 1;
	}



	swiperLoader($postCarousel, $carouselOptions).then((eaelPostCarousel)=>{
		if ($autoplay === 0) {
			eaelPostCarousel.autoplay.stop();
		}

		if ($pause_on_hover && $autoplay !== 0) {
			$postCarousel.on("mouseenter", function() {
				eaelPostCarousel.autoplay.stop();
			});
			$postCarousel.on("mouseleave", function() {
				eaelPostCarousel.autoplay.start();
			});
		}
	});

	var $tabContainer = $('.eael-advance-tabs'),
		nav = $tabContainer.find('.eael-tabs-nav li'),
		tabContent = $tabContainer.find('.eael-tabs-content > div');
	nav.on('click', function() {
		var currentContent = tabContent.eq($(this).index()),
			sliderExist = $(currentContent).find('.swiper-container-wrap.eael-post-carousel-wrap');
		if(sliderExist.length) {
			swiperLoader($postCarousel, $carouselOptions);
		}
	});
};

const swiperLoader = (swiperElement, swiperConfig) => {
	if ( 'undefined' === typeof Swiper ) {
		const asyncSwiper = elementorFrontend.utils.swiper;
		return new asyncSwiper( swiperElement, swiperConfig ).then( ( newSwiperInstance ) => {
			return  newSwiperInstance;
		} );
	} else {
		return swiperPromise( swiperElement, swiperConfig );
	}
}

const swiperPromise =  (swiperElement, swiperConfig) => {
	return new Promise((resolve, reject) => {
		const swiperInstance =  new Swiper( swiperElement, swiperConfig );
		resolve( swiperInstance );
	});
}

jQuery(window).on("elementor/frontend/init", function() {
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/eael-post-carousel.default",
		PostCarouselHandler
	);
});
