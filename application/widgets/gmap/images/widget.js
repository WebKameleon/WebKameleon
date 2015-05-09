km_w2_szukaj = function () {
    var adres = document.getElementById('km_w2_adres').value;
    var geo = new google.maps.Geocoder();
    if (!geo) {
        return;
    }
    var tab = new Array();
    tab['address'] = adres;
    geo.geocode(tab, function (wyniki, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            km_w2_marker.setPosition(wyniki[0].geometry.location);
            km_w2_mapa.setCenter(wyniki[0].geometry.location);
            km_w2_savepos(wyniki[0].geometry.location);
        }
    });
}

km_w2_savepos = function (loc) {
    jQueryKam('#in_options_lat').val(loc.lat());
    jQueryKam('#in_options_lng').val(loc.lng());
}

jQueryKam(function ($) {
    $('#szukaj_adresu').on('click', km_w2_szukaj);
});
