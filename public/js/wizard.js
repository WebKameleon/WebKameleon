
function wizardScrollTop()
{
    jQueryKam('html, body').animate({scrollTop: jQueryKam("#carousel-wizard").offset().top}, 500);
}


jQueryKam(function ($) {


    var km_wizard_create = function () {
        var dialog = $("#km_wizard_create_dialog");
        if (dialog.hasClass("km_wizard_dialog") == false) {
            dialog.dialog({
                autoOpen : false,
                dialogClass : "km_wizard_dialog",
                modal: true,
                width: 1000,
                height: 560,
                title : tr("Choose template"),
                open : km_wizard_load_templates,
                buttons: [
                    {
                        text: tr("Cancel"),
                        click: function () {
                            dialog.dialog("close");
                        }
                    },
                    {
                        text: tr("Create"),
                        "class" : "km_wizard_create",
                        click: km_wizard_create_check
                    }
                ]
            }).addClass("km_wizard_dialog");
        }
        km_wizard_error();
        dialog.dialog("open");
        return false;
    }

    var km_wizard_create_check = function () {
        km_wizard_error();

        if ($("#km_wizard_name").val().length == 0) {
            km_wizard_error(tr("Please enter service name"));
        } else if ($("#km_wizard_name").val().length > 40) {
            km_wizard_error(tr("Please enter shorter service name"));
        } else {
            var template = $("#km_wizard_list li.km_wizard_template_active").attr("rel"),
                URL      = $("#km_wizard_url").val();

            if (template || URL) {
                km_preloader_show(tr("Creating service, please wait"));
                $.post(KAM_ROOT + "ajax/wizard_create", {
                    name : $("#km_wizard_name").val(),
                    url : URL,
                    template : template
                }, function (data) {
                    km_preloader_hide();
                    if (data.status == 1) {
                        document.location = KAM_ROOT + "wizard";
                    } else {
                        km_wizard_error(data.error || tr("Error creating service"));
                    }
                }, "json");
            } else {
                km_wizard_error(tr("Please choose template"));
            }
        }
    }

    var km_wizard_load_templates = function () {
        km_preloader_show();
        $.getJSON(KAM_ROOT + "ajax/wizard_list_templates", function (data) {
            km_preloader_hide();

            var family_ul = $("#km_wizard_family ul").empty(),
                list_ul   = $("#km_wizard_list ul").empty();

            if (data.status == 1) {
                $.each(data.families, function (k, family) {
                    $("<li></li>").text(family).attr("rel", k).appendTo(family_ul);
                });

                $.each(data.templates, function (template, tmb) {
                    var tmp = template.split("/");
                    $("<li></li>").attr("rel", template).append(
                        $("<img/>").attr("src", "data:image/jpg;base64," + tmb)
                    ).append(
                        $("<span></span>").html(tmp[1] || tmp[0])
                    ).appendTo(list_ul);
                });


                $("<hr/>").appendTo(family_ul);

                $("<li></li>").text(tr("My template")).attr("rel", "my_template").appendTo(family_ul);
                var my_templates_li = $("<li></li>").attr("rel", "my_template").appendTo(list_ul);

                $("<ul></ul>").text(tr("URL address")).append(
                    $("<li></li>").append(
                        $("<input/>").attr("type", "text").attr("id", "km_wizard_url").addClass("km_wizard_input")
                    )
                ).appendTo(my_templates_li);

                delete data.families;
                delete data.templates;
                delete data.status;

                $.each(data, function (type, templates) {
                    if ($.isEmptyObject(templates) == false) {
                        var my_ul = $("<ul></ul>").text(tr("Files: " + type)).appendTo(my_templates_li);
                        $.each(templates, function (id, name) {
                            $("<li></li>").html(name).attr("rel", type + ":" + id).appendTo(my_ul);
                        });
                    }
                });

                km_wizard_update_templates();
            } else {
                km_wizard_error(data.error || tr("Error fetching templates list"));
            }
        });
    }

    var km_wizard_link = function (e) {
        var URL = $(this).attr("href"),
            rel = $(this).attr("rel");

        var link = this;

        KamConfirm(tr("Are you sure you want to " + rel + " website?"), function () {
            km_preloader_show();
            $.getJSON(URL, function (data) {
                km_preloader_hide();
                if (data.status == 1) {
                    $(link).parent().hide();
                    if (rel == "trash") {
                        $("#km_wizard_trash").show();
                    }
                    if (rel == "remove") {
                        $(".wizard_trash_title").toggle(!data.is_empty)
                        $(".wizard_trash_empty").toggle(data.is_empty);
                    }
                } else {
                    KamDialog(data.error || tr("Save error"));
                }
            });
        });
        return false;
    }

    var km_wizard_rename = function () {
        var link = $(this);
        KamPrompt(tr("New service name"), tr("Rename service"), function (value) {
            km_wizard_error();

            if (value.length == 0) {
                km_wizard_error(tr("Please enter service name"));
            } else if (value.length > 40) {
                km_wizard_error(tr("Please enter shorter service name"));
            } else {
                km_preloader_show();
                $.post(link.attr("href"), {
                    name : value
                }, function (data) {
                    km_preloader_hide();
                    if (data.status == 1) {
                        link.siblings(".wizard_service_name").text(value).attr("title", value);
                    } else {
                        km_wizard_error(data.error || tr("Error renaming service"));
                    }
                }, "json");
            }
        }, link.siblings(".wizard_service_name").text());

        return false;
    }

	//zapamietaj zawartość wszystkich li z szablonami
	var km_crousel = $("#km_wizard_list > ul li[carousel]").clone();
	//zapamietaj zawartość wszystkich li z thumb-ami szablonów
	var km_crousel_thumb = $("#thumb-list > li").clone();
	
    var km_wizard_update_templates = function () {
        if ($("#km_wizard_family li.km_wizard_family_active").length == 0) {
            $("#km_wizard_family li:first").addClass("km_wizard_family_active");
        }
		$("#km_wizard_list > ul li[family]").hide().filter("[family^=" + $("#km_wizard_family li.km_wizard_family_active").attr("rel") + "]").show();

		//wyczysc class wszystkich li, aby kliknieciu aktywne w carousel były tylko te co trzeba
		$("#km_wizard_list > ul li[carousel]").attr("class","");

		//wybierz wszystkie li do caruseli kliknięte przez usera
		var aLi = km_crousel.filter("[carousel^=" + $("#km_wizard_family li.km_wizard_family_active").attr("rel") + "]");

		//ustaw klasę do carousel
		aLi.attr("class","item");
		//ustaw pierwszy item jako active
		aLi.filter(function (index) {
			if (index==0) return true;
		}).attr("class","item active");

		//wyczysc zawartosc kontenera karuzeli
		$(".carousel-inner").empty();
		//wstaw do karuzeli to co kliknął user
		aLi.each(function () {
			var elem = $(this).clone();
			$(".carousel-inner").append(elem);
		});
	   
		//wybierz wszystkie li do thumba, które kliknął user
		var aLiThumb = km_crousel_thumb.filter("[carousel-thumb^=" + $("#km_wizard_family li.km_wizard_family_active").attr("rel") + "]");
		//wyczyść li
		$("#thumb-list").empty();
		// i dodaj li które kliknął user
		aLiThumb.each(function (index) {
			var elem = $(this).clone();
			//ponumeruj li od zera aby po liknięciu pokazać właściwy slide w carousel
			elem.attr("data-slide-to",index);
			$("#thumb-list").append(elem);
		});
    }

    var km_wizard_error = function (error) {
        if (error == null) {
            $(".km_wizard_error").hide();
        } else {
            $(".km_wizard_error").html(error).show();
        }
    }

    if ($("#km_wizard_create").attr("popup_mode") == 1) {
        $("#km_wizard_create").on("click", km_wizard_create);
    }

    $("#km_wizard_family").on("click", "li[rel]", function (e) {
        ga('send','event','wizard/family',$(this).attr("rel"));
	
        $(this).siblings().removeClass("km_wizard_family_active");
        $(this).addClass("km_wizard_family_active");
	$("#carousel-wizard").show();
	if ($(this).attr("rel")=="drive" || $(this).attr("rel")=="default") $("#carousel-wizard").hide();
        km_wizard_update_templates();
    });

    km_wizard_update_templates();

    $("#km_wizard_list").on("click", "li[rel]", function (e) {
        if ($(this).children("ul").length)
            return false;

        $("#km_wizard_list li[rel]").removeClass("km_wizard_template_active");
        $(this).addClass("km_wizard_template_active");
    });

    $("body").on("click", ".wizard_service_link[rel]", km_wizard_link);
    $("body").on("click", ".wizard_service_rename", km_wizard_rename);

    $("body").on("click", "#km_wizard_create_submit", function (e) {
        var template = $("#km_wizard_list li.km_wizard_template_active").attr("rel"),
            URL      = $("#km_wizard_url").val();

        if (URL || template) {
            $("input[name='wizard[template]']").val(URL || template).parent().submit();
        } else {
            KamDialog(tr("Please choose template"));
        }
        return false;
    });
	
    $("#km_wizard_list").on("click", "input[rel]", function (e) {
        var template = $(this).attr("rel");
        if (template) {
            
            //in case of ad block of analytics
            setTimeout(function() {
                $("input[name='wizard[template]']").val(template).parent().submit();
            },1000);
            
	    
	    
            ga('send', 'event', {
                        'eventCategory': 'wizard/name',
                        'eventAction': template,
                        'hitCallback': function() {
                            $("input[name='wizard[template]']").val(template).parent().submit();
                        }
            });             
            
            
        } else {
            KamDialog(tr("Please choose template"));
        }
        return false;
    });	
	
    if (location.hash.length){
            var val = location.hash.substring(1,location.hash.length-1);
            $("#km_wizard_family li").filter("[rel^="+ val + "]").trigger("click");
    }
	
        
});


