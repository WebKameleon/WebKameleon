head.js(
    KAM_ROOT + "dhtmlxcalendar/dhtmlxcalendar.css",
    KAM_ROOT + "dhtmlxcalendar/skins/dhtmlxcalendar_dhx_skyblue.css",
    KAM_ROOT + "dhtmlxcalendar/dhtmlxcalendar.js",
    function () {
        jQueryKam(function ($) {
            $("input[datetimepicker], input[datepicker]").each(function () {
                var input  = this,
                    button = $(this).siblings(".km_datetimepicker_button"),
                    format = "%Y-%m-%d";

                if (button.length == 0) {
                    button = $("<a></a>").addClass("km_datetimepicker_button").attr("href", "#").insertAfter(input);
                }

                var myCalendar = new dhtmlXCalendarObject({
                    input  : input,
                    button : button[0]
                });

                myCalendar.loadUserLanguage("pl");
                myCalendar.setDateFormat(format);
                myCalendar.hideTime();

                if (this.getAttribute("datetimepicker")) {
                    myCalendar.setDateFormat(format = "%Y-%m-%d %H:%i");
                    myCalendar.attachEvent("onChange", function (date, state) {
                        input.value = myCalendar.getFormatedDate(format, date)
                    });
                    myCalendar.showTime();
                }
            });
        });
    }
);