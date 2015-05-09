function KamCookie(name, value)
{
    if (typeof value == "function") {
        jQueryKam.post(KAM_ROOT + "ajax/get_cookie", {
            name : name
        }, function (data) {
            if (data.status == 1) {
                value(data.value);
            }
        }, "json");
    } else if (typeof value != "undefined") {
        jQueryKam.post(KAM_ROOT + "ajax/set_cookie", {
            name : name,
            value : value
        });
    }
}