    if (document.getElementsByName("longitude"))
        var longitude = document.getElementsByName("longitude")[0];
    if (document.getElementsByName("latitude"))
        var latitude  = document.getElementsByName("latitude")[0];

    function geolocaliser() {
        if (navigator.geolocation)
            navigator.geolocation.getCurrentPosition(getPos);
    }

    function getPos(position) {
        let button = document.getElementById("button");

        longitude.value = position.coords.longitude;
        latitude.value  = position.coords.latitude;
        button.innerHTML = 'Vous avez été correctement géolocalisé !';
        button.className = 'btn btn-success';
        button.setAttribute("disabled", "");
    }