jQueryKam(function ($) {
    $(".breadcrumbs > ul > li > span").on("click", function (e) {
        var ul = $(this).next("ul").slideToggle();
        $(".breadcrumbs > ul > li > ul").not(ul).slideUp();
    });
});