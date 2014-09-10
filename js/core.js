jQuery(document).ready(function($) {
  function initMaps() {
    var spots = [];
    for (var i = 0; i < SPOTS.length; i++) {
      if (SPOTS[i].name) {
        spots.push(SPOTS[i]);
      }
    }

    var coord, pos;
    var map = new google.maps.Map(document.getElementById('syrup-map'), {});
    var bounds = new google.maps.LatLngBounds();

    for (var i = 0; i < spots.length; i++) {
      coord = spots[i].coordinate.split(', ');
      pos = new google.maps.LatLng(parseFloat(coord[0]), parseFloat(coord[1]));
      bounds.extend(pos);
      new google.maps.Marker({
        position: pos,
        map: map,
        title: spots[i].name
      });
    }

    google.maps.event.addListenerOnce(map, 'bounds_changed', function(){
      if (15 < map.getZoom()) {
        map.setZoom(15);
      }
    });

    map.fitBounds(bounds);
  }

  google.maps.event.addDomListener(window, 'load', initMaps);
});
