(function ($, Drupal) {
    Drupal.behaviors.popupMenu = {
      attach: function (context, settings) {
        $('.popup-wrapper', context).each(function () {
            const wrapper = this;
            const button = wrapper.querySelector('.menu-btn');
            const popup = wrapper.querySelector('.popup-menu');
            const closeBtn = popup.querySelector('.popup-close');
    
            if (!button.classList.contains('popup-bound')) {
              button.classList.add('popup-bound');
              button.addEventListener('click', function () {
                popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
              });
            }
    
            if (!closeBtn.classList.contains('popup-bound')) {
              closeBtn.classList.add('popup-bound');
              closeBtn.addEventListener('click', function () {
                popup.style.display = 'none';
              });
            }
    
            // Optional: close when clicking outside
            document.addEventListener('click', function (e) {
              if (!wrapper.contains(e.target)) {
                popup.style.display = 'none';
              }
            });
        });

        $('.menu-btn', context).each(function () {
          if (!this.classList.contains('popup-bound')) {
            this.classList.add('popup-bound');
            this.addEventListener('click', function () {
                const card = this.closest('.related-article-card');
                const popup = card.querySelector('.popup-menu');
                if (popup) {
                  popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
                }
              });
          }
        });
  
        $('.popup-close', context).each(function () {
          if (!this.classList.contains('popup-bound')) {
            this.classList.add('popup-bound');
            this.addEventListener('click', function () {
              this.closest('.popup-menu').style.display = 'none';
            });
          }
        });
  
        // Optional: Clap and Bookmark tracking
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
  