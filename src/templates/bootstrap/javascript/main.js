$(document).ready(function() {
  $('div.name').click(function() {
    $(this).next('.expand-button, .toggle-button').click();
  });
  $('.expand-button').each(function(index) {
    $(this).click(function(e) {
      if ($(this).parent().hasClass('open')) {
        $(this).next('.more').slideUp('fast');
      } else {
        $(this).next('.more').slideDown('fast');
      }
      $(this).parent().toggleClass('open').toggleClass('closed');
    });
  });
  $('.toggle-button').each(function(index) {
    $(this).click(function(e) {
      var div = $(this).next('.source-listing');
      $(this).parent().toggleClass('open').toggleClass('closed');
    });
  });
});
