var AdvancedMenu = function ($scope, $) {
    var $indicator_class = $('.eael-advanced-menu-container', $scope).data(
        'indicator-class'
    )
    var $dropdown_indicator_class = $(
        '.eael-advanced-menu-container',
        $scope
    ).data('dropdown-indicator-class')
    var $horizontal = $('.eael-advanced-menu', $scope).hasClass(
        'eael-advanced-menu-horizontal'
    )

    var $fullWidth = $('.eael-advanced-menu--stretch');

    if ($horizontal) {
        // insert indicator
        $('.eael-advanced-menu > li.menu-item-has-children', $scope).each(
            function () {
                $('> a', $(this)).append(
                    '<span class="' + $indicator_class + '"></span>'
                )
            }
        )
        $('.eael-advanced-menu > li ul li.menu-item-has-children', $scope).each(
            function () {
                $('> a', $(this)).append(
                    '<span class="' + $dropdown_indicator_class + '"></span>'
                )
            }
        )

        // insert responsive menu toggle, text
        $('.eael-advanced-menu-horizontal', $scope)
            .before('<span class="eael-advanced-menu-toggle-text"></span>')
            .after(
                '<button class="eael-advanced-menu-toggle"><span class="eicon-menu-bar"></span></button>'
            )



        // responsive menu slide
        $('.eael-advanced-menu-container', $scope).on(
            'click',
            '.eael-advanced-menu-toggle',
            function (e) {
                e.preventDefault()
                const $siblings = $(this).siblings('nav').children('.eael-advanced-menu-horizontal');

                $siblings.css('display') == 'none'
                    ? $siblings.slideDown(300)
                    : $siblings.slideUp(300)
            }
        )

        // clear responsive props
        $(window).on('resize load', function () {
            if (window.matchMedia('(max-width: 991px)').matches) {
                $('.eael-advanced-menu-horizontal', $scope).addClass(
                    'eael-advanced-menu-responsive'
                )
                $('.eael-advanced-menu-toggle-text', $scope).text(
                    $(
                        '.eael-advanced-menu-horizontal .current-menu-item a',
                        $scope
                    )
                        .eq(0)
                        .text()
                )

                if ($fullWidth) {
                    const css = {}
                    if(!$('.eael-advanced-menu-horizontal', $scope).parent().hasClass('eael-nav-menu-wrapper')){
                        $('.eael-advanced-menu-horizontal', $scope).wrap('<nav class="eael-nav-menu-wrapper"></nav>');
                    }
                    const $navMenu = $(".eael-advanced-menu-container nav",$scope);
                    menu_size_reset($navMenu);


                    if($fullWidth.length>0){
                        css.width = parseFloat($('.elementor').width()) + 'px';
                        css.left = -parseFloat($navMenu.offset().left) + 'px';
                        css.position = 'absolute';
                    }
                    $navMenu.css(css);
                }
            } else {
                $('.eael-advanced-menu-horizontal', $scope).removeClass(
                    'eael-advanced-menu-responsive'
                )
                $(
                    '.eael-advanced-menu-horizontal, .eael-advanced-menu-horizontal ul',
                    $scope
                ).css('display', '')
                $(".eael-advanced-menu-container nav",$scope).removeAttr( 'style' );;
            }
        })
    }

    function menu_size_reset(selector){
        const css = {};
        css.width = '';
        css.left = '';
        css.position = 'inherit';
        selector.css(css);
    }

    $('.eael-advanced-menu > li.menu-item-has-children', $scope).each(
        function () {
            // indicator position
            var $height = parseInt($('a', this).css('line-height')) / 2
            $(this).append(
                '<span class="eael-advanced-menu-indicator ' +
                    $indicator_class +
                    '" style="top:' +
                    $height +
                    'px"></span>'
            )

            // if current, keep indicator open
            // $(this).hasClass('current-menu-ancestor') ? $(this).addClass('eael-advanced-menu-indicator-open') : ''
        }
    )

    $('.eael-advanced-menu > li ul li.menu-item-has-children', $scope).each(
        function (e) {
            // indicator position
            var $height = parseInt($('a', this).css('line-height')) / 2
            $(this).append(
                '<span class="eael-advanced-menu-indicator ' +
                    $dropdown_indicator_class +
                    '" style="top:' +
                    $height +
                    'px"></span>'
            )

            // if current, keep indicator open
            // $(this).hasClass('current-menu-ancestor') ? $(this).addClass('eael-advanced-menu-indicator-open') : ''
        }
    )

    // menu indent
    $(
        '.eael-advanced-menu-dropdown-align-left .eael-advanced-menu-vertical li.menu-item-has-children'
    ).each(function () {
        var $padding_left = parseInt($('a', $(this)).css('padding-left'))

        $('ul li a', this).css({
            'padding-left': $padding_left + 20 + 'px',
        })
    })

    $(
        '.eael-advanced-menu-dropdown-align-right .eael-advanced-menu-vertical li.menu-item-has-children'
    ).each(function () {
        var $padding_right = parseInt($('a', $(this)).css('padding-right'))

        $('ul li a', this).css({
            'padding-right': $padding_right + 20 + 'px',
        })
    })

    $(
        '.eael-advanced-menu-vertical li.menu-item-has-children.current-menu-ancestor .eael-advanced-menu-indicator'
    ).each(function () {
        // ToDo Alternate way: check eael_advanced_menu_submenu_expand settings and expand if enabled
        let isMenuOpen = $(this).siblings('ul.sub-menu').css('display');
        if(isMenuOpen !== 'none') {
            $(this).toggleClass('eael-advanced-menu-indicator-open');
        }
    });

    // menu dropdown toggle
    $('.eael-advanced-menu', $scope).on(
        'click',
        '.eael-advanced-menu-indicator',
        function (e) {
            e.preventDefault()
            $(this).toggleClass('eael-advanced-menu-indicator-open')
            $(this).hasClass('eael-advanced-menu-indicator-open')
                ? $(this).siblings('ul').slideDown(300)
                : $(this).siblings('ul').slideUp(300)
        }
    )
    // main menu toggle
    $('.eael-advanced-menu-container', $scope).on(
        'click',
        '.eael-advanced-menu-responsive li a',
        function (e) {
            $(this).parents('.eael-advanced-menu-horizontal').slideUp(300)
        }
    )
}

jQuery(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.default',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-one',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-two',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-three',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-four',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-five',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-six',
        AdvancedMenu
    )
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/eael-advanced-menu.skin-seven',
        AdvancedMenu
    )
})
