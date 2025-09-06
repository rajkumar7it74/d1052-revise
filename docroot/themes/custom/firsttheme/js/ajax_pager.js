(function ($, Drupal) {
    Drupal.behaviors.ajaxPager = {
      attach: function (context, settings) {
        // Target pager links with 'use-ajax' class
        $('.pager a.use-ajax', context).once('ajax-pager').each(function () {
          const elementSettings = {
            url: $(this).attr('href'),
            event: 'click',
            progress: {
              type: 'throbber',
              message: 'Loading more content...',
            }
          };
  
          Drupal.ajax(this, this, elementSettings);
        });
      }
    };
  })(jQuery, Drupal);
  