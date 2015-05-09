function KamTree(node, element_id)
{
    var dialog = jQueryKam("#jstree_dialog");
    if (dialog.size() == 0) {
        dialog = jQueryKam("<iframe></iframe>").attr("id", "jstree_dialog").appendTo("body").dialog({
            autoOpen : false,
            modal : true,
            width : 900,
            height : 660,
            title : "Drzewko",
            resizable : false
        }).bind("dialogclose", function (e) {
            jQueryKam(this).attr("src", "");
            jQueryKam("body").unbind("tree_node_selected");
        });
    }
    if (element_id) {
        jQueryKam("body").one("tree_node_selected", function (e, metadata) {
            jQueryKam("#" + element_id).val(metadata.id).trigger("change");
            dialog.dialog("close");
        });
    }
    dialog.one("dialogopen", function (e) {
        if (element_id)
            node = jQueryKam("#" + element_id).val() || node;
        jQueryKam(this).css("width", "98%").attr("src", KAM_ROOT + "tree?node=" + (node || 0) + "&multi_langs=" + (jQueryKam("#" + element_id).attr("jsmultilangs") || 0));
    }).dialog("open");
}

jQueryKam(function ($) {
    $("[jstree]").each(function(){
        var node        = $(this).attr("jstree"),
            element_id  = $(this).attr("id");

        if (element_id == null) {
            $(this).attr("id", element_id = "jstree_" + $("[id][jstree]").length);
        }

        if ($(this).is("input")) {
            var button = $(this).siblings(".km_jstree_button");
            if (button.length == 0) {
                button = $("<a></a>").addClass("km_jstree_button").attr("href", "#").insertAfter(this);
            }
            button.on("click", function (e) {
                KamTree(node, element_id);
                e.preventDefault();
                return false;
            });
        } else {
            $(this).on("click", function (e) {
                KamTree(node);
            });
        }
    });
});