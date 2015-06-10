jQueryKam(function ($) {

    var km_setup_load = function () {
        km_preloader_show();
        $("#km_setup_template").empty();
        $.getJSON(KAM_ROOT + "ajax/setup_properties", function (data) {
            km_preloader_hide();
 
            $.each(data.templates_list, function (k, v) {
                var option = $("<option></option>").val(v).text(v.split("/").pop());
                if (v == data.template) {
                    option.attr("selected", "selected");
                }
                option.appendTo("#km_setup_template");
            });
            if (data.templates_list.length==0) $("#km_setup_template").remove();
            delete data.template;
            delete data.templates_list;

            delete data.analitycs;
            delete data.analitycs_list;

            $.each(data, function (k, v) {
                if (k!='mourning' && $("#km_setup_" + k).length) {
                    $("#km_setup_" + k).val(v);
                }
            });
            if (typeof(data.mourning)!='undefined') if (data.mourning=='1') $('#km_setup_mourning').prop('checked',true);
        });
    }

    var km_setup_properties = function () {
        var dialog = $("#km_setup_properties_dialog");
        if (dialog.hasClass("km_setup_properties_dialog") == false) {
            dialog.dialog({
                autoOpen : false,
                modal: false,
                width: 600,
                title : tr("Setup properties"),
                buttons: [
                    {
                        text: tr("Cancel"),
                        click: function () {
                            dialog.dialog("close");
                        }
                    },
                    {
                        text: tr("Save changes"),
                        click: function () {
                            var template = $("#km_setup_template").val(),
                                logo     = $("#km_setup_logo").val();

                            $("#km_setup_properties_form").submit();
                        }
                    }
                ]
            }).addClass("km_setup_properties_dialog");
            km_setup_load();
        }
        dialog.dialog("open");
    }

    var km_setup_analytics = function () {
        km_preloader_show();
        $("#km_setup_analytics_list").empty();
        $.getJSON(KAM_ROOT + "ajax/setup_list_analytics", function (data) {
            km_preloader_hide();
            
            if (data.status==0) {
                location.href = KAM_ROOT+"scopes/analytics?setreferpage="+km_infos["page"];
            }
            
            if (data.analytics_list.length) {
                var analytics = $("#km_setup_analytics").val();
                
                $.each(data.analytics_list, function (k, v) {
                    var option = $("<option></option>").val(v.id).text(v.name + " [" + v.websiteUrl + "]");
                    if (v.id == analytics) {
                        option.attr("selected", "selected");
                    }
                    option.appendTo("#km_setup_analytics_list");
                });
                $("#km_setup_analytics_dialog li").hide().filter("[rel='list']").show();
                $("#km_setup_analytics_dialog").dialog({
                    modal: true,
                    width: 500,
                    title : tr("Setup analytics"),
                    buttons: [
                        {
                            text: tr("Cancel"),
                            click: function () {
                                $(this).dialog("close");
                            }
                        },
                        {
                            text: tr("Choose"),
                            click: function () {
                                $("#km_setup_analytics").val(
                                    $("#km_setup_analytics_list").val()
                                );
                                $(this).dialog("close");
                            }
                        }
                    ]
                });
            } else {
                $("#km_setup_analytics_dialog li").hide().filter("[rel='empty']").show();
                $("#km_setup_analytics_dialog").dialog({
                    modal: true,
                    width: 540,
                    title : tr("Setup analytics"),
                    buttons: [
                        {
                            text: tr("Cancel"),
                            click: function () {
                                $(this).dialog("close");
                            }
                        }
                    ]
                });
            }
        });
    }

    $("#km_setup_properties").on("click", km_setup_properties);
    $("#km_setup_analytics_load").on("click", km_setup_analytics);

    $("#km_setup_export_local").on("click", function (e) {
        var href = $(this).attr("href");
        km_preloader_show(tr("Please wait"));
        $.get(href, function (data) {
            km_preloader_hide();
            if (data.status == 1 && data.filename) {
                document.location = KAM_ROOT + "wizard/export/" + data.filename;
            } else {
                KamDialog(data.error || tr("Error exporting template"));
            }
        });
        return false;
    });

    $("#km_setup_export_drive").on("click", function (e) {
        var href = $(this).attr("href");
        km_preloader_show(tr("Please wait"));
        $.get(href, function (data) {
            km_preloader_hide();
            if (data.status == 1) {
                KamDialog(tr("Service exported to Google Drive"));
            } else {
                KamDialog(data.error || tr("Error exporting service"));
            }
        });
        return false;
    });

    $("#menu_copy").on("click", function (e) {
        var href = $(this).attr("href");
        km_preloader_show(tr("Please wait"));
        $.post(href, function (data) {
            km_preloader_hide();
            if (data.status == 1) {
                KamDialog(tr("Template copied"));
                document.location.reload();
            } else {
                KamDialog(data.error || tr("Error copying template"));
            }
        }, "json");
        return false;
    });
});