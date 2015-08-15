(function ($) {
    'use strict';
    $(function () {
        $(".calendar-event").children().not(".event-title").hide();
        $(document).on("click", ".calendar-event .event-title",
            function (e) {
                e.preventDefault(); // remove line if you are using a link in the event title
				var current_date = $(this).parent().children();
                $(this).parent().children().not(".event-title").toggle().attr("tabindex", "-1");
				$(this).parent().focus();
				$(".calendar-event").children().not(".event-title").not( current_date ).hide();
            });
        $(document).on("click", ".calendar-event .close",
            function (e) {
                e.preventDefault();
                $(this).closest(".vevent").find(".event-title a").focus();
                $(this).closest("div.details").toggle();
            });
    });
}(jQuery));	