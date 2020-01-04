    var longitude = document.getElementsByName("longitude")[0];
    var latitude  = document.getElementsByName("latitude")[0];

    if (navigator.geolocation)
        navigator.geolocation.getCurrentPosition(getPos);

    function getPos(position) {
        longitude.value = position.coords.longitude;
        latitude.value  = position.coords.latitude;
    }