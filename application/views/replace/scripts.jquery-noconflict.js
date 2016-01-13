
        var jQueryKam = $.noConflict(noConflictParam);
            
        jQueryKam(function ($) {
            if ($("[fancybox]").length) {
                if (typeof $.fancybox == "undefined") {
                    $("<link />").attr({
                        "type" : "type/css",
                        "rel"  : "stylesheet",
                        "href" : noConflictPath+"/widgets/common/fancybox2/jquery.fancybox.css"
                    }).appendTo("head");
                    $.getScript(noConflictPath+"/widgets/common/fancybox2/jquery.fancybox.pack.js", function () {
                        $("[fancybox]").fancybox();
                    });
                } else {
                    $("[fancybox]").fancybox();
                }
            }
        });
      