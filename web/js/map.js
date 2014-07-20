function initialize() {
    var lat = parseFloat($('#map').data('lat'));
    var lng = parseFloat($('#map').data('lng'));

    var mapOptions = {
        scrollwheel: false,
        center: new google.maps.LatLng(lat, lng),
        zoom: 14,
        minZoom : 6
    };

    var map = new google.maps.Map(document.getElementById('map'), mapOptions);

    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(lat, lng),
        map: map,
        draggable: false
    });

}

$(document).ready(function () {
    if ($('#map').length) initialize();
});