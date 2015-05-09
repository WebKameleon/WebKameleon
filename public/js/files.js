function KamFiles(type, callback, single_mode)
{
    var dialog = jQueryKam("#files_dialog");
    if (dialog.length == 0) {
        dialog = jQueryKam("<iframe></iframe>").attr("id", "files_dialog").appendTo("body").dialog({
            autoOpen : false,
            modal : true,
            width : 900,
            height : 450,
            title : tr('Choose file'),
            resizable : false
        }).bind("dialogclose", function (e) {
            jQueryKam(this).attr("src", "");
        });
    }

    if (typeof callback == "string") {
        callback = KamFilesElement(callback);
        single_mode = true;
    }

    if (typeof callback == "function") {
        var cb = function (e, files) {
            callback(files, dialog);
        }

        jQueryKam("body").one("picker_files", cb);
        dialog.one("dialogclose", function (e) {
            jQueryKam("body").off("picker_files", cb);
        });
    }

    dialog.one("dialogopen", function (e) {
        jQueryKam(this).css("width", "98%").attr("src", KAM_ROOT + "files?type=" + type + "&single_mode=" + (single_mode ? 1 : 0));
    }).dialog("open");

    return dialog;
}

function KamFilesElement (element_id) {
    return function (files, dialog) {
        var file = files[0];

        var URL = file.url.replace(file.baseUrl, "");
        if (URL[0] == "/")
            URL = URL.substring(1);

        jQueryKam("#" + element_id).val(URL).trigger("change", [ file ]);
        dialog.dialog("close");
    }
}

function CKEditorFiles (type, ui_element) {
    var input = jQueryKam("#" + ui_element.domId).find("input").css("width", "95%");

    jQueryKam("<a></a>").addClass("km_gallery_button").attr("href", "#").addClass("km_gallery_button_" + type).on("click", function (e) {
        KamFiles(type, function (files, dialog) {
            var file = files[0];

            var URL = file.url;
            if (URL[0] == "/")
                URL = URL.substring(1);

            input.val(URL).trigger("change", [ file ]);
            dialog.dialog("close");

            if (ui_element.onChange)
                ui_element.onChange.call(ui_element);
        }, true);
        e.preventDefault();
        return false;
    }).insertAfter(input);
}

jQueryKam(function ($) {
    $("[files]").each(function(){
        var type        = $(this).attr("files"),
            element_id  = $(this).attr("id");

        if (element_id == null) {
            $(this).attr("id", element_id = "files_" + $("[id][files]").length);
        }

        if ($(this).is("input")) {
            var button = $(this).siblings(".km_gallery_button");
            if (button.length == 0) {
                button = $("<a></a>").addClass("km_gallery_button").attr("href", "#").insertAfter(this);
            }
            button.addClass("km_gallery_button_" + type).on("click", function(e){
                KamFiles(type, element_id, true);
                e.preventDefault();
                return false;
            });
        } else {
            $(this).on("click", function (e) {
                KamFiles(type);
            });
        }
    });
});
