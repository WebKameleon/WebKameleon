var jquerycontextmenu = {
    arrowpath: "arrow.gif", //full URL or path to arrow image
    contextmenuoffsets: [1, -1], //additional x and y offset from mouse cursor for contextmenus

    //***** NO NEED TO EDIT BEYOND HERE

    builtcontextmenuids: [], //ids of context menus already built (to prevent repeated building of same context menu)

    cmenuconf: function (target, contextmenuid) {
        var data = target.attr("contextmenu");
        if (data == null)
            return;

        data = data.split(",");

        var page        = data[0],
            page_id     = data[1],
            sid         = data[2],
            server      = data[3],
            pri         = data[4],
            level       = data[5],
            menu_id     = data[6],
            hidden      = data[7],
            more        = data[8],
            next        = data[9],
            uniqueid    = data[10],
            repeat      = data[11];

        var is_hidden = target.find(".km_modul_visible").hasClass("km_icontd_visible_off");

        var type = (page_id == km_infos["page_header"] ? "header" : (page_id == km_infos["page_footer"] ? "footer" : "body"));

        // poziomy
        jQueryKam(".km_contenxtmenu_level").each(function () {
            if (jQueryKam(this).attr("id") == "km_contenxtmenu_lvl_" + type) {
                jQueryKam(this).show().find("a").each(function () {
                    var rel = jQueryKam(this).attr("rel");
                    if (rel == level) {
                        jQueryKam(this).addClass("km_checked");
                    } else {
                        jQueryKam(this).removeClass("km_checked");
                    }
                    jQueryKam(this).attr("href", KAM_ROOT + "edit/set_level/" + sid + "?page=" + page + "&level=" + rel);
                });
            } else {
                jQueryKam(this).hide();
            }
        });

        
        
        
        jQueryKam("#km_contenxtmenu_menu_edit").find("a").each(function () {
            jQueryKam(this).attr("href", "javascript:tdedit("+sid+","+page+",'kameleon_td"+sid+"')");
        });        
        
        // menu
        if (menu_id > 0) {
            jQueryKam("#km_contenxtmenu_menu").show().find("a").attr("href", KAM_ROOT + "menu/get/" + menu_id + "?page=" + page_id);
            jQueryKam("#km_contenxtmenu_menu_new").hide();
            jQueryKam("#km_contenxtmenu_menu_change").show();
            jQueryKam("#km_contenxtmenu_menu_disable").show().find("a").attr("href", KAM_ROOT + "edit/set_menu/" + sid + "?page=" + page_id);
        } else {
            jQueryKam("#km_contenxtmenu_menu").hide();
            jQueryKam("#km_contenxtmenu_menu_new").show().find("a").attr("href", KAM_ROOT + "edit/set_menu/" + sid + "?menu_id=new&page=" + page_id);
            jQueryKam("#km_contenxtmenu_menu_change").hide();
            jQueryKam("#km_contenxtmenu_menu_disable").hide();
        }

        // powtarzaj
        if (type == "body") {
            jQueryKam("#km_contenxtmenu_repeat").show().find("ul li a").each(function () {
                var rel = jQueryKam(this).attr("rel");
                jQueryKam(this).parent().toggleClass("km_checked", rel == repeat);
                jQueryKam(this).attr("href", KAM_ROOT + "edit/set_repeat/" + sid + "?page=" + page + "&repeat=" + rel);
            });
        } else {
            jQueryKam("#km_contenxtmenu_repeat").hide();
        }

        // podmiana menu
        jQueryKam("#km_contenxtmenu_menu_change").find("ul li a").each(function () {
            var rel = jQueryKam(this).attr("rel");
            if (rel == menu_id) {
                jQueryKam(this).addClass("km_checked");
            } else {
                jQueryKam(this).removeClass("km_checked");
            }
            jQueryKam(this).attr("href", KAM_ROOT + "edit/set_menu/" + sid + "?menu_id=" + rel + "&page=" + page_id);
        });

        // widocznosc
        jQueryKam("#km_contenxtmenu_menu_visibility a").off().on("click", function (e) {
            km_module_visible(sid);
        }).removeClass().addClass("km_contenxtmenu_menu_visibility_" + (is_hidden ? "off" : "on"));

        // usuwanie
        jQueryKam("#km_contenxtmenu_menu_delete a").off().on("click", function (e) {
            km_module_delete(sid, page);
        });

        // przesuwanie gora / dol
        jQueryKam("#km_contenxtmenu_menu_up a").attr("href", KAM_ROOT + "edit/move_up/" + sid + "?page=" + page);
        jQueryKam("#km_contenxtmenu_menu_down a").attr("href", KAM_ROOT + "edit/move_down/" + sid + "?page=" + page);

        // info
        jQueryKam("#km_contenxtmenu_menu_infoswf").hide();

        // more
        if (more > 0) {
            jQueryKam("#km_contenxtmenu_menu_more").show().find("a").attr("href", KAM_ROOT + "index/get/" + more);
        } else {
            jQueryKam("#km_contenxtmenu_menu_more").hide();
        }

        // next
        if (next > 0) {
            jQueryKam("#km_contenxtmenu_menu_next").show().find("a").attr("href", KAM_ROOT + "index/get/" + next);
        } else {
            jQueryKam("#km_contenxtmenu_menu_next").hide();
        }

        // skopiuj
        jQueryKam("#km_contenxtmenu_menu_copy a").off().on("click", function (e) {
            copyToClib(sid, "td");
        });

        // skopiuj identyfikator
        jQueryKam("#km_contenxtmenu_menu_mask a").off().on("click", function (e) {
            copyToClib(uniqueid, "mask");
        });
    },

    positionul: function (contextmenuid, e) {
        var ul = jQueryKam(contextmenuid);
        
        var istoplevel = ul.hasClass("km_jqcontextmenu"); //Bool indicating whether $ul is top level context menu DIV
        var docrightedge = jQueryKam(document).scrollLeft() + jQueryKam(window).width() - 40;//40 is to account for shadows in FF
        var docbottomedge = jQueryKam(document).scrollTop() + jQueryKam(window).height() - 40;
        if (istoplevel) { //if main context menu DIV
            var x = e.pageX + this.contextmenuoffsets[0]; //x pos of main context menu UL
            var y = e.pageY + this.contextmenuoffsets[1];
            x = (x + ul.data("dimensions").w > docrightedge) ? docrightedge - ul.data("dimensions").w : x; //if not enough horizontal room to the ridge of the cursor
            y = (y + ul.data("dimensions").h > docbottomedge) ? docbottomedge - ul.data("dimensions").h : y;
        } else { //if sub level context menu UL
            var parentli = ul.data("$parentliref");
            var parentlioffset = parentli.offset();
            var x = ul.data("dimensions").parentliw; //x pos of sub UL
            var y = 0;

            x = (parentlioffset.left + x + ul.data("dimensions").w > docrightedge) ? x - ul.data("dimensions").parentliw - ul.data("dimensions").w : x; //if not enough horizontal room to the ridge parent LI
            y = (parentlioffset.top + ul.data("dimensions").h > docbottomedge) ? y - ul.data("dimensions").h + ul.data("dimensions").parentlih : y;
        }
        ul.css({left: x, top: y});
    },

    showbox: function (target, contextmenuid, e, cmenuconf) {
        (cmenuconf || jquerycontextmenu.cmenuconf)(target, contextmenuid);
        jQueryKam(contextmenuid).show();
    },

    hidebox: function (contextmenuid) {
        jQueryKam(contextmenuid).find("ul").andSelf().hide(); //hide context menu plus all of its sub ULs
    },

    buildcontextmenu: function (contextmenuid) {
        var ul = jQueryKam(contextmenuid);
        ul.css({display: "block", visibility: "hidden"});
        ul.data("dimensions", {w: ul.outerWidth(), h: ul.outerHeight()}); //remember main menu's dimensions
        ul.find("ul").parent().each(function (i) { //find all LIs within menu with a sub UL
            var li = jQueryKam(this).css({zIndex: 1000 + i});
            var subul = li.find("ul:eq(0)").css({display: "block"}); //set sub UL to "block" so we can get dimensions
            subul.data("dimensions", {w: subul.outerWidth(), h: subul.outerHeight(), parentliw: this.offsetWidth, parentlih: this.offsetHeight});
            subul.data("$parentliref", li); //cache parent LI of each sub UL
            li.data("$subulref", subul); //cache sub UL of each parent LI
            li.children("a:eq(0)").append( //add arrow images
                jQueryKam("<span></span>").addClass("km_rightarrowclass")
            );
            li.on("mouseenter", function (e) { //show sub UL when mouse moves over parent LI
                var targetul = jQueryKam(this).data("$subulref");
                if (targetul.queue().length <= 1) { //if 1 or less queued animations
                    jquerycontextmenu.positionul(targetul, e);
                    targetul.show();
                }
            });
            li.on("mouseleave", function (e) { //hide sub UL when mouse moves out of parent LI
                jQueryKam(this).data("$subulref").hide();
            });
        });
        ul.find("ul").andSelf().css({display: "none", visibility: "visible"}); //collapse all ULs again
        this.builtcontextmenuids.push(contextmenuid); //remember id of context menu that was just built
    },


    init: function (target, contextmenuid, cmenuconf) {
        if (this.builtcontextmenuids.length == 0) { //only bind click event to document once
            jQueryKam(document).on("click", function (e) {
                if (e.button == 0) { //hide all context menus (and their sub ULs) when left mouse button is clicked
                    jquerycontextmenu.hidebox(jQueryKam(".km_jqcontextmenu"));
                }
            });
        }
        if (jQueryKam.inArray(contextmenuid, this.builtcontextmenuids) == -1) { //if this context menu hasn't been built yet
            this.buildcontextmenu(contextmenuid);
        }
        if (target.parents().filter("ul.km_jqcontextmenu").length > 0)  { //if jQueryKamtarget matches an element within the context menu markup, don't bind oncontextmenu to that element
            return;
        }
        target.on("contextmenu", jquerycontextmenu.open(target, contextmenuid, cmenuconf));
        target.find(".km_icontd_showmenu").on("click", jquerycontextmenu.open(target, contextmenuid, cmenuconf));
    },

    open : function (target, contextmenuid, cmenuconf)
    {
        return function (e) {
            jquerycontextmenu.hidebox(jQueryKam(".km_jqcontextmenu")); //hide all context menus (and their sub ULs)
            jquerycontextmenu.positionul(contextmenuid, e);
            jquerycontextmenu.showbox(target, contextmenuid, e, cmenuconf);
            return false;
        }
    }
}
jQueryKam.fn.contextmenu = function (contextmenuid, cmenuconf) {
    return this.each(function () {
        jquerycontextmenu.init(jQueryKam(this), contextmenuid, cmenuconf);
    });
};

jQueryKam(function ($) {
    $("#km_jsdomenu").detach().appendTo("body");
    $("[contextmenu]").contextmenu("#km_jsdomenu");
});