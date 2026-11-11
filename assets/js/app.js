/* ==========================================================
   TechVerse event registration - client side logic (jQuery)
   - countdown to the fest
   - multi-step navigation with progress bar
   - live field validation
   - event cards (max N), team members, live fee calculator
   - photo preview, AJAX submission, rendering of the event pass
   ========================================================== */
$(function () {
  'use strict';

  var $form = $('#regForm');
  var $steps = $form.find('.step');
  var $stepper = $('.stepper li');
  var $bar = $('.progress-bar');
  var $alert = $form.find('.form-alert');
  var $eventInputs = $form.find('input[name="events[]"]');
  var maxEvents = +$('.event-grid').data('max') || 3;
  var current = 1;
  var total = $steps.length;
  var MAX_PHOTO = 2 * 1024 * 1024;

  /* ---------- Countdown ---------- */
  var $cd = $('.countdown');
  if ($cd.length) {
    var start = new Date($cd.data('start')).getTime();
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var tick = function () {
      var diff = Math.max(0, start - Date.now());
      var s = Math.floor(diff / 1000);
      $cd.find('[data-unit=d]').text(Math.floor(s / 86400));
      $cd.find('[data-unit=h]').text(pad(Math.floor(s % 86400 / 3600)));
      $cd.find('[data-unit=m]').text(pad(Math.floor(s % 3600 / 60)));
      $cd.find('[data-unit=s]').text(pad(s % 60));
      if (!diff) { $cd.addClass('live'); clearInterval(timer); }
    };
    var timer = setInterval(tick, 1000);
    tick();
  }

  $('#dob').attr('max', new Date().toISOString().split('T')[0]);

  /* ---------- Validation rules ---------- */
  var namePattern = /^[A-Za-z][A-Za-z .'-]*$/;
  var checked = function (name) { return $form.find('input[name="' + name + '"]:checked').length > 0; };
  var isTeam = function () { return $form.find('input[name=participation]:checked').val() === 'Team'; };

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
    email: function (v) {
      if (!v) return 'Email is required.';
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Please enter a valid email address.';
    },
    phone: function (v) {
      if (!v) return 'Mobile number is required.';
      if (!/^\+?[0-9]{10,14}$/.test(v.replace(/[\s-]/g, ''))) return 'Enter a valid 10-14 digit number.';
    },
    dob: function (v) {
      if (!v) return 'Date of birth is required.';
      var age = calcAge(v);
      if (isNaN(age) || age < 0) return 'Please enter a valid date.';
      if (age < 15) return 'Participants must be at least 15 years old.';
      if (age > 60) return 'Please enter a valid date of birth.';
    },
    gender: function () { if (!checked('gender')) return 'Please select your gender.'; },
    college: function (v) { if (v.length < 3) return 'Please enter your college name.'; },
    roll_no: function (v) {
      if (!v) return 'Roll number is required.';
      if (!/^[A-Za-z0-9\/-]{3,20}$/.test(v)) return 'Use 3-20 letters, digits, "/" or "-".';
    },
    department: function (v) { if (!v) return 'Please select your department.'; },
    year: function () { if (!checked('year')) return 'Please select your year of study.'; },
    city: function (v) { if (!v) return 'City is required.'; },
    state: function (v) { if (!v) return 'State is required.'; },
    'events[]': function () {
      var n = $eventInputs.filter(':checked').length;
      if (!n) return 'Please choose at least one event.';
      if (n > maxEvents) return 'You can register for at most ' + maxEvents + ' events.';
    },
    team_name: function (v) {
      if (!isTeam()) return;
      if (v.length < 2) return 'Please enter a team name.';
    },
    'team_members[]': function () {
      if (!isTeam()) return;
      var names = $form.find('input[name="team_members[]"]').map(function () { return $.trim(this.value); }).get()
        .filter(Boolean);
      if (!names.length) return 'Add at least one team member.';
      for (var i = 0; i < names.length; i++) {
        if (!namePattern.test(names[i])) return 'Member names may contain only letters, spaces, apostrophes and hyphens.';
      }
    },
    tshirt: function (v) { if (!v) return 'Please choose a T-shirt size.'; },
    declaration: function () { if (!$('#declaration').is(':checked')) return 'Please accept the declaration to continue.'; }
  };
  // Error <span data-for> names differ from the input names for array fields
  var errorKey = { 'events[]': 'events', 'team_members[]': 'team_members' };

  function calcAge(dateStr) {
    var dob = new Date(dateStr), now = new Date();
    var age = now.getFullYear() - dob.getFullYear();
    var m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;
    return age;
  }

  function showError(key, msg) {
    var $err = $form.find('.error[data-for="' + key + '"]');
    $err.text(msg || '');
    $err.closest('.field').toggleClass('invalid', !!msg).toggleClass('valid', !msg);
  }

  function validateField(name) {
    var rule = rules[name];
    if (!rule) return true;
    var $el = $form.find('[name="' + name + '"]').first();
    var msg = rule($.trim($el.val() || ''));
    showError(errorKey[name] || name, msg);
    return !msg;
  }

  function validateStep(step) {
    var ok = true, $first = null, seen = {};
    $steps.filter('[data-step="' + step + '"]').find('[name]').each(function () {
      if (seen[this.name]) return;
      seen[this.name] = true;
      if (!validateField(this.name)) {
        ok = false;
        if (!$first) $first = $(this);
      }
    });
    if (!ok) {
      if ($first.is(':visible')) $first.trigger('focus');
      else $('html, body').animate({ scrollTop: $first.closest('.field').offset().top - 40 }, 250);
      var $active = $steps.filter('.active').addClass('shake');
      setTimeout(function () { $active.removeClass('shake'); }, 450);
    }
    return ok;
  }

  /* Live validation */
  $form.on('blur', 'input, select, textarea', function () {
    if (this.name && rules[this.name] && this.type !== 'checkbox' && this.type !== 'radio') validateField(this.name);
  });
  $form.on('input change', 'input, select, textarea', function () {
    if (this.name && rules[this.name] && $(this).closest('.field').hasClass('invalid')) validateField(this.name);
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
      $(this).find('.dot').html(s < step ? '&#10003;' : '0' + s);
    });
    $bar.css('width', (step / total * 100) + '%');
    $('html, body').animate({ scrollTop: $('#form-section').offset().top - 20 }, 300);
  }
  $form.on('click', '.next', function () { if (validateStep(current)) goTo(current + 1); });
  $form.on('click', '.prev', function () { goTo(current - 1); });

  /* ---------- Event cards: limit + fee calculator ---------- */
  function rupees(n) { return '₹' + n.toLocaleString('en-IN'); }

  function updateEvents() {
    var $sel = $eventInputs.filter(':checked');
    var n = $sel.length;
    // Disable the remaining cards once the limit is reached
    $eventInputs.not(':checked').prop('disabled', n >= maxEvents)
      .closest('.event-card').toggleClass('locked', n >= maxEvents);
    $('#eventHint').text(n ? n + ' of ' + maxEvents + ' selected' + (n >= maxEvents ? ' (maximum reached)' : '') : 'Pick at least one event.');
    updateFee();
  }

  function updateFee() {
    var lines = [], sum = 0;
    $eventInputs.filter(':checked').each(function () {
      var fee = +$(this).data('fee');
      sum += fee;
      lines.push($('<li>').append($('<span>').text($(this).data('name')), $('<b>').text(rupees(fee))));
    });
    var $acc = $form.find('input[name=accommodation]:checked');
    if ($acc.data('fee')) {
      sum += +$acc.data('fee');
      lines.push($('<li>').append($('<span>').text('Hostel stay'), $('<b>').text(rupees(+$acc.data('fee')))));
    }
    $('#feeLines').empty().append(lines.length ? lines : $('<li class="muted">').text('No events selected yet.'));
    var $total = $('#feeTotal');
    if ($total.text() !== rupees(sum)) {
      $total.text(rupees(sum)).removeClass('bump');
      void $total[0].offsetWidth; // restart the animation
      $total.addClass('bump');
    }
  }

  $eventInputs.on('change', updateEvents);
  $form.on('change', 'input[name=accommodation]', updateFee);

  /* ---------- Team members ---------- */
  var $teamBox = $('#teamBox');
  var $rows = $teamBox.find('.member-row');

  function refreshMembers() {
    var visible = $rows.filter(':not(.hidden)').length;
    $('#addMember').toggle(visible < $rows.length);
    $rows.find('.remove-member').toggle(visible > 1);
  }

  function toggleTeam(animate) {
    var on = isTeam();
    if (animate) { $teamBox[on ? 'slideDown' : 'slideUp'](250); } else { $teamBox.toggle(on); }
    if (!on) { showError('team_name', ''); showError('team_members', ''); }
  }

  // Start with one member row visible; extra rows are revealed with "Add member"
  $rows.slice(1).addClass('hidden').hide();
  refreshMembers();
  toggleTeam(false);

  $form.on('change', 'input[name=participation]', function () { toggleTeam(true); });

  $('#addMember').on('click', function () {
    var $next = $rows.filter('.hidden').first();
    $next.removeClass('hidden').hide().fadeIn(200).find('input').trigger('focus');
    refreshMembers();
  });

  $teamBox.on('click', '.remove-member', function () {
    var $row = $(this).closest('.member-row');
    $row.find('input').val('');
    // Move the emptied row to the end so names stay in order
    $row.addClass('hidden').hide().appendTo($row.parent());
    $rows = $teamBox.find('.member-row');
    $rows.each(function (i) {
      $(this).find('.member-num').text(i + 2);
      $(this).find('input').attr('placeholder', 'Member ' + (i + 2) + ' full name');
    });
    refreshMembers();
  });

  /* ---------- Extras ---------- */
  $('#dob').on('change', function () {
    var age = calcAge(this.value);
    $('#ageHint').text(this.value && !isNaN(age) && age >= 0 ? 'Age: ' + age + ' years' : '');
  });

  $('#phone').on('input', function () { this.value = this.value.replace(/[^0-9+\s-]/g, ''); });
  $('#roll_no').on('input', function () { this.value = this.value.toUpperCase(); });

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
    $('#ageHint').text('');
    $alert.attr('hidden', true);
    $rows.slice(1).addClass('hidden').hide();
    $rows.first().removeClass('hidden').show();
    refreshMembers();
    toggleTeam(false);
    updateEvents();
    goTo(1);
  }

  $('#resetBtn').on('click', function () {
    if (!confirm('Clear everything you have entered?')) return false;
    setTimeout(clearUI, 0); // let the native reset run first
  });

  /* ---------- Submit via AJAX ---------- */
  $form.on('submit', function (e) {
    e.preventDefault();

    for (var s = 1; s <= total; s++) {
      if (!validateStep(s)) {
        if (s !== current) { goTo(s); validateStep(s); }
        return;
      }
    }

    var $btn = $('#submitBtn');
    $btn.prop('disabled', true).addClass('loading').find('.btn-text').text('Registering...');
    $alert.attr('hidden', true);
    $eventInputs.prop('disabled', false); // disabled inputs are not submitted; re-enable before sending
    var formData = new FormData(this);
    updateEvents();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .done(function (res) {
        if (res && res.success) {
          $('#form-section').slideUp(350, function () {
            var $result = $('#result-section').hide().html(res.html);
            if (window.renderQRCodes) window.renderQRCodes($result[0]);
            $result.fadeIn(400);
            $('html, body').animate({ scrollTop: $result.offset().top - 20 }, 300);
          });
        } else {
          handleErrors(res && res.errors, res && res.message);
        }
      })
      .fail(function (xhr) {
        var res = xhr.responseJSON;
        if (res && res.errors) handleErrors(res.errors, res.message);
        else $alert.text('Could not reach the server. Please try again in a moment.').removeAttr('hidden');
      })
      .always(function () {
        $btn.prop('disabled', false).removeClass('loading').find('.btn-text').text('Register Now');
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

  /* ---------- "Register another participant" on the pass ---------- */
  $('#result-section').on('click', '.js-new', function (e) {
    e.preventDefault();
    $('#result-section').fadeOut(250, function () {
      $(this).empty().show();
      $form[0].reset();
      clearUI();
      $('#form-section').slideDown(350);
    });
  });

  updateEvents();
});
