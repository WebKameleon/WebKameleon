function KamGallery(type, element_id)
{
    var dialog = jQueryKam("#gallery_dialog");
    if (dialog.length == 0) {
        dialog = jQueryKam("<iframe></iframe>").attr("id", "gallery_dialog").appendTo("body").dialog({
            autoOpen : false,
            modal : true,
            width : 900,
            height : 560,
            title : "Menadżer plików",
            resizable : true
        }).bind("dialogclose", function (e) {
            jQueryKam(this).attr("src", "");
//            jQueryKam("body").unbind("gallery_file_selected");
        });
    }
    if (element_id) {
        jQueryKam("body").one("gallery_file_selected", function (e, file) {
            jQueryKam("#" + element_id).val(file.url.replace(file.baseUrl, "")).trigger("change", [ file ]);
            dialog.dialog("close");
        });
    }
    dialog.one("dialogopen", function (e) {
        jQueryKam(this).css("width", "98%").attr("src", KAM_ROOT + "gallery?type=" + type);
    }).dialog("open");
}

jQueryKam(function ($) {
    $("[gallery]").each(function(){
        var type        = $(this).attr("gallery"),
            element_id  = $(this).attr("id");

        if (element_id == null) {
            $(this).attr("id", element_id = "gallery_" + $("[id][gallery]").length);
        }

        if ($(this).is("input")) {
            var button = $(this).siblings(".km_gallery_button");
            if (button.length == 0) {
                button = $("<a></a>").addClass("km_gallery_button").attr("href", "#").insertAfter(this);
            }
            button.addClass("km_gallery_button_" + type).on("click", function(e){
                KamGallery(type, element_id);
                e.preventDefault();
                return false;
            });
        } else {
            $(this).on("click", function (e) {
                KamGallery(type);
            });
        }
    });
});