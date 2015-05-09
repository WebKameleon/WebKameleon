var wkw_gmap_array=[];

function wkw_gmap_add(div_id,typeId,lat,lng,zoom,marker,title,icon)
{
    if (typeof(wkw_gmap_array[div_id])=='undefined') {
        wkw_gmap_array[div_id]={};
        
        wkw_gmap_array[div_id].latlng=new google.maps.LatLng(lat, lng);
        wkw_gmap_array[div_id].opt = {
            zoom: zoom,
            center: wkw_gmap_array[div_id].latlng,
            mapTypeId : typeId
        };
        
        wkw_gmap_array[div_id].map = new google.maps.Map(document.getElementById(div_id),wkw_gmap_array[div_id].opt);
        wkw_gmap_array[div_id].markers = [];
        wkw_gmap_array[div_id].point=null;
    };

    if (icon!=null) {
        wkw_gmap_array[div_id].point  = new google.maps.MarkerImage(
            icon.url,
            new google.maps.Size(icon.w, icon.h),
            new google.maps.Point(0, 0),
            new google.maps.Point(parseInt(icon.w)/2, parseInt(icon.h)/2)
        );
    }
    
    if (marker) {
        wkw_gmap_array[div_id].markers.push(new google.maps.Marker({
            position : new google.maps.LatLng(lat,lng),
            icon: wkw_gmap_array[div_id].point,
            map : wkw_gmap_array[div_id].map,
            title: title
        }));        

    }
    
    if (wkw_gmap_array[div_id].markers.length>1) {
        var latlngbounds = new google.maps.LatLngBounds();
        for (var i=0; i<wkw_gmap_array[div_id].markers.length; i++) {
            latlngbounds.extend(wkw_gmap_array[div_id].markers[i].getPosition());
        }
        
        wkw_gmap_array[div_id].map.setCenter(latlngbounds.getCenter());
        wkw_gmap_array[div_id].map.fitBounds(latlngbounds);
    }

    
}