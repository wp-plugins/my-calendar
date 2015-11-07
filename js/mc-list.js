(function ($) {
    'use strict';
    $(function () {
        $("li.mc-events").children().not(".event-date").hide();
        $("li.current-day").children().show();
        $(document).on("click", ".event-date button",
            function (e) {
                e.preventDefault();
                $(this).closest( '.mc-events' ).find( '.vevent' ).toggle().attr("tabindex", "-1").focus();
                var visible = $(this).closest( '.mc-events' ).find(".vevent").is(":visible");
                if (visible) {
                    $(this).closest( '.mc-events' ).find(".vevent").attr("aria-expanded", "true");
                } else {
                    $(this).closest( '.mc-events' ).find(".vevent").attr("aria-expanded", "false");
                }
            });
    });
}(jQuery));	