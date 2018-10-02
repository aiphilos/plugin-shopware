(function($) {
    $.subscribe('plugin/swSearch/onDataAttributes.aiPhilosSearch', function (event, $el, opts) {
        opts.searchDelay = 1000;
    });
})(jQuery);