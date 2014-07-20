var map;
var marker;
var address = "Киев, Украина";
var geocoder = new google.maps.Geocoder();
var lat_c;
var lng_c;
var correct = 1;

function initialize_map(lat, lng, zoom) {
    zoom = parseInt(zoom);

    if(lat == 0 && lng == 0) {
        lat = '50.454187';
        lng = '30.520928';
    }

    var map = new google.maps.Map(
        document.getElementById('map-canvas'), {
            center: new google.maps.LatLng(lat, lng),
            zoom: zoom,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false,
            mapTypeControl: false
        });

//    var infowindow = new google.maps.InfoWindow({
//        content: 'Change the zoom level',
//        position: new google.maps.LatLng(lat, lng)
//    });
//
//    infowindow.open(map);

//    google.maps.event.addListener(marker, "dragstart", function() {
//        infowindow.close(map);
//    });

    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(lat, lng),
        map: map,
        draggable: true
    });

    google.maps.event.addListener(map, "zoom_changed", function() {
        $('#zoom').val(map.getZoom())
    });

    google.maps.event.addListener(marker, "dragend", function() {
        $('#lat').val(marker.getPosition().lat());
        $('#lng').val(marker.getPosition().lng());
        updateAddress(new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng()));
    });


    var blockedFn = function(e){
        e.stopPropagation();
        e.preventDefault();
        return false;
    };
    var unblockInput = function(){
        $("#address").attr('readonly',false);
        $("#address").attr('opacity',1);
        $('#address').unbind('keydown',blockedFn);
        $('#address').bind('keydown',addressInputKeydown);
    };
    var blockInput = function(){
        $("#address").attr('readonly',true);
        $("#address").attr('opacity',0.5);
        $('#address').unbind('keydown',addressInputKeydown);
        $('#address').bind('keydown',blockedFn);
    };

    /*------------*/
    var updateAddress = function(latLng) {
        blockInput();
        geocoder.geocode({'latLng': latLng}, function(results, status) {
            unblockInput();
            if(status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    document.getElementById("address").value = results[0].formatted_address;
                } else {
                    document.getElementById("address").value = "No results";
                }
            } else {
                document.getElementById("address").value = status;
            }
        });
    };

    var updateMarkers = function(address){
        var geo = new google.maps.Geocoder;
        geo.geocode({'address':address},function(results, status){
            if (status == google.maps.GeocoderStatus.OK &&
                (results && results[0] && results[0].geometry && results[0].geometry.location)
                ) {

                var location = results[0].geometry.location;
                marker.setPosition(location);
                $('#lat').val(marker.getPosition().lat());
                $('#lng').val(marker.getPosition().lng());
                map.panTo(marker.getPosition());
            } else {
                $('#address').addClass('error');
            }
            return false;
        });
    };

    var timeout = null;

    var keyUpTimeout = function() {
        updateMarkers(document.getElementById("address").value);
    };

    var addressInputKeydown = function(e){
        $('#address').removeClass('error');
        clearTimeout(timeout);
        if (e.keyCode == 13) {
            e.preventDefault();
            e.stopPropagation();
            keyUpTimeout();
            return;
        }
        timeout = setTimeout(keyUpTimeout, 1500);
    };

    $('#address').bind('keydown',addressInputKeydown);
}

/*function getAddress(latLng) {
    geocoder.geocode( {'latLng': latLng},
        function(results, status) {
            if(status == google.maps.GeocoderStatus.OK) {
                if(results[0]) {
                    document.getElementById("address").value = results[0].formatted_address;
                }
                else {
                    document.getElementById("address").value = "No results";
                }
            }else {
                document.getElementById("address").value = status;
            }

        });
}

function getLatLong(address){
    var geo = new google.maps.Geocoder;
    geo.geocode({'address':address},function(results, status){
        if (status == google.maps.GeocoderStatus.OK) {
            console.log(results[0].geometry);
            lat_c = results[0].geometry.location.lat();
            lng_c = results[0].geometry.location.lng();
        } else {
            console.log("Geocode was not successful for the following reason: " + status);
        }
    });
}*/

$(document).ready(function() {

    if(document.getElementById('map-canvas')) {
        lat_c = lng_c = 0;
        zoom_c = 10;
        if(document.getElementById('lat')) {
            lat_c = document.getElementById('lat').value;
            lng_c = document.getElementById('lng').value;
            zoom_c = (document.getElementById('zoom').value > 0) ? document.getElementById('zoom').value : 10;
            $('#zoom').val(zoom_c);
        }
        initialize_map(lat_c, lng_c, zoom_c);
    }

});