(function ($, Drupal) {
    Drupal.behaviors.popupMenu = {
      attach: function (context, settings) {
        // Popup toggle and close
        $('.popup-wrapper', context).each(function () {
          const wrapper = this;
          const button = wrapper.querySelector('.menu-btn');
          const popup = wrapper.querySelector('.popup-menu');
          const closeBtn = popup.querySelector('.popup-close');
  
          if (button && !button.classList.contains('popup-bound')) {
            button.classList.add('popup-bound');
            button.addEventListener('click', function (e) {
              e.stopPropagation();
              popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
            });
          }
  
          if (closeBtn && !closeBtn.classList.contains('popup-bound')) {
            closeBtn.classList.add('popup-bound');
            closeBtn.addEventListener('click', function () {
              popup.style.display = 'none';
            });
          }
  
          // Close popup when clicking outside
          if (!wrapper.classList.contains('popup-outside-bound')) {
            wrapper.classList.add('popup-outside-bound');
            document.addEventListener('click', function (e) {
              if (!wrapper.contains(e.target)) {
                popup.style.display = 'none';
              }
            });
          }
        });
  
        // Clap button tracking
        $('.clap-btn', context).each(function () {
          if (!this.classList.contains('popup-bound')) {
            this.classList.add('popup-bound');
            this.addEventListener('click', function () {
              let count = parseInt(this.textContent.replace(/\D/g, '')) || 0;
              count++;
              this.textContent = `👏 ${count}`;
            });
          }
        });
  
        // Bookmark toggle
        $('.bookmark-btn', context).each(function () {
          if (!this.classList.contains('popup-bound')) {
            this.classList.add('popup-bound');
            this.addEventListener('click', function () {
              this.classList.toggle('bookmarked');
              this.textContent = this.classList.contains('bookmarked') ? '🔖 Saved' : '🔖';
            });
          }
        });
      }
    };
  })(jQuery, Drupal);
  