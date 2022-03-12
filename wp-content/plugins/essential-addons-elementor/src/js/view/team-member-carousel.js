
var TeamMemberCarouselHandler = function($scope, $) {
    var $carousel = $scope.find(".eael-tm-carousel").eq(0),
        $pagination =
            $carousel.data("pagination") !== undefined
                ? $carousel.data("pagination")
                : ".swiper-pagination",
        $arrow_next =
            $carousel.data("arrow-next") !== undefined
                ? $carousel.data("arrow-next")
                : ".swiper-button-next",
        $arrow_prev =
            $carousel.data("arrow-prev") !== undefined
                ? $carousel.data("arrow-prev")
                : ".swiper-button-prev",
        $items =
            $carousel.data("items") !== undefined ? $carousel.data("items") : 3,
        $items_tablet =
            $carousel.data("items-tablet") !== undefined
                ? $carousel.data("items-tablet")
                : 3,
        $items_mobile =
            $carousel.data("items-mobile") !== undefined
                ? $carousel.data("items-mobile")
                : 3,
        $margin =
            $carousel.data("margin") !== undefined
                ? $carousel.data("margin")
                : 10,
        $margin_tablet =
            $carousel.data("margin-tablet") !== undefined
                ? $carousel.data("margin-tablet")
                : 10,
        $margin_mobile =
            $carousel.data("margin-mobile") !== undefined
                ? $carousel.data("margin-mobile")
                : 10,
        $speed =
            $carousel.data("speed") !== undefined
                ? $carousel.data("speed")
                : 400,
        $autoplay =
            $carousel.data("autoplay") !== undefined
                ? $carousel.data("autoplay")
                : 999999,
        $loop =
            $carousel.data("loop") !== undefined ? $carousel.data("loop") : 0,
        $grab_cursor =
            $carousel.data("grab-cursor") !== undefined
                ? $carousel.data("grab-cursor")
                : 0,
        $data_id =
            $carousel.data("id") !== undefined ? $carousel.data("id") : "",
        $pause_on_hover =
            $carousel.data("pause-on-hover") !== undefined
                ? $carousel.data("pause-on-hover")
                : "",
        $slider_options = {
            direction: "horizontal",
            speed: $speed,
            grabCursor: $grab_cursor,
            loop: $loop,
            observer: true,
			observeParents: true,
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
            breakpoints: {
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
            }
        };

    swiperLoader($carousel, $slider_options).then((TeamSlider)=>{
		if (0 == $autoplay) {
			TeamSlider.autoplay.stop();
		}

		if ($pause_on_hover && $autoplay !== 0) {
			$carousel.on("mouseenter", function() {
				TeamSlider.autoplay.stop();
			});
			$carousel.on("mouseleave", function() {
				TeamSlider.autoplay.start();
			});
		}

		TeamSlider.update();
	});


    var $tabContainer = $('.eael-advance-tabs'),
		nav = $tabContainer.find('.eael-tabs-nav li'),
		tabContent = $tabContainer.find('.eael-tabs-content > div');

	
	nav.on('click', function() {
		var currentContent = tabContent.eq($(this).index()),
			sliderExist = $(currentContent).find('.swiper-container-wrap.eael-team-member-carousel-wrap');
		if(sliderExist.length) {
			swiperLoader($carousel, $slider_options);
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
        "frontend/element_ready/eael-team-member-carousel.default",
        TeamMemberCarouselHandler
    );
});
