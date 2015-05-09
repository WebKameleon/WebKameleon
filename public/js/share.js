jQueryKam(function ($) {
    var km_share_roles = {};

    $("#km_share_role_select ul li").each(function () {
        km_share_roles[$(this).attr("rel")] = $(this).text();
    });

    var km_share_get_role = function (user) {
        var role = null;
        $.each(km_share_roles, function (k, v) {
            if (role == null && user[k])
                role = {
                    rel : k, name : v
                };
        });
        return role || {
            rel : "unknown", name : "unknown"
        };
    }

    var km_share_list_add_user = function (user, is_god) {
        var role = km_share_get_role(user);
        var li = $("<li></li>").data("user", user).data("role", role).data("is_god", is_god);
        var div = $("<div></div>").addClass("km_share_user").appendTo(li);
        if (user.fullname) {
            $("<span></span>").addClass("km_share_name").text(user.fullname).appendTo(div);
        }
        $("<span></span>").addClass("km_share_email").text(user.email).attr("title", user.email).appendTo(div);
        var role_div = $("<div></div>").addClass("km_share_role").text(role.name).appendTo(li);
        if (is_god !== true) {
            role_div.html("<a>" + role.name + "</a>");
            $("<div></div>").addClass("km_share_delete").appendTo(li);
        }
        $("<div></div>").addClass("km_clean").appendTo(li);
        if (is_god !== true) {
            li.appendTo("#km_share_list");
        } else {
            li.prependTo("#km_share_list");
        }
    }

    var km_share_list_load = function () {
        $("#km_share_list").empty();
        km_preloader_show();
        $.getJSON(KAM_ROOT + "ajax/share_load", function (data) {
            km_preloader_hide();
            $.each(data.users, function (k, user) {
                km_share_list_add_user(user, user.username == data.owner);
            });
        });
    }

    var km_share_server = function () {
        var dialog = $("#km_share_dialog");
        if (dialog.hasClass("km_share_dialog") == false) {
            dialog.dialog({
                autoOpen : false,
                modal: true,
                width: 600,
                height: 500,
                title : tr("Share server"),
                buttons: [
                    {
                        text: tr("Ready"),
                        click: function () {
                            dialog.dialog("close");
                        }
                    }
                ]
            }).addClass("km_share_dialog");
            km_share_list_load();
        }
        dialog.dialog("open");
    }

    var km_share_json_response = function (data) {
        km_preloader_hide();
        if (data.status == 1) {
            km_share_list_load();
        } else if (data.error) {
            alert(data.error);
        } else {
            alert(tr("Save error"));
        }
    }

    $("#km_share_list").on("click", ".km_share_role a", function (e) {
        var data = $(this).parent().parent().data(),
            p    = $(this).position();

        $("#km_share_role_select").css({
            "top"  : (p.top + 20) + "px",
            "left" : p.left + "px"
        }).data("user", data.user).show().find("li").removeClass("km_checked").filter("[rel=" + data.role.rel + "]").addClass("km_checked");

        return false;
    }).on("click", ".km_share_delete", function (e) {
            var data = $(this).parent().data();

            km_preloader_show();
            $.post(KAM_ROOT + "ajax/share_delete", {
                username : data.user.username
            }, km_share_json_response, "json");
        });

    $("#km_share_submit").on("click", function (e) {
        km_preloader_show();
        $.post(KAM_ROOT + "ajax/share_add", {
            term : $("#km_share_name").val()
        }, function (data) {
            if (data.status == 1) {
                $("#km_share_name").val("");
            }
            km_share_json_response(data);
        }, "json");
    });

    $("body").on("click", function (e) {
        $("#km_share_role_select").hide();
    });

    $("#share_more_options").on("click", function (e) {
        var user = $(this).parent().parent().parent().data("user");
        
        var href=$(this).attr('href')+user.username+"?setreferpage="+km_infos["page"];
        $(this).attr('href',href);
        

    });
    
    $("#km_share_role_select").on("click", "li", function (e) {
        var user = $(this).parent().parent().data("user"),
            role = $(this).attr("rel");

        km_preloader_show();
        $.post(KAM_ROOT + "ajax/share_role", {
            username : user.username,
            role : role
        }, km_share_json_response, "json");

    });

    $("#km_share_name").autocomplete({
        source : function (request, response) {
            $.post(KAM_ROOT + "ajax/share_autocomplete", request, function (data) {
                response(data.users);
            }, "json");
        },
        select : function (e, ui) {
            km_preloader_show();
            $.post(KAM_ROOT + "ajax/share_add", {
                username : ui.item.value
            }, function (data) {
                $("#km_share_name").val("");
                km_share_json_response(data);
            }, "json");
        },
        appendTo : "#km_share_dialog"
    });

    $("#km_button_share").on("click", km_share_server);
});