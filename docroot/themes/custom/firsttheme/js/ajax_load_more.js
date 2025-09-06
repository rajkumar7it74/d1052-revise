(function ($, Drupal) {
    $(document).ready(function () {
      $('#load-more').click(function () {
        const button = $(this);
        const page = parseInt(button.attr('data-page')) + 1;
        const baseUrl = drupalSettings.path.baseUrl;
        $.ajax({
          url: baseUrl + '/load-more-articles?page=' + page,
          type: 'GET',
          success: function (data) {
            $('#ajax-content').append(data);
            button.attr('data-page', page);
          },
          error: function () {
            alert('Failed to load more content.');
          }
        });
      });
    });
  })(jQuery, Drupal);
  