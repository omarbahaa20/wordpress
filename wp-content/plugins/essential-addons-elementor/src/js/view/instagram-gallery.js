jQuery(window).on("elementor/frontend/init", function() {
    let InstagramGallery = function($scope, $) {
        if (!isEditMode) {
            let $instagram_gallery = $(".eael-instafeed", $scope).isotope({
                itemSelector: ".eael-instafeed-item",
                percentPosition: true,
                columnWidth: ".eael-instafeed-item"
            });

            $instagram_gallery.imagesLoaded().progress(function() {
                $instagram_gallery.isotope("layout");
            });
        }

        // ajax load more
        $(".eael-load-more-button", $scope).on("click", function(e) {
            e.preventDefault();
            let $this = $(this),
                $LoaderSpan = $("span", $this),
                $text = $LoaderSpan.html(),
                $widget_id = $this.data("widget-id"),
                $post_id = $this.data("post-id"),
                $settings = $this.data("settings"),
                $page = parseInt($this.data("page"), 10);
            // update load moer button
            $this.addClass("button--loading");
            $LoaderSpan.html(localize.i18n.loading);

            $.ajax({
                url: localize.ajaxurl,
                type: "post",
                data: {
                    action: "instafeed_load_more",
                    security: localize.nonce,
                    page: $page,
                    post_id: $post_id,
                    widget_id: $widget_id,
                    settings: $settings
                },
                success: function(response) {
                    let $html = $(response.html);
                    // append items
                    let $instagram_gallery = $(".eael-instafeed", $scope).isotope();
                    $(".eael-instafeed", $scope).append($html);
                    $instagram_gallery.isotope("appended", $html);
                    $instagram_gallery.imagesLoaded().progress(function() {
                        $instagram_gallery.isotope("layout");
                    });

                    // update load more button
                    if (response.num_pages > $page) {
                        $page++;
                        $this.data("page", $page);
                        $this.removeClass("button--loading");
                        $LoaderSpan.html($text);
                    } else {
                        $this.remove();
                    }
                },
                error: function() {}
            });
        });
    };
    elementorFrontend.hooks.addAction(
        "frontend/element_ready/eael-instafeed.default",
        InstagramGallery
    );
});
