jQueryKam(function ($) {

    var photo = $("<div></div>").addClass("article_photo").on("click", function (e) {
        $("#bgimg").siblings(".km_gallery_button").trigger("click");
    }).append(
        $("<span></span>").addClass("ui-icon ui-icon-closethick").on("click", function (e) {
            $("#bgimg").val("").trigger("change");
            return false;
        })
    ).insertBefore(".km_nheader_left");

    $("<input />").attr("type", "hidden").attr("name", "article[photo]").val(1).appendTo("#td_form");

    $("#bgimg").on("change", function (e, file) {
        photo.find("img").remove();
        if (file) {
            $("<img />").attr("src", file.url).prependTo(photo);
        } else if (this.value) {
            $("<img />").attr("src", km_infos["uimages"] + "/" + this.value).prependTo(photo);
        }
    }).trigger("change");
    
    
    $('.webtd_trailer').click(function(){
        $(this).resizable();
        $(this).addClass('edit').children().addClass('edit');
    });

});