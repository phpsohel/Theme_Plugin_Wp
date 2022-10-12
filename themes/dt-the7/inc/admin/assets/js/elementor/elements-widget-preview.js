(function ($) {

    /**
     * @param $scope The Widget wrapper element as a jQuery element
     * @param $ The jQuery alias
     */
    var the7ElementsWidgetHandler = function ($scope, $) {
        var precessEffects = function ($atoms, instant) {
            var k = 1;

            var $itemsToAnimate = $atoms.filter(function () {
                var $this = $(this);

                return !$this.hasClass("shown") && !$this.hasClass("animation-triggered");
            }).each(function () {
                var $this = $(this);
                var timeout = 200;
                if (!instant && dtGlobals.isInViewport($this) && !$this.hasClass("hidden")) {
                    timeout = 100 * k++;
                }

                $this.addClass("animation-triggered");
                setTimeout(function () {
                    $this.removeClass("animation-triggered").addClass("shown");
                }, timeout);
            });
        }

        var calculateColumns = function ($dataContainer, $isoContainer) {
            var contWidth = parseInt($dataContainer.attr("data-width"));
            var contNum = parseInt($dataContainer.attr("data-columns"));
            var contPadding = parseInt($dataContainer.attr("data-padding"));

            $isoContainer.calculateColumns(contWidth, contNum, contPadding, null, null, null, null, "px", window.the7GetElementorMasonryColumnsConfig);
        }

        var calculateColumnsOnResize = function () {
            calculateColumns($dataAttrContainer, $dataAttrContainer.find(".iso-container"));
        }

        var $dataAttrContainer = $scope.find(".portfolio-shortcode");
        if (!$dataAttrContainer.length) {
            $dataAttrContainer = $scope.find(".products-shortcode");
        }

        if ($dataAttrContainer.hasClass("mode-masonry")) {
            //Masonry layout
            i = $scope.attr("data-id");
            var $isoContainer = $dataAttrContainer.find(".iso-container");

            $isoContainer.addClass("cont-id-" + i).attr("data-cont-id", i);
            jQuery(window).off("columnsReady");
            $isoContainer.off("columnsReady.The7Elements").one("columnsReady.The7Elements.IsoInit", function () {
                $isoContainer.IsoInitialisation(".iso-item", "masonry", 400);
                if ($dataAttrContainer.hasClass("jquery-filter")) {
                    window.the7ApplyMasonryJsFiltering($dataAttrContainer);
                }
            });

            $isoContainer.on("columnsReady.The7Elements.IsoLayout", function () {
                $(".preload-me", $isoContainer).heightHack();
                $isoContainer.isotope("layout");
            });

            $(window).off("debouncedresize.The7Elements", calculateColumnsOnResize).on("debouncedresize.The7Elements", calculateColumnsOnResize);

            $isoContainer.on("IsoLayout.The7Elements", function () {
                calculateColumns($dataAttrContainer, $dataAttrContainer.find(".iso-container"));
            });

            calculateColumns($dataAttrContainer, $isoContainer);

            setTimeout(function () {
                $isoContainer.isotope("layout");
            }, 600);
        } else if ($dataAttrContainer.hasClass("jquery-filter")) {
            if ($dataAttrContainer.hasClass("dt-css-grid-wrap")) {
                // Filter active item class handling since it's not included in filtrade.
                window.the7ApplyGeneralFilterHandlers($dataAttrContainer.find(".filter-categories"));
                // Filtrade filtering for css grid.
                window.the7ApplyMasonryWidgetCSSGridFiltering($dataAttrContainer.find(".dt-css-grid"));
            } else if ($dataAttrContainer.hasClass("mode-grid")) {
                // Isotope filtering.
                window.the7ApplyMasonryJsFiltering($dataAttrContainer);
            }
        }

        if ($dataAttrContainer.is(".content-rollover-layout-list:not(.disable-layout-hover)")) {
            $dataAttrContainer.find(".post-entry-wrapper").clickOverlayGradient();
        }

        precessEffects($dataAttrContainer.find(".wf-cell"), $dataAttrContainer.hasClass("loading-effect-none"));

        window.the7AddHovers($dataAttrContainer);
        window.the7AddDesktopHovers($dataAttrContainer);
        window.the7ApplyGeneralOrderingSwitchEffects($dataAttrContainer.find(".filter"));

        // Stub anchors.
        $dataAttrContainer.find("a").on("click", function (e) {
            e.preventDefault();

            return false;
        });
    };

    // Make sure you run this code under Elementor.
    $(window).on("elementor/frontend/init", function () {
        var onEditSettingsTimeout;

        elementorFrontend.hooks.addAction("frontend/element_ready/the7_elements.default", the7ElementsWidgetHandler);
        elementorFrontend.hooks.addAction("frontend/element_ready/the7-elements-woo-masonry.default", the7ElementsWidgetHandler);

        elementorEditorAddOnChangeHandler("the7_elements:overlay_background_background", toggleDefaultImageOverlay);
        elementorEditorAddOnChangeHandler("the7_elements:overlay_hover_background_background", toggleDefaultImageOverlay);
        elementorEditorAddOnChangeHandler("the7_elements", function (controlView, widgetView) {
            if ($.isEmptyObject(controlView.model.attributes.selectors)) {
                return;
            }

            if (widgetView.model.getSetting("layout") !== "masonry") {
                return;
            }

            clearTimeout(onEditSettingsTimeout);
            onEditSettingsTimeout = setTimeout(function () {
                window.jQuery(widgetView.$el).find(".iso-container").isotope("layout");
            }, 800);
        });
    });

    function elementorEditorAddOnChangeHandler(widgetType, handler) {
        widgetType = widgetType ? ":" + widgetType : "";
        elementor.channels.editor.on("change" + widgetType, handler);
    }

    function toggleDefaultImageOverlay(controlView, widgetView) {
        if (widgetView.model.getSetting("overlay_background_background") || widgetView.model.getSetting("overlay_hover_background_background")) {
            widgetView.$el.find(".the7-elementor-widget").removeClass("enable-bg-rollover");
        } else {
            widgetView.$el.find(".the7-elementor-widget").addClass("enable-bg-rollover");
        }
    }

})(jQuery);