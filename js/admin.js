jQuery(document).ready(function($) {
  // Shop Hours Editor
  var syrup_toggle = function(){
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
      syrup_toggle();
    });
  }

  form.submit(function(){
    syrup_toggle();
  });

  editor.on('click', 'a.delete', function(e){
    e.preventDefault();

    $(this).parents('li').remove();
  });

  syrup_toggle();

  // Location Preview
  $('.syrup-location-preview').each(function(){
    var root = $(this);
    var update = function(){
      var id = root.data('map');
      var lat = root.find('input.lat').val();
      var lng = root.find('input.lng').val();
      var pos = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
      var map = root.data('instance');
      if (!map) {
        map = new google.maps.Map(document.getElementById(id), { zoom: 15 });
        root.data('instance', map);
      }
      map.setCenter(pos);
      var marker = root.data('marker');
      if (!marker) {
        marker = new google.maps.Marker({
          position: pos,
          map: map
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
