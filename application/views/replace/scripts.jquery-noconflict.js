
        var jQueryKam = $.noConflict(noConflictParam);
            
        jQueryKam(function ($) {
            if ($("[fancybox]").length) {
                $.ajaxSetup({
                    cache: true
                }); 
                if (typeof $.fancybox == "undefined") {
                    
                    var href=noConflictPath+"/widgets/common/fancybox2/jquery.fancybox.css";
                    var css='<link rel="stylesheet" type="text/css" href="'+href+'"/>';
                    $('head').append(css);
                   
                    $.getScript(noConflictPath+"/widgets/common/fancybox2/jquery.fancybox.pack.js", function () {
                        $("[fancybox]").fancybox();
                    });
                } else {
                    $("[fancybox]").fancybox();
                }
            }
        });
      