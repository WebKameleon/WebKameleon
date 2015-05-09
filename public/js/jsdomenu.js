/* jQuery Context Menu
 * Created: Dec 16th, 2009 by DynamicDrive.com. This notice must stay intact for usage
 * Author: Dynamic Drive at http://www.dynamicdrive.com/
 * Visit http://www.dynamicdrive.com/ for full source code
 */
int_add_menu_exist = function (page, page_id, sid, server) {
    if (jQueryKam("#int_addMenuExist").length == 0) {
        var div = jQueryKam("<div></div>").attr("id", "int_addMenuExist");
        km_preloader_show(km_lang['Trwa ładowanie ustawień']);
        var divIn = jQueryKam("<ul></ul>").addClass("int_addMenuExistIn");
        var divS = jQueryKam("<li></li>").append(jQueryKam("<label></label>").attr('for', 'int_addMenuExist_menu').html('Menu')).append(jQueryKam("<select></select>").attr('id', 'int_addMenuExist_menu'));
        divIn.append(divS);
        div.append(divIn);
        jQueryKam("body").append(div);
        jQueryKam("#int_addMenuExist").dialog({
            autoOpen: false,
            dialogClass: 'km_sharedialog',
            height: 200,
            width: 600,
            modal: true,
            title: 'Dostępne menu',
            buttons: {
                "Anuluj": {
                    text: km_lang['Anuluj'],
                    id: 'km_setup_lang_cancel',
                    click: function () {
                        jQueryKam(this).dialog("close");
                    }
                },
                "Zapisz": {
                    text: km_lang['Zapisz zmiany'],
                    click: function () {
                        jQueryKam(this).dialog("close");
                        km_preloader_show();
                        nr_menu = jQueryKam("#int_addMenuExist_menu").val();
                        jQueryKam.getJSON(km_infos["ajax_link"], { action: 'menuSave', menu_id: nr_menu, page: page, page_id: page_id, sid: sid, server: server, ajax_noauth: 0 }, function (data) {
                            document.location.href = km_infos["page_link"] + '?page=' + km_infos['page'] + '&version=' + km_infos['server_version'] + '&SetServer=' + km_infos['server_name'];
                        });
                    }
                }
            }
        });
    }
    int_menulist(page, page_id, sid, server);
    jQueryKam("#int_addMenuExist").dialog("open");
    //km_share_list();
}

int_menulist = function () {
    var sel = jQueryKam("#int_addMenuExist_menu");
    jQueryKam.getJSON(km_infos["ajax_link"], { action: 'menuList', page: page, page_id: page_id, sid: sid, server: server, ajax_noauth: 0 }, function (data) {
        //km_storage['szablon']=data.szablon;
        km_preloader_hide();
        jQueryKam("#int_addMenuExistIn").show();
        for (var c in data.lista) {
            var op = jQueryKam("<option></option>").html(data.lista[c].id + " - " + data.lista[c].name).val(data.lista[c].id);
            sel.append(op);
        }
    });
}

function int_AddMenu(menu_id, page, page_id, sid, server) {
    addMenu(menu_id, page, page_id, sid, server);
}

function int_skopiuj(param1, param2, param3, param4) {
    skopiuj(param1, param2, unescape(param3), param4);
}

function int_skopiuj_identyfikator(s) {
    skopiuj(s, 'mask');
}

var jquerycontextmenu = {
    arrowpath: 'arrow.gif', //full URL or path to arrow image
    contextmenuoffsets: [1, -1], //additional x and y offset from mouse cursor for contextmenus

    //***** NO NEED TO EDIT BEYOND HERE

    builtcontextmenuids: [], //ids of context menus already built (to prevent repeated building of same context menu)

    contextmenucfg: [], // konfiguracja modulow

    positionul: function (jQueryKam, ul, e) {
        var istoplevel = ul.hasClass('km_jqcontextmenu') //Bool indicating whether $ul is top level context menu DIV
        var docrightedge = jQueryKam(document).scrollLeft() + jQueryKam(window).width() - 40 //40 is to account for shadows in FF
        var docbottomedge = jQueryKam(document).scrollTop() + jQueryKam(window).height() - 40
        if (istoplevel) { //if main context menu DIV
            var x = e.pageX + this.contextmenuoffsets[0] //x pos of main context menu UL
            var y = e.pageY + this.contextmenuoffsets[1]
            x = (x + ul.data('dimensions').w > docrightedge) ? docrightedge - ul.data('dimensions').w : x //if not enough horizontal room to the ridge of the cursor
            y = (y + ul.data('dimensions').h > docbottomedge) ? docbottomedge - ul.data('dimensions').h : y
        } else { //if sub level context menu UL
            var parentli = ul.data('$parentliref')
            var parentlioffset = parentli.offset()
            var x = ul.data('dimensions').parentliw //x pos of sub UL
            var y = 0

            x = (parentlioffset.left + x + ul.data('dimensions').w > docrightedge) ? x - ul.data('dimensions').parentliw - ul.data('dimensions').w : x //if not enough horizontal room to the ridge parent LI
            y = (parentlioffset.top + ul.data('dimensions').h > docbottomedge) ? y - ul.data('dimensions').h + ul.data('dimensions').parentlih : y
        }
        ul.css({left: x, top: y})
    },

    cmenuconf: function (jQueryKam, t, contextmenu) {
        // ustawienie poziomu
        api = this.contextmenucfg[t.attr('id')]['api'];
        type = this.contextmenucfg[t.attr('id')]['type'];
        tdtyp = this.contextmenucfg[t.attr('id')]['tdtyp'];
        level = this.contextmenucfg[t.attr('id')]['level'];
        menu_id = this.contextmenucfg[t.attr('id')]['menu_id'];
        page = this.contextmenucfg[t.attr('id')]['page'];
        page_id = this.contextmenucfg[t.attr('id')]['page_id'];
        pri = this.contextmenucfg[t.attr('id')]['pri'];
        sid = this.contextmenucfg[t.attr('id')]['sid'];
        server = this.contextmenucfg[t.attr('id')]['server'];
        title = this.contextmenucfg[t.attr('id')]['title'];
        infoswf = this.contextmenucfg[t.attr('id')]['infoswf'];
        more = this.contextmenucfg[t.attr('id')]['more'];
        next = this.contextmenucfg[t.attr('id')]['next'];
        scriptname = this.contextmenucfg[t.attr('id')]['scriptname'];
        ext = this.contextmenucfg[t.attr('id')]['ext'];
        vis_lab = this.contextmenucfg[t.attr('id')]['vis_lab'];
        vis_class = this.contextmenucfg[t.attr('id')]['vis_class'];

        // ustawienie POZIOMU
        if (tdtyp == "td") {
            jQueryKam("#km_contenxtmenu_lvl_td").show();
            jQueryKam("#km_contenxtmenu_lvl_hf").hide();
        } else {
            jQueryKam("#km_contenxtmenu_lvl_td").hide();
            jQueryKam("#km_contenxtmenu_lvl_hf").show();
        }
        lista_poziomow = jQueryKam("#km_contenxtmenu_lvl_" + tdtyp + " a");
        for (var i = 0; i < lista_poziomow.length; i++) {
            if (lista_poziomow.eq(i).attr("rel") == level) {
                lista_poziomow.eq(i).addClass("km_checked");
            } else {
                lista_poziomow.eq(i).removeClass("km_checked");
            }
            lista_poziomow.eq(i).attr("href", "javascript:doMenuChange('" + page_id + "','" + sid + "','" + server + "','level','" + lista_poziomow.eq(i).attr("rel") + "')");
        }

        // ustawienie TYPU
        lista_typow = jQueryKam("#km_contenxtmenu_typ ul a");
        for (var i = 0; i < lista_typow.length; i++) {
            if (lista_typow.eq(i).attr("rel") == type) {
                lista_typow.eq(i).addClass("km_checked");
            } else {
                lista_typow.eq(i).removeClass("km_checked");
            }
            lista_typow.eq(i).attr("href", "javascript:doMenuChange('" + page_id + "','" + sid + "','" + server + "','typ','" + lista_typow.eq(i).attr("rel") + "')");
        }

        // ustawienie API
        lista_api = jQueryKam("#km_contenxtmenu_api ul a");
        for (var i = 0; i < lista_api.length; i++) {
            if (lista_api.eq(i).attr("rel") == api && api != "") {
                lista_api.eq(i).addClass("km_checked");
            } else {
                lista_api.eq(i).removeClass("km_checked");
            }
            lista_api.eq(i).attr("href", "javascript:doMenuChange('" + page_id + "','" + sid + "','" + server + "','usluga','" + lista_api.eq(i).attr("rel") + "')");
        }
        jQueryKam("#km_contextmenu_apiremove").attr("href", "javascript:doMenuChange('" + page_id + "','" + sid + "','" + server + "','usluga','NULL')");

        // ustawienie MENU
        if (menu_id > 0) {
            jQueryKam("#km_contenxtmenu_menu_menu").show();
            jQueryKam("#km_contenxtmenu_menu_menu a").attr("href", "menus." + ext + "?menu=" + menu_id + "&setreferpage=" + page_id);
            jQueryKam("#km_contenxtmenu_menu_insert").hide();
            jQueryKam("#km_contenxtmenu_menu_insert_exist").show();
            jQueryKam("#km_contenxtmenu_menu_off").show();
            jQueryKam("#km_contenxtmenu_menu_off a").eq(0).unbind().bind('click', function () {
                addMenu(0, page, page_id, sid, server);
            });
            jQueryKam("#km_contenxtmenu_menu_insert_exist a").eq(0).unbind().bind('click', function () {
                int_add_menu_exist(page, page_id, sid, server);
            });
        } else {
            jQueryKam("#km_contenxtmenu_menu_menu").hide();
            jQueryKam("#km_contenxtmenu_menu_insert").show();
            jQueryKam("#km_contenxtmenu_menu_insert_exist").hide();
            jQueryKam("#km_contenxtmenu_menu_off").hide();
            jQueryKam("#km_contenxtmenu_menu_insert a").eq(0).unbind().bind('click', function () {
                addMenu(-1, page, page_id, sid, server);
            });
        }

        // ustawienie opcji KOPIUJ
        jQueryKam("#km_contenxtmenu_menu_copy a").eq(0).unbind().bind('click', function () {
            if (menu_id > 0) {
                int_skopiuj(sid, 'td', escape(title), menu_id);
            } else {
                int_skopiuj(sid, 'td', escape(title), null);
            }
        });

        // ustawienie opcji DELETE
        jQueryKam("#km_contenxtmenu_menu_delete a").eq(0).unbind().bind('click', function () {
            km_module_delete(sid, page_id);
        });

        // ustawienie INFOSWF
        if (infoswf.length > 0) {
            jQueryKam("#km_contenxtmenu_menu_infoswf").show();
            jQueryKam("#km_contenxtmenu_menu_infoswf a").attr("href", infoswf);
        } else {
            jQueryKam("#km_contenxtmenu_menu_infoswf").hide();
        }

        // ustawienie MORE
        if (more.length > 0) {
            jQueryKam("#km_contenxtmenu_menu_more").show();
            jQueryKam("#km_contenxtmenu_menu_more a").attr("href", scriptname + "?page=" + more + "&setreferpage=" + page_id);
        } else {
            jQueryKam("#km_contenxtmenu_menu_more").hide();
        }

        // ustawienie NEXT
        if (next.length > 0) {
            jQueryKam("#km_contenxtmenu_menu_next").show();
            jQueryKam("#km_contenxtmenu_menu_next a").attr("href", scriptname + "?page=" + next + "&setreferpage=" + page_id);
        } else {
            jQueryKam("#km_contenxtmenu_menu_next").hide();
        }

        // ustawienie UPDOWN
        jQueryKam("#km_contenxtmenu_menu_up a").attr("href", scriptname + "?table=td&page=" + page + "&pole=page_id&wart=" + page_id + "&sid=" + sid + "&dir=up&action=Move");
        jQueryKam("#km_contenxtmenu_menu_down a").attr("href", scriptname + "?table=td&page=" + page + "&pole=page_id&wart=" + page_id + "&sid=" + sid + "&dir=down&action=Move");

        // ustawienia VISIBILITY
        jQueryKam("#km_contenxtmenu_menu_visibility a").unbind().bind('click', function () {
            km_module_visible(sid);
        });
        jQueryKam("#km_contenxtmenu_menu_visibility a").removeClass().addClass(vis_class);
        jQueryKam("#km_contenxtmenu_menu_visibility a").html(vis_lab);

        // ustawienie opcji KOPIUJ IDENTYFIKATOR
        jQueryKam("#km_contenxtmenu_menu_mask a").eq(0).unbind().bind('click', function () {
            int_skopiuj_identyfikator(sid);
        });


        //alert(this.contextmenucfg[t.attr('id')]['page_id']);
    },

    showbox: function (jQueryKam, t, contextmenu, e) {
        jquerycontextmenu.cmenuconf(jQueryKam, t, contextmenu);
        contextmenu.show();
    },

    hidebox: function (jQueryKam, contextmenu) {
        contextmenu.find('ul').andSelf().hide() //hide context menu plus all of its sub ULs
    },


    buildcontextmenu: function (jQueryKam, menu) {
        menu.css({display: 'block', visibility: 'hidden'}).appendTo(document.body)
        menu.data('dimensions', {w: menu.outerWidth(), h: menu.outerHeight()}) //remember main menu's dimensions
        var lis = menu.find("ul").parent() //find all LIs within menu with a sub UL
        lis.each(function (i) {
            var li = jQueryKam(this).css({zIndex: 1000 + i});
            var subul = li.find('ul:eq(0)').css({display: 'block'}) //set sub UL to "block" so we can get dimensions
            subul.data('dimensions', {w: subul.outerWidth(), h: subul.outerHeight(), parentliw: this.offsetWidth, parentlih: this.offsetHeight})
            subul.data('$parentliref', li) //cache parent LI of each sub UL
            li.data('$subulref', subul) //cache sub UL of each parent LI
            li.children("a:eq(0)").append( //add arrow images
                '<span class="km_rightarrowclass" style="border:0;"></span>'
            )
            li.bind('mouseenter', function (e) { //show sub UL when mouse moves over parent LI
                var targetul = jQueryKam(this).data('$subulref')
                if (targetul.queue().length <= 1) { //if 1 or less queued animations
                    jquerycontextmenu.positionul(jQueryKam, targetul, e)
                    targetul.show()
                }
            })
            li.bind('mouseleave', function (e) { //hide sub UL when mouse moves out of parent LI
                jQueryKam(this).data('$subulref').hide()
            })
        })
        menu.find('ul').andSelf().css({display: 'none', visibility: 'visible'}) //collapse all ULs again
        this.builtcontextmenuids.push(menu.attr('id')) //remember id of context menu that was just built
    },


    init: function (jQueryKam, target, contextmenu, km_tdtyp, km_page_id, km_page, km_sid, km_server, km_type, km_level, km_api, km_pri, km_id, km_hash, km_tab_name, km_hidden, km_valid, km_script_name, km_menu_id, km_title, km_infoswf, km_more, km_next, km_scriptname, km_ext, km_vis_lab, km_vis_class) {
        this.contextmenucfg[target.attr('id')] = {
            tdtyp: km_tdtyp,
            page_id: km_page_id,
            page: km_page,
            sid: km_sid,
            server: km_server,
            type: km_type,
            level: km_level,
            api: km_api,
            pri: km_pri,
            id: km_id,
            hash: km_hash,
            tab_name: km_tab_name,
            hidden: km_hidden,
            valid: km_valid,
            script_name: km_script_name,
            menu_id: km_menu_id,
            title: km_title,
            infoswf: km_infoswf,
            more: km_more,
            next: km_next,
            scriptname: km_scriptname,
            ext: km_ext,
            vis_lab: km_vis_lab,
            vis_class: km_vis_class
        };
        if (this.builtcontextmenuids.length == 0) { //only bind click event to document once
            jQueryKam(document).bind("click", function (e) {
                if (e.button == 0) { //hide all context menus (and their sub ULs) when left mouse button is clicked
                    jquerycontextmenu.hidebox(jQueryKam, jQueryKam('.km_jqcontextmenu'))
                }
            })
        }
        if (jQueryKam.inArray(jQueryKam('#' + contextmenu).attr('id'), this.builtcontextmenuids) == -1) //if this context menu hasn't been built yet
        {
            this.buildcontextmenu(jQueryKam, jQueryKam('#' + contextmenu))
        }
        jQueryKam(document).bind("click", function (e) {
            if (e.button == 0) { //hide all context menus (and their sub ULs) when left mouse button is clicked
                jquerycontextmenu.hidebox(jQueryKam, jQueryKam('.km_jqcontextmenu'))
            }
        })
        if (target.parents().filter('ul.km_jqcontextmenu').length > 0) //if $target matches an element within the context menu markup, don't bind oncontextmenu to that element
        {
            return
        }
        target.bind("contextmenu", function (e) {
            var t = jQueryKam(this);
            jquerycontextmenu.hidebox(jQueryKam, jQueryKam('.km_jqcontextmenu')) //hide all context menus (and their sub ULs)
            jquerycontextmenu.positionul(jQueryKam, jQueryKam('#' + contextmenu), e)
            jquerycontextmenu.showbox(jQueryKam, t, jQueryKam('#' + contextmenu), e)
            return false;
        });
        target.find(".km_icontd_showmenu").eq(0).bind("click", function (e) {
            var t = jQueryKam(this);
            jquerycontextmenu.hidebox(jQueryKam, jQueryKam('.km_jqcontextmenu')) //hide all context menus (and their sub ULs)
            jquerycontextmenu.positionul(jQueryKam, jQueryKam('#' + contextmenu), e)
            jquerycontextmenu.showbox(jQueryKam, t.parent().parent().parent(), jQueryKam('#' + contextmenu), e)
            return false;
        });
    }
}

jQueryKam.fn.addcontextmenu = function (contextmenuid, km_tdtyp, km_page_id, km_page, km_sid, km_server, km_type, km_level, km_api, km_pri, km_id, km_hash, km_tab_name, km_hidden, km_valid, km_script_name, km_menu_id, km_title, km_infoswf, km_more, km_next, km_scriptname, km_ext, km_vis_lab, km_vis_class) {
    return this.each(function () { //return jQuery obj
        var target = jQueryKam(this)
        jQueryKam("#km_dragbox_" + km_sid + " .km_icontd_info").hover(
            function (eventObj) {
                var ht = jQueryKam("#km_dragbox_" + km_sid + " .km_infotd").html();
                var lf = eventObj.pageX;
                var tp = eventObj.pageY;
                jQueryKam("#km_hinter").html(ht);
                if ((lf + 10 + jQueryKam("#km_hinter").width()) > jQueryKam(window).width()) {
                    lf = jQueryKam(window).width() - 100 - jQueryKam("#km_hinter").width();
                }
                jQueryKam("#km_hinter").css({
                    'left': (lf + 10) + 'px',
                    'top': (tp + 10) + 'px'
                }).show();
            },
            function () {
                jQueryKam("#km_hinter").hide();
            }
        );
        jquerycontextmenu.init(jQueryKam, target, contextmenuid, km_tdtyp, km_page_id, km_page, km_sid, km_server, km_type, km_level, km_api, km_pri, km_id, km_hash, km_tab_name, km_hidden, km_valid, km_script_name, km_menu_id, km_title, km_infoswf, km_more, km_next, km_scriptname, km_ext, km_vis_lab, km_vis_class)
    })
};