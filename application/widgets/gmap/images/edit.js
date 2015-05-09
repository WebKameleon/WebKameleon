var km_w2_marker;
var km_w2_mapa;

function km_w2_start() {
    var pos = new google.maps.LatLng(
        jQueryKam('#in_options_lat').val() || 51.501062598965646,
        jQueryKam('#in_options_lng').val() || -0.12413263320922852
    );

    var km_w2_opcjeMapy = {
        center : pos,
        mapTypeControl : false,
        mapTypeId : google.maps.MapTypeId.ROADMAP
    };

    km_w2_mapa = new google.maps.Map(document.getElementById("km_w2"), km_w2_opcjeMapy);
    km_w2_marker = new google.maps.Marker({
        position : pos,
        map : km_w2_mapa,
        visible : true
    });

    google.maps.event.addListener(km_w2_mapa, 'click', function (event) {
        if (km_w2_marker.getVisible()) {
            km_w2_marker.setPosition(event.latLng);
            km_w2_savepos(event.latLng);
        }
    });

    google.maps.event.addListener(km_w2_mapa, 'zoom_changed', function (e) {
        jQueryKam("select[name='gmap[zoom]']").val(
            km_w2_mapa.getZoom()
        );
    });

    google.maps.event.addListener(km_w2_mapa, 'maptypeid_changed', function (e) {
        jQueryKam('select[name="gmap[type]"]').val(
            km_w2_mapa.getMapTypeId()
        );
    });

    jQueryKam("select[name='gmap[zoom]']").on('change',function (e) {
        km_w2_mapa.setZoom(
            parseInt(jQueryKam(this).val(), 10)
        );
    }).trigger('change');

    jQueryKam('select[name="gmap[type]"]').on('change',function (e) {
        km_w2_mapa.setMapTypeId(
            jQueryKam(this).val()
        );
    }).trigger('change');
    
    
    checkGeo();
}

(function () {
    if (window.addEventListener) {
        window.addEventListener('load', km_w2_start, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', km_w2_start);
    }
}());
