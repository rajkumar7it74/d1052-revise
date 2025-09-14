(function ($, Drupal) {
    Drupal.behaviors.dobDateRestriction = {
      attach: function (context, settings) {
        const $dobField = $('#edit-dob', context);
  
        if ($dobField.length && !$dobField.data('dob-restricted')) {
          const today = new Date();
          const eighteenYearsAgo = new Date();
          eighteenYearsAgo.setFullYear(today.getFullYear() - 18);
  
          $dobField.attr('max', today.toISOString().split('T')[0]);
          $dobField.val(eighteenYearsAgo.toISOString().split('T')[0]);

          // Mark as processed to avoid duplicate execution
          $dobField.data('dob-restricted', true);
        }
      }
    };

    Drupal.behaviors.aadhaarValidation = {
        attach: function (context, settings) {
          // Select the Aadhaar field
          const $aadhaarField = $('#edit-aadhar', context);

          // Prevent multiple bindings using a custom flag
          if ($aadhaarField.length && !$aadhaarField.data('aadhaar-bound')) {
            $aadhaarField.on('input', function () {
              let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
              let formatted = value.match(/.{1,4}/g)?.join('-') || '';
              $(this).val(formatted);
            });
            $aadhaarField.on('blur', function () {
              const value = $(this).val();
              const pattern = /^[2-9]{1}[0-9]{3}-[0-9]{4}-[0-9]{4}$/;
              if (!pattern.test(value)) {
                $('#adhaar_validation_error').text("Please enter valid Adhaar Format and should start from 2-9.");
                $('#adhaar_validation_error').css('color', 'red');
                $(this).focus();
              }     
              else {
                  $('#adhaar_validation_error').text('');
                  $('#adhaar_validation_error').css('color', '');
                  $(this).blur();
              }
            });
            
            // Mark as bound to prevent duplicate event handlers
            $aadhaarField.data('aadhaar-bound', true);
          }
        }
      };

    Drupal.behaviors.phoneValidation = {
      attach: function (context, settings) {
        const $phoneField = $('#edit-phone', context);
        if ($phoneField.length && !$phoneField.data('phone-bound')) {
          $phoneField.on('blur', function () {
            const value = $(this).val();
            const pattern = /^(0|91)?[6-9][0-9]{9}$/;
            if (!pattern.test(value)) {
              $('#phone_validation_error').text("Please enter valid phone number.");
              $('#phone_validation_error').css('color', 'red');
              $(this).focus();
            }     
            else {
              $('#phone_validation_error').text('');
              $('#js_validation_error').css('color', '');
              $(this).blur();
            }
          });
          // Mark as bound to prevent duplicate event handlers
          $phoneField.data('phone-bound', true);
        }
      }
    };

    Drupal.behaviors.emailValidation = {
      attach: function (context, settings) {
        const $emailField = $('#edit-email', context);
        if ($emailField.length && !$emailField.data('email-bound')) {
          $emailField.on('blur', function () {
            const value = $(this).val();
            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!pattern.test(value)) {
              $('#email_validation_error').text("Please enter valid email.");
              $('#email_validation_error').css('color', 'red');
              $(this).focus();
            }     
            else {
              $('#email_validation_error').text('');
              $('#email_validation_error').css('color', '');
              $(this).blur();
            }
            // Mark as bound to prevent duplicate event handlers
            $emailField.data('email-bound', true);
          });
        }
      }
    };
      
  })(jQuery, Drupal);
  