head.js(
    KAM_ROOT + "jpicker/css/jPicker-1.1.6.min.css",
    KAM_ROOT + "jpicker/jpicker-1.1.6.min.js",
    function () {
        jQueryKam(function ($) {

            $("input[colorpicker]").each(function () {
                var input = this;
                $(input).attr("readonly", "readonly").jPicker({
                    window : {
                        effects : {
                            type : "fade"
                        },
                        position : {
                            x : "0px",
                            y : "center"
                        }
                    },
                    images : {
                        clientPath : KAM_ROOT + "jpicker/images/"
                    }
                }, function (color, context) {

                }, function (color, context) {
                    $(input).val("#" + color.val("hex"));
                }, function (color, context) {

                });
            });

            setTimeout(function () {
                $("input[colorpicker]").unbind("blur");
            }, 0);
        });
    }
)