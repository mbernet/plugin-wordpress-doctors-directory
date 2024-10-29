// Archivo: assets/js/autocomplete.js

jQuery(document).ready(function($) {
    if (typeof google === 'object' && typeof google.maps === 'object') {
        var input = document.getElementById('direccion_usuario');
        if (input) {
            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode'], // Solo direcciones
                componentRestrictions: { country: 'es' } // Restricción opcional por país (ejemplo: España)
            });

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    // El usuario no seleccionó un predicado válido
                    alert("No se encontró la ubicación. Por favor, intenta de nuevo.");
                    return;
                }

                // Obtener coordenadas
                var lat = place.geometry.location.lat();
                var lng = place.geometry.location.lng();

                // Verificar si ya existen campos ocultos
                var existingLat = $('input[name="latitud_usuario"]');
                var existingLng = $('input[name="longitud_usuario"]');

                if (existingLat.length && existingLng.length) {
                    existingLat.val(lat);
                    existingLng.val(lng);
                } else {
                    // Crear campos ocultos para latitud y longitud
                    var latInput = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'latitud_usuario')
                        .val(lat);
                    var lngInput = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'longitud_usuario')
                        .val(lng);

                    $('#form-filtro-profesionales').append(latInput, lngInput);
                }
            });
        }
    }
});