// MULTISCHOWEK

var tr_cache=[];


tr = function (txt) {
    
    if (typeof tr_cache[txt] != "undefined") return tr_cache[txt];
    
    jQueryKam.ajax({
        url: km_infos["public_link"] + '/tr',
        type: 'GET',
        dataType: 'json',
        data: {txt: txt},
        cache: true,
        timeout: 3000,
        async: false,
        success : function (data) {
                tr_cache[txt] = data.translated;
                txt=data.translated;
    }});
    

    return txt;
}


function confirmDelete(a) {
    
    if (typeof(a)!='undefined') {
        KamConfirm(tr('Are you sure')+'?',function() {
            var href=jQueryKam(a).prop('href');
            if (href.indexOf('?')>0) href+'&';
            else href+='?';
            href+='r='+Math.random();
            location.href=href;
        }, null,jQueryKam(a).text());
        return false;
    }

    return confirm(tr('Are you sure')+'?');
}


km_td_paste_check_menu = function (a,sid)
{
    jQueryKam.getJSON(km_infos["ajax_link"] + '/webtd/'+sid, null, function (td) {
        
        a.href+='&_='+Math.random();
        if (td.menu_id > 0)
        {
            
            KamConfirm(tr('Module contains menu/list reference, should it be copied as wall?'),
                function() {
                    a.href+='&menu_copy=1';
                    location.href=a.href;
            }, function() {
                    a.href+='&menu_copy=0';
                    location.href=a.href;
            });
        } else {
            location.href=a.href;
        }
    });
    
    return false;
}


km_paste_bymulti = function (page, what, ret) {

    if (what && typeof what == "string")
        what = what.split(",");

    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"] + '/clipboard', { page: page || km_infos["page"] }, function (data) {
        
        km_preloader_hide();
        
        var show = false,
            html = "",
            li   = "";

        html += '<div class="km_schowek_header">' + data.title + '<img src="' + km_infos['root'] + 'skins/' + km_infos['skin'] + '/img/multischowek/close.gif" alt="' + data.close + '" onclick="document.getElementById(\'km_pastediv\').style.display=\'none\'" /></div>';
        html += '<div class="km_schowek_items"><ul>';

        if (data.items != null) {
     
            if (typeof data.items.td != 'undefined' && (what == null || jQueryKam.inArray("td", what) > -1)) {
                show = true;
                jQueryKam.each(data.items.td, function (k, item) {
                    if (km_infos['page_hf'] == 0) {
                        li = li + '<li><a rel="td" onclick="return km_td_paste_check_menu(this,'+item.k+')" href="' + km_infos['root'] + 'edit/paste/' + item.k + '?page=' + data.page_id + (ret ? '&return_url=' + getReturnUrl() : '') + '">' + data.td + ': ' + item.v + '</a></li>';
                    } else {
                        li = li + '<li><a rel="td" onclick="return km_td_paste_check_menu(this,'+item.k+')" href="' + km_infos['root'] + 'edit/paste/' + item.k + '?page=' + data.page_id + '&hf=' + km_infos['page_header'] + '&return_url=' + (ret ? '&return_url=' + getReturnUrl() : '') + '">' + data.td + ': ' + item.v + ' &raquo; ' + data.header + '</a></li>';
                        li = li + '<li><a rel="td" onclick="return km_td_paste_check_menu(this,'+item.k+')" href="' + km_infos['root'] + 'edit/paste/' + item.k + '?page=' + data.page_id + '&hf=' + km_infos['page_footer'] + '&return_url=' + (ret ? '&return_url=' + getReturnUrl() : '') + '">' + data.td + ': ' + item.v + ' &raquo; ' + data.footer + '</a></li>';
                    }
                });
            }

            if (typeof data.items.page != 'undefined' && (what == null || jQueryKam.inArray("page", what) > -1)) {
                show = true;
                jQueryKam.each(data.items.page, function (k, item) {
                    li+='<li><a rel="page" href="' + km_infos['root'] + 'index/paste/' + item.k + '?referer=' + data.page_id + '&return_url=' + (ret ? '&return_url=' + getReturnUrl() : '') + '">' + data.page + ': ' + item.v + '</a></li>';
                });
            }
        }

        html += li + '</ul></div>';

        if (show) {
            jQueryKam("#km_pastediv").html(html).show().draggable({
                handle : '.km_schowek_header'
            });
        } else {
            jQueryKam("#km_pastediv").hide();
            //alert(data.nothing);
            KamDialog(dta.nothing)
        }
    });
}

// PRELOADER
km_preloader_show = function (message) {
    var preloader = jQueryKam("#km_preloader");
    if (preloader.length == 0) {
        preloader = jQueryKam("<div></div>")
            .attr("id", "km_preloader")
            .css("height", jQueryKam(document).height() + "px")
            .append(
                jQueryKam("<div></div>").addClass("km_preloader_msg")
            ).appendTo("body");
    }

    var msg = preloader.find(".km_preloader_msg").html(message || tr("Please wait"));
    preloader.show();
    msg.css("margin-left", "-" + (msg.outerWidth() / 2) + "px");
}

km_preloader_hide = function () {
    jQueryKam("#km_preloader").hide();
}

// DROPMENU
km_dropmenu_show = function (ev, type) { // bookmark / lang / server / plugin
    jQueryKam('body').unbind('click.km');
    if (km_droplist['active'].length > 0)
        jQueryKam("#km_" + km_droplist['active'] + "_link").removeClass("km_" + km_droplist['active'] + "_link_active");
    if (km_droplist['active'] == type) {
        km_droplist['active'] = "";
        km_dropmenu_hide();
    } else {
        jQueryKam(".km_dropmenu_href").removeClass("km_dropmenu_active");
        if (jQueryKam("#km_dropmenu")) jQueryKam("#km_dropmenu").remove();
        var to = jQueryKam(ev.target).offset();
        var tamto = jQueryKam("#km_" + type + "_link").offset();
        var div = jQueryKam("<div></div>").attr("id", "km_dropmenu").hide().addClass('km_dropmenu_' + type).css({ 'left': tamto.left + 'px', 'top': (tamto.top + jQueryKam("#km_" + type + "_link").outerHeight()) + 'px'});
        var ul = jQueryKam("<ul></ul>");
        jQueryKam.each(km_droplist[type], function (k, c) {
            var href = jQueryKam("<a></a>");

            if (c['html'])
                href.html(c['html']);

            if (c['class'])
                href.addClass(c['class']);

            if (c['style'])
                href.attr('style', c['css']);

            if (c['title'])
                href.attr('title', c['title']);

            if (c['img'])
                href.css('background-image', "url('" + c['img'] + "')");

            if (c['href'])
                href.attr('href', c['href']);

            if (c['onclick'])
                href.attr('onclick', 'javascript:' + c['onclick']);

            href.attr('rel', 'km_droplink');

            jQueryKam("<li></li>").append(href).appendTo(ul);
        });

        jQueryKam(div).append(ul);
        km_droplist['active'] = type;
        ev.stopPropagation();
        jQueryKam('body').append(div).unbind('click.km').bind('click.km', function (ev) {
            km_dropmenu_click(ev, type)
        });
        jQueryKam("#km_dropmenu").slideDown('fast');
        jQueryKam("#km_" + type + "_link").addClass("km_" + type + "_link_active");
    }
}

km_dropmenu_hide = function () {
    jQueryKam(".km_dropmenu_href").removeClass("km_dropmenu_active");
    jQueryKam("#km_dropmenu").remove();
}

km_dropmenu_click = function (ev, type) {
    jQueryKam('body').unbind('click.km');
    jQueryKam("#km_" + type + "_link").removeClass("km_" + type + "_link_active");
    km_droplist['active'] = "";
    km_dropmenu_hide();
}

km_dropmenu_load = function (type) {
    jQueryKam.getJSON(km_infos["ajax_link"]+'/dropmenu_load_' + type, { return_link: km_infos["return_link"], page: km_infos["page"], page_link: km_infos["page_link"] }, function (data) {
        if (data.status == '1') {
            km_droplist[type] = data.items;
            if (type == "plugin")
                jQueryKam("#km_plugins_link").css('display', 'block');

            if (type == "bookmark") {
                if (data.dodany == '1')
                    jQueryKam("#km_bookmark_link span").removeClass("km_iconi_bookmark").addClass("km_iconi_bookmark_on");
                else
                    jQueryKam("#km_bookmark_link span").removeClass("km_iconi_bookmark_on").addClass("km_iconi_bookmark");
            }

            if (type == "lang" && jQueryKam("#km_setup_lang").length) {
                jQueryKam.each(data.items, function (k, item) {
                    if (!item.class) {
                        jQueryKam("<option></option>").val(item.id).text(item.title).appendTo("#km_setup_lang")
                    }
                });
            }
        }
    });
}

km_dropmenu_init = function () {
    jQueryKam("#km_ddmenu_linijka").on('click', function (e) {
        jQueryKam(".km_ruler_x").toggle();
    });

    if (jQueryKam("#km_server_link").length) {
        km_dropmenu_load("server");
        jQueryKam("#km_server_link").on('click',function (e) {
            km_dropmenu_show(e, "server");
        }).disableSelection();
    }

    if (jQueryKam("#km_lang_link").length) {
        km_dropmenu_load("lang");
        jQueryKam("#km_lang_link").on('click',function (e) {
            km_dropmenu_show(e, "lang");
        }).disableSelection();
    }

    if (jQueryKam("#km_bookmark_link").length) {
        km_dropmenu_load("bookmark");
        jQueryKam("#km_bookmark_link").on('click',function (e) {
            km_dropmenu_show(e, "bookmark");
        }).disableSelection();
    }
}


// STRONA
km_page_visible = function (pagesid) {
    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"]+'/page_visible', { pagesid: pagesid }, function (data) {
        if (data.status == '1') {
            if (data.hidden == '1') jQueryKam(".km_page_visible").removeClass("km_iconi_visible").addClass("km_iconi_invisible");
            else jQueryKam(".km_page_visible").removeClass("km_iconi_invisible").addClass("km_iconi_visible");
            km_preloader_hide();
        } else
            KamDialog(tr("Save error"));
    });
}

km_page_sitemap = function (pagesid) {
    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"]+'/page_sitemap_visible', { pagesid: pagesid }, function (data) {
        if (data.status == '1') {
            if (data.nositemap == '1')
                jQueryKam(".km_page_sitemap_visible").removeClass("km_iconi_sm").addClass("km_iconi_nsm");
            else
                jQueryKam(".km_page_sitemap_visible").removeClass("km_iconi_nsm").addClass("km_iconi_sm");
            km_preloader_hide();
        } else
            KamDialog(tr("Save error"));
    });
}

km_bookmark = function () {
    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"]+'/bookmark', { page: km_infos["page"] }, function (data) {
        if (data.status == '1') {
            km_preloader_hide();
            km_dropmenu_load("bookmark");
        } else
            KamDialog(tr("Save error"));
    });
}


// MODUŁY

km_module_insert = function (type, section) {
    if (km_infos["page_hf"] == 0 || section) {
        km_preloader_show();
        jQueryKam.getJSON(km_infos["ajax_link"] + '/module_add', {
            type_id: type,
            page_id: km_infos["page_hf"] == 0 ? km_infos["page"] : (section == 1 ? km_infos["page_header"] : km_infos["page_footer"])
        }, function (data) {
            km_preloader_hide();
            if (data.status == 1) {
                document.location = KAM_ROOT + "index/get/" + km_infos["page"]+'?_='+data.td.sid+'#kameleon_td'+data.td.sid;
            } else {
                var e=tr("Save error");
                KamDialog(data.error?data.error:e,e);
            }
        });
    } else {
        var dialog = jQueryKam('#km_checkHF');
        if (dialog.hasClass("km_checkHF") == false) {
            dialog.dialog({
                autoOpen: true,
                width: 300,
                modal: true,
                title: tr("Choose section"),
                buttons: [
                    {
                        text: tr("Cancel"),
                        id: 'km_check_hf_cancel',
                        click: function () {
                            jQueryKam(this).dialog("close");
                        }
                    },
                    {
                        text: tr("Choose"),
                        click: function () {
                            var val = jQueryKam("#km_check_hf").val();
                            if (val) {
                                jQueryKam(this).dialog("close");
                                km_module_insert(type, val);
                            } else {
                                KamDialog(tr("You must choose section"));
                            }
                        }
                    }
                ]
            }).addClass("km_checkHF");
        }
        dialog.dialog("open");
    }
}

km_module_drag = function (level, tdsid, kolejka, strona) {
    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"]+'/module_drag', { level: level, tdsid: tdsid, kolejka: kolejka }, function (data) {
        km_preloader_hide();
        if (data.status == 0)
            KamDialog(tr("Save error"));
    });
}

km_module_delete = function (tdsid, page) {
    if (confirmDelete()) {
        km_preloader_show();
        if (page == undefined)
            page = km_infos["page"];
        jQueryKam.getJSON(km_infos["ajax_link"]+'/module_delete', { tdsid: tdsid }, function (data) {
            km_preloader_hide();
            if (data.status == '1')
                jQueryKam("#km_dragbox_" + tdsid).remove();
            else
                KamDialog(tr("Save error"));
        });
    }
}

km_module_visible = function (tdsid) {
    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"]+'/module_visible', { tdsid: tdsid  }, function (data) {
        if (data.status == '1') {
            if (data.hidden == '1')
            {
                jQueryKam("#km_td_" + tdsid + " .km_modul_visible").removeClass("km_icontd_visible_on").addClass("km_icontd_visible_off").show();
            }
            else
                jQueryKam("#km_td_" + tdsid + " .km_modul_visible").removeClass("km_icontd_visible_off").addClass("km_icontd_visible_on");
            km_preloader_hide();
        } else
            KamDialog(tr("Save error"));
    });
}

// CZUJKA
km_czujka_logo = function (event) {
    if (jQueryKam(event.target).closest(".km_gapps").length === 0) {
        jQueryKam(document).unbind('click', km_czujka_logo);
        jQueryKam(".km_gapps ul").hide();
        event.preventDefault();
    }
}

function getUrlVars(url)
{
    var vars = {};
    url = url || document.location.search;
    if (url) {
        jQueryKam.each(decodeURI(url).substring(1).split("&"), function (k, v) {
            var parts = v.split("=");
            vars[parts[0]] = parts[1];
        });
    }
    return vars;
}

function getUrlVar(name, defaultValue, url)
{
    var value = null || defaultValue;
    jQueryKam.each(getUrlVars(url), function (k, v) {
        if (k == name)
            value = v;
    });
    return value;
}



function copyToClib(id,what)
{
    km_preloader_show();
    jQueryKam.getJSON(km_infos["ajax_link"]+'/copy', { id: id, what: what  }, function (data) {
        km_preloader_hide();
        if (data.status == '1') {
            //alert(data.info);
            KamDialog(data.info);
            jQueryKam(".km_iconi_paste").removeClass("km_icon_disabled");
        } else {
            KamDialog(tr("Save error"));
        }
    });
}

var Base64 = {

    // private property
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode: function (string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode: function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}

function getReturnUrl()
{
    return Base64.encode(document.location.href);
}

function KamDialog(html, title, options,okCallback)
{

    
    if (options==null)
    {
        options = {
            buttons: [
                    {
                        text: tr("OK"),
                        click: function () {
                            if (okCallback) {
                                okCallback(jQueryKam(this));
                            }
                            jQueryKam(this).dialog("close");
                        }
                    }]
        };
    }
    
    if (title==null)
    {
        title=tr('Info');
    }
    
    
    options = jQueryKam.extend({
        title : title,
        dialogClass : "km_dialog",
        closeOnEscape : false,
        minHeight: 0
    }, options);

    return jQueryKam("<div></div>").addClass("km_dialog_content").html('<div class="km_dialog_content">' + html + '</div>').appendTo("body").dialog(options).dialog("open");
}

function KamPrompt(html, title, okCallback, defaultValue, width)
{
    html = '<div class="km_dialog_block">'
         + '<label>' + html + '</label>'
         + '<input type="text" class="km_dialog_input" value="' + (defaultValue || "") + '" />'
         + '</div>';
         
         
    var options={
        modal : true,
        dialogClass : "km_dialog km_dialog_prompt",
        create : function (event, ui) {
            var btn = jQueryKam(this).dialog("widget").find(".km_fbtn_action");
            jQueryKam(this).find(".km_dialog_input").on("keyup", function (e) {
                if (e.keyCode == 13) {
                    btn.trigger("click");
                }
            });
        },
        buttons : [
            {
                text : tr("OK"),
                class : "km_fbtn_action",
                click : function () {
                    jQueryKam(this).dialog("close");
                    if (okCallback)
                        okCallback(
                            jQueryKam(this).find(".km_dialog_input").val()
                        );
                }
            }
        ]
    };
    
    if (width!=null) options.width=width;

    return KamDialog(html, title, options);
}

function KamConfirm(html, yesCallback, noCallback,title)
{
    
    if (title==null) title=tr('Confirmation');
    
    return KamDialog(html, title, {
        modal : true,
        dialogClass : "km_dialog km_dialog_confirm",
        buttons : [
            {
                text : tr("No"),
                class : "km_fbtn_normal",
                click : function () {
                    jQueryKam(this).dialog("close");
                    if (noCallback)
                        noCallback();
                }
            },
            {
                text : tr("Yes"),
                class : "km_fbtn_action",
                click : function () {
                    jQueryKam(this).dialog("close");
                    if (yesCallback)
                        yesCallback();
                }
            }
        ]
    });
}

function tdedit(sid,page,hash)
{
    document.location = KAM_ROOT + "edit/get/" + sid + "?page=" + page + "&setreferpage=" + page + "&hash=" + hash + "&w=" + jQueryKam("#km_td_" + sid).outerWidth();
}

String.prototype.ucfirst = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
}


// INICJALIZACJA
jQueryKam(function ($) {
    km_dropmenu_init();

    $.each(["show", "hide"], function (i, val) {
        var org = $.fn[val];
        $.fn[val] = function() {
            this.trigger(val);
            return org.apply(this, arguments);
        };
    });

    var org = $.ui.dialog.prototype._show;
    $.ui.dialog.prototype._show = function (dialog) {
        $(dialog).addClass("km_dialog");
        return org.apply(this, arguments);
    }

    if ($("#km_logo")) {
        $("#km_logo").bind('click', function () {
            var ul = $(".km_gapps ul");
            if (ul.css('display') == 'block') {
                ul.hide();
                $(document).unbind('click', km_czujka_logo);
            } else {
                ul.show();
                $(document).bind('click', km_czujka_logo);
            }
        });
    }

    if ($(".km_loginb")) {
        $(".km_loginb").bind('click', function () {
            if ($(".km_profileb").css('display') == 'block') {
                $(".km_profileb").hide();
            } else {
                $(".km_profileb").fadeIn();
            }
        });
    }

    $(".km_ddmenu li li a").bind('click', function () {
        $(".km_ddmenu li ul").hide();
    });

    $(".km_insert_type").bind('click', function () {
        km_module_insert($(this).attr('rel'));
    });

    $("ul.sf-menu").superfish({
        delay : 0,
        speed : 0,
        speedOut : 0
    });

    $("body").on("click", "a[href][confirm]", function (e) {
        var html = $(this).attr("confirm"),
            URL  = $(this).attr("href");

        if ($.isNumeric(html))
            html = tr("Are you sure") + "?";

        KamConfirm(html, function () {
            document.location = URL;
        });

        return false;
    });

    $(".km_zakladki a").on("click", function (e) {
        var rel = $(this).attr("rel");
        $(this).parent().addClass("active").siblings().removeClass("active");
        $(".km_zakladka").hide().filter(".km_zakladka_" + rel).show();
    }).first().trigger("click");

    if ($("form[validate]").length) {
        head.js(KAM_ROOT + "js/validate.js");
    }

//    if ($("[gallery]").length) {
//        head.js(KAM_ROOT + "js/gallery.js");
//    }

    if ($("[files]").length) {
        head.js(KAM_ROOT + "js/files.js");
    }

    if ($("[jstree]").length) {
        head.js(KAM_ROOT + "js/jstree.js");
    }

    if ($("input[colorpicker]").length) {
        head.js(KAM_ROOT + "js/colorpicker.js");
    }

    if ($("input[datetimepicker], input[datepicker]").length) {
        head.js(KAM_ROOT + "js/datetimepicker.js");
    }

    if ($(".webtd_title").length) {
        var km_check_title = function () {
            $(".webtd_notitle").toggle(
                $(".webtd_title").val().length == 0
            );
        }

        $(".webtd_title").on("keyup", km_check_title);
        km_check_title();
    }

    if ($("#km_toolbar_toggle").length) {
        head.js(KAM_ROOT + "js/cookies.js", function () {
            $("#km_toolbar_toggle").on("click", function (e) {
                $(".km_toolbar_toggle").toggle("normal");
                $(this).toggleClass("toggle");
                KamCookie("km_toolbar_toggle", $(this).hasClass("toggle") ? "visible" : "hidden");
            });

            KamCookie("km_toolbar_toggle", function (value) {
                if (value == "visible") {
                    $(".km_toolbar_toggle").show(0);
                    $("#km_toolbar_toggle").addClass("toggle");
                } else {
                    $(".km_toolbar_toggle").hide(0);
                    $("#km_toolbar_toggle").removeClass("toggle");
                }
            });
        });
    }

    $("input[slider]").each(function () {
        var options = $(this).attr("slider").split(":"),
            value   = $(this).val(),
            input   = this;

        $("<div></div>").addClass("km_slider").insertAfter(input).slider({
            min   : options[0] * 1,
            max   : options[1] * 1,
            step  : options[2] * 1,
            range : "min",
            value :  value,
            slide : function (e, ui) {
                $(input).val(ui.value);
            }
        });
    });

    $("a.km_prompt_title").on("click", function (e) {
        var href = this.href;
        KamPrompt("", tr("Enter page title"), function (value) {
            document.location = href + (href.indexOf("?") > -1 ? "&" : "?") + "title=" + value;
        });
        return false;
    });

    $(".km_dragbox").each(function () {
        var dragbox = $(this);

        var elem = dragbox.find(".km_tdin").children().first();

        if (elem.css("float") === "left" || elem.css("float") === "right") {
	    dragbox.addClass('td-float-'+elem.css("float"));
	    /*
            dragbox.css({
                float : elem.css("float")
            });
	    */
        }
    });

    $("#km_translate_save").on("click", function (e) {
        var translations = {};

        $('.km_translate, [km_translate="1"]').each(function (e) {
            var rel = $(this).attr("rel");
            var parts = rel.split(",");
            if (parts.length == 4) {
                var attr = parts.pop();
                rel = parts.join(",");
                translations[rel] = $(this).attr(attr);
            } else if (parts.length == 3) {
                translations[rel] = $(this).html();
            }
        });

        km_preloader_show();

        $.post(KAM_ROOT + "ajax/translate/"+km_infos["page"], translations, function (data) {
            km_preloader_hide();
            document.location = km_infos["root"]+"index/get/"+km_infos["page"]+"?google_translate_next="+data.next;
        });

        return false;
    });
    
    
    
    $(".km_section_prompt").mouseenter(function () {
     
        $(this).children().addClass('km_section_prompt_over');

        
        var prompter = jQueryKam("#km_prompter");
        var link=location.href;
        var prompt=tr("Switch header/footer in View menu");
        link+=(link.indexOf('?')>0)?'&':'?';
        link+='switcheditmode=1';
        if (prompter.length==0) {
            prompter = jQueryKam("<div></div>")
                .attr("id", "km_prompter")
                .addClass("km_prompter")
                .append(
                    jQueryKam("<div></div>").addClass("km_prompter_msg")
                );//.appendTo("body");
            
            $(this).append(prompter);
            prompter.find(".km_prompter_msg").html('<a class="yesiknow" href="'+km_infos["root"]+'index/get/'+km_infos["page"]+'?UserKnowsHowToEditHeaderFooter=1"><img title="'+tr('Yes, I know')+'" src="'+km_infos["root"]+'skins/kameleon/img/i_close.png" /></a><a href="'+link+'">'+prompt+'</a>');
        }
        
        
        prompter.css("top",$(this).position().top+$(this).height()/2 - 20);
        var msg=prompter.find(".km_prompter_msg");
       
        
        msg.css("margin-left", (($(this).width() / 2) - prompt.length*3.6) + "px");
        
        msg.hide();
        prompter.show();
        
        setTimeout( function() {
            msg.fadeIn();
            setTimeout(function() {
                msg.fadeOut();
            },8000);
        },1000);
        

    }).mouseleave(function () {
        $(this).children().removeClass('km_section_prompt_over');
        jQueryKam("#km_prompter").fadeOut();

    });
});



    
