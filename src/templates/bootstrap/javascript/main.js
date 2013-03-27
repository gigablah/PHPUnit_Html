$(document).ready(function() {
  $('section.suite, div.test').click(function(e) {
    el = e.srcElement || e.target;
    if (!$(el).parents('div.result').length && !$(el).parents('div.source').length) {
      $(this).find('.expand-button:first').click();
    }
    e.stopPropagation();
  });
  $('.expand-button').each(function(index) {
    $(this).click(function(e) {
      if ($(this).parent().hasClass('open')) {
        $(this).next('.more').slideUp('fast');
      } else {
        $(this).next('.more').slideDown('fast');
      }
      $(this).parent().toggleClass('open').toggleClass('closed');
      e.stopPropagation();
    });
  });
  $('.toggle-button').each(function(index) {
    $(this).click(function(e) {
      var div = $(this).next('.source-listing');
      $(this).parent().toggleClass('open').toggleClass('closed');
    });
  });
});
