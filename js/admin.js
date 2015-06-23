jQuery(document).ready(function($) {
  // Shop Hours Editor
  var syrupToggle = function(){
    $('.syrup-toggle').each(function(){
      var hidden = $(this).find('input[type="hidden"]');
      var checkbox = $(this).find('input[type="checkbox"]');
      if (checkbox.is(':checked')) {
        hidden.val('on');
      } else {
        hidden.val('off');
      }
    });
  };

  var editor = $('#syrup-shop-hours-editor');
  var form = editor.find('form');
  var list = form.find('ul');
  if (0 < editor.length) {
    $('#syrup-shop-hours-editor-new').click(function(e){
      e.preventDefault();

      $('#syrup-shop-hours-editor-template > li').clone().appendTo(list);
      syrupToggle();
    });
  }

  form.submit(function(){
    syrupToggle();
  });

  editor.on('click', 'a.delete', function(e){
    e.preventDefault();

    $(this).parents('li').remove();
  });

  syrupToggle();

  // Location Mini Map
  $('.syrup-location-preview').each(function(){
    var root = $(this);
    var getPos = function(){
      var lat = root.find('input.lat').val();
      var lng = root.find('input.lng').val();
      if (lat === '0' && lng === '0') {
        lat = '35.680907802';
        lng = '139.767122085';
      }
      return new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
    };
    var setPos = function(pos){
      root.find('input.lat').val(pos.lat());
      root.find('input.lng').val(pos.lng());
    };
    var update = function(){
      var id = root.data('map');
      var pos = getPos();
      var map = root.data('instance');
      if (!map) {
        map = new google.maps.Map(document.getElementById(id), { zoom: 13 });
        root.data('instance', map);
      }
      map.setCenter(pos);
      var marker = root.data('marker');
      if (!marker) {
        marker = new google.maps.Marker({
          position: pos,
          map: map,
          draggable: true
        });
        google.maps.event.addListener(marker, 'dragend', function(){
          setPos(marker.getPosition());
        });
        root.data('marker', marker);
      }
      marker.setPosition(pos);
    };
    root.find('input.lat').keyup(function(){update();});
    root.find('input.lng').keyup(function(){update();});
    update();
  });
});
