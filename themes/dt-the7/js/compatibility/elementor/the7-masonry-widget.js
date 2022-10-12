window.the7GetElementorMasonryColumnsConfig = function ($container) {
    var $dataAttrContainer = $container.parent().hasClass("mode-masonry") ? $container.parent() : $container;
    var containerWidth = $container.width() - 1;
    var breakpoints = elementorFrontend.config.breakpoints;
    var columnsNum = "";
    var singleWidth = "";
    var doubleWidth = "";

    if (Modernizr.mq("all and (min-width:" + (dtLocal.elementor.settings.container_width + 1) + "px)")) {
        columnsNum = parseInt($dataAttrContainer.attr("data-wide-desktop-columns-num"));

        return {
            singleWidth: Math.floor(containerWidth / columnsNum) + "px",
            doubleWidth: Math.floor(containerWidth / columnsNum) * 2 + "px",
            columnsNum: columnsNum
        };
    }

    var modernizrMqPoints = [
        {
            breakpoint: breakpoints.xl,
            columns: parseInt($dataAttrContainer.attr("data-desktop-columns-num"))
        },
        {
            breakpoint: breakpoints.lg,
            columns: parseInt($dataAttrContainer.attr("data-tablet-columns-num"))
        },
        {
            breakpoint: breakpoints.md,
            columns: parseInt($dataAttrContainer.attr("data-mobile-columns-num"))
        }
    ];

    modernizrMqPoints.forEach(function (mgPoint) {
        if (Modernizr.mq("all and (max-width:" + (mgPoint.breakpoint - 1) + "px)")) {
            columnsNum = mgPoint.columns;
            singleWidth = Math.floor(containerWidth / columnsNum) + "px";
            doubleWidth = Math.floor(containerWidth / columnsNum) * 2 + "px";

            return false;
        }
    });

    return {
        singleWidth: singleWidth,
        doubleWidth: doubleWidth,
        columnsNum: columnsNum
    };
}

window.the7ApplyMasonryWidgetCSSGridFiltering = function($container) {
    var config = the7ShortcodesFilterConfig($container);

    config.pagerClass = "page filter-item";
    config.previousButtonLabel = "<i class=\"dt-icon-the7-arrow-35-1\" aria-hidden=\"true\"></i>";
    config.nextButtonLabel = "<i class=\"dt-icon-the7-arrow-35-2\" aria-hidden=\"true\"></i>";

    $container.shortcodesFilter(config);
}

jQuery(function ($) {
    $(".elementor-widget-the7_elements .iso-container").each(function (i) {
        var $container = $(this);

        the7ApplyColumns(i + 100, $container, the7GetElementorMasonryColumnsConfig);
        $container.one("columnsReady", function () {
            $container.isotope("layout");
        });
    });
    $(".elementor-widget-the7_elements .jquery-filter .dt-css-grid").each(function () {
        the7ApplyMasonryWidgetCSSGridFiltering($(this));
    });
});