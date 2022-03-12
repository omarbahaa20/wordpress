var FlipCarousel = function($scope, $) {
    var $this = $(".eael-flip-carousel", $scope);

    var style = $this.data("style"),
        start = $this.data("start"),
        fadeIn = $this.data("fadein"),
        loop = $this.data("loop"),
        autoplay = $this.data("autoplay"),
        pauseOnHover = $this.data("pauseonhover"),
        spacing = $this.data("spacing"),
        click = $this.data("click"),
        scrollwheel = $this.data("scrollwheel"),
        touch = $this.data("touch"),
        buttons = $this.data("buttons");

        var buttonPrev = ($this.data("buttonprev"));
        var buttonNext = ($this.data("buttonnext"));
        var options = {
            style: style,
            start: start,
            fadeIn: fadeIn,
            loop: loop,
            autoplay: autoplay,
            pauseOnHover: pauseOnHover,
            spacing: spacing,
            click: click,
            scrollwheel: scrollwheel,
            tocuh: touch,
            buttons: buttons,
            buttonPrev: '',
            buttonNext: ''
        };
    if( (typeof buttonPrev) === 'object' ) {
        options.buttonPrev = '<span class="flip-custom-nav"><img class="eael-flip-carousel-svg-icon" src="' + buttonPrev.url + '" alt="' + buttonPrev.alt + '" /></div>';
    }else {
        options.buttonPrev = '<i class="flip-custom-nav ' + buttonPrev + '"></i>';
    }
    if( (typeof buttonNext) === 'object' ) {
        options.buttonNext = '<span class="flip-custom-nav"><img class="eael-flip-carousel-svg-icon" src="'+buttonNext.url+'" alt="'+buttonNext.alt+'" /></div>';
    }else {
        options.buttonNext = '<i class="flip-custom-nav ' + buttonNext + '"></i>';
    }

    $this.flipster(options);
};

jQuery(window).on("elementor/frontend/init", function() {
    elementorFrontend.hooks.addAction(
        "frontend/element_ready/eael-flip-carousel.default",
        FlipCarousel
    );
});
