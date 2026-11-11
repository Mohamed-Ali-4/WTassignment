/* ==========================================================
   Online Admission Portal - client side logic (jQuery)
   - multi-step navigation with progress bar
   - live field validation
   - photo preview, character counter, age hint
   - AJAX submission to submit.php and rendering of the result
   ========================================================== */
$(function () {
  'use strict';

  var $form = $('#appForm');
  var $steps = $form.find('.step');
  var $stepper = $('.stepper li');
  var $bar = $('.progress-bar');
  var $alert = $form.find('.form-alert');
  var current = 1;
  var total = $steps.length;
  var MAX_PHOTO = 2 * 1024 * 1024;

  /* ---------- Populate "year of passing" ---------- */
  var thisYear = new Date().getFullYear();
  var $year = $('#passing_year');
  for (var y = thisYear; y >= thisYear - 30; y--) {
    $year.append($('<option>', { value: y, text: y }));
  }

  /* ---------- Date of birth limits ---------- */
  var today = new Date().toISOString().split('T')[0];
  $('#dob').attr('max', today);

  /* ---------- Validation rules ---------- */
  var namePattern = /^[A-Za-z][A-Za-z .'-]*$/;
  var rules = {
    first_name: function (v) {
      if (!v) return 'First name is required.';
      if (v.length < 2) return 'Please enter at least 2 characters.';
      if (!namePattern.test(v)) return 'Only letters, spaces, apostrophes and hyphens allowed.';
    },
    last_name: function (v) {
      if (!v) return 'Last name is required.';
      if (!namePattern.test(v)) return 'Only letters, spaces, apostrophes and hyphens allowed.';
    },
    dob: function (v) {
      if (!v) return 'Date of birth is required.';
      var age = calcAge(v);
      if (isNaN(age) || age < 0) return 'Please enter a valid date.';
      if (age < 15) return 'Applicants must be at least 15 years old.';
      if (age > 100) return 'Please enter a valid date of birth.';
    },
    gender: function () {
      if (!$('input[name="gender"]:checked').length) return 'Please select your gender.';
    },
    nationality: function (v) { if (!v) return 'Please select your nationality.'; },
    program: function (v) { if (!v) return 'Please choose a program.'; },
    mode: function () {
      if (!$('input[name="mode"]:checked').length) return 'Please select a study mode.';
    },
    qualification: function (v) { if (!v) return 'Please select your qualification.'; },
    percentage: function (v) {
      if (v === '') return 'Percentage is required.';
      var n = parseFloat(v);
      if (isNaN(n) || n < 0 || n > 100) return 'Enter a value between 0 and 100.';
    },
    passing_year: function (v) { if (!v) return 'Please select the year.'; },
    institution: function (v) {
      if (!v) return 'Institution name is required.';
      if (v.length < 3) return 'Please enter the full name.';
    },
    email: function (v) {
      if (!v) return 'Email is required.';
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Please enter a valid email address.';
    },
    phone: function (v) {
      if (!v) return 'Mobile number is required.';
      if (!/^\+?[0-9]{10,14}$/.test(v.replace(/[\s-]/g, ''))) return 'Enter a valid 10-14 digit number.';
    },
    address: function (v) {
      if (!v) return 'Address is required.';
      if (v.length < 5) return 'Please enter your full address.';
    },
    city: function (v) { if (!v) return 'City is required.'; },
    state: function (v) { if (!v) return 'State is required.'; },
    pincode: function (v) {
      if (!v) return 'PIN / ZIP code is required.';
      if (!/^[A-Za-z0-9 -]{4,10}$/.test(v)) return 'Please enter a valid code.';
    },
    declaration: function () {
      if (!$('#declaration').is(':checked')) return 'You must accept the declaration to continue.';
    }
  };

  function calcAge(dateStr) {
    var dob = new Date(dateStr);
    var now = new Date();
    var age = now.getFullYear() - dob.getFullYear();
    var m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;
    return age;
  }

  function showError(name, msg) {
    var $err = $form.find('.error[data-for="' + name + '"]');
    var $field = $err.closest('.field');
    $err.text(msg || '');
    $field.toggleClass('invalid', !!msg).toggleClass('valid', !msg);
  }

  function validateField(name) {
    var rule = rules[name];
    if (!rule) return true;
    var $el = $form.find('[name="' + name + '"]');
    var val = $.trim($el.val() || '');
    var msg = rule(val);
    showError(name, msg);
    return !msg;
  }

  function validateStep(step) {
    var ok = true;
    var $first = null;
    var seen = {};
    $steps.filter('[data-step="' + step + '"]').find('[name]').each(function () {
      var name = this.name;
      if (seen[name]) return;
      seen[name] = true;
      if (!validateField(name)) {
        ok = false;
        if (!$first) $first = $(this);
      }
    });
    if (!ok) {
      $first.trigger('focus');
      $steps.filter('.active').addClass('shake');
      setTimeout(function () { $steps.removeClass('shake'); }, 450);
    }
    return ok;
  }

  /* Live validation */
  $form.on('blur', 'input, select, textarea', function () {
    if (this.name && rules[this.name]) validateField(this.name);
  });
  $form.on('input change', 'input, select, textarea', function () {
    var $f = $(this).closest('.field');
    if (this.name && rules[this.name] && $f.hasClass('invalid')) validateField(this.name);
  });
  $form.on('change', 'input[type=radio], input[type=checkbox]', function () {
    if (rules[this.name]) validateField(this.name);
  });

  /* ---------- Step navigation ---------- */
  function goTo(step) {
    current = step;
    $steps.removeClass('active').filter('[data-step="' + step + '"]').addClass('active');
    $stepper.each(function () {
      var s = +$(this).data('step');
      $(this).toggleClass('active', s === step).toggleClass('done', s < step);
      $(this).find('.dot').html(s < step ? '&#10003;' : s);
    });
    $bar.css('width', (step / total * 100) + '%');
    $('html, body').animate({ scrollTop: $('#form-section').offset().top - 20 }, 300);
  }

  $form.on('click', '.next', function () {
    if (validateStep(current)) goTo(current + 1);
  });
  $form.on('click', '.prev', function () { goTo(current - 1); });

  /* ---------- Extras ---------- */
  $('#dob').on('change', function () {
    var age = calcAge(this.value);
    $('#ageHint').text(this.value && !isNaN(age) && age >= 0 ? 'Age: ' + age + ' years' : '');
  });

  $('#statement').on('input', function () {
    $('#charCount').text(this.value.length);
  });

  $('#phone, #pincode').on('input', function () {
    this.value = this.value.replace(this.id === 'phone' ? /[^0-9+\s-]/g : /[^A-Za-z0-9 -]/g, '');
  });

  $('#photo').on('change', function () {
    var file = this.files && this.files[0];
    var $img = $('#photoPreview');
    showError('photo', '');
    if (!file) { $img.attr('hidden', true).attr('src', ''); $('.photo-placeholder').show(); return; }
    if (!/^image\/(png|jpe?g|webp)$/.test(file.type)) {
      showError('photo', 'Only JPG, PNG or WEBP images are allowed.');
      this.value = '';
      return;
    }
    if (file.size > MAX_PHOTO) {
      showError('photo', 'Image must be smaller than 2 MB.');
      this.value = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      $img.attr('src', e.target.result).removeAttr('hidden').hide().fadeIn(250);
      $('.photo-placeholder').hide();
    };
    reader.readAsDataURL(file);
  });

  function clearUI() {
    $form.find('.field').removeClass('invalid valid');
    $form.find('.error').text('');
    $('#photoPreview').attr('hidden', true).attr('src', '');
    $('.photo-placeholder').show();
    $('#charCount').text('0');
    $('#ageHint').text('');
    $alert.attr('hidden', true);
    goTo(1);
  }

  $('#resetBtn').on('click', function () {
    if (!confirm('Clear all the information you have entered?')) return false;
    setTimeout(clearUI, 0); // let the native reset run first
  });

  /* ---------- Submit via AJAX ---------- */
  $form.on('submit', function (e) {
    e.preventDefault();

    // Validate every step; jump to the first one with a problem
    for (var s = 1; s <= total; s++) {
      if (!validateStep(s)) {
        if (s !== current) { goTo(s); validateStep(s); }
        return;
      }
    }

    var $btn = $('#submitBtn');
    $btn.prop('disabled', true).addClass('loading').find('.btn-text').text('Submitting...');
    $alert.attr('hidden', true);

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: new FormData(this),
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .done(function (res) {
        if (res && res.success) {
          $('#form-section').slideUp(350, function () {
            $('#result-section').hide().html(res.html).fadeIn(400);
            $('html, body').animate({ scrollTop: 0 }, 300);
          });
        } else {
          handleErrors(res && res.errors, res && res.message);
        }
      })
      .fail(function (xhr) {
        var res = xhr.responseJSON;
        if (res && res.errors) {
          handleErrors(res.errors, res.message);
        } else {
          $alert.text('Could not reach the server. Please try again in a moment.').removeAttr('hidden');
        }
      })
      .always(function () {
        $btn.prop('disabled', false).removeClass('loading').find('.btn-text').text('Submit Application');
      });
  });

  function handleErrors(errors, message) {
    var firstStep = null;
    $.each(errors || {}, function (name, msg) {
      showError(name, msg);
      var step = $form.find('.error[data-for="' + name + '"]').closest('.step').data('step');
      if (step && (firstStep === null || step < firstStep)) firstStep = step;
    });
    $alert.text(message || 'Please correct the highlighted fields.').removeAttr('hidden');
    if (firstStep && firstStep !== current) goTo(firstStep);
  }

  /* ---------- Buttons inside the result card ---------- */
  // (Print button uses an inline onclick so it also works on the no-JS fallback page.)
  $('#result-section').on('click', '.js-new', function (e) {
    e.preventDefault();
    $('#result-section').fadeOut(250, function () {
      $(this).empty().show();
      $form[0].reset();
      clearUI();
      $('#form-section').slideDown(350);
    });
  });
});
