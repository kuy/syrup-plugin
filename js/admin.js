jQuery(document).ready(function($) {
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
});
