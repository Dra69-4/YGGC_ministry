document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Mobile nav toggle ---------- */
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('primaryNav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('is-open');
      toggle.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    // Close menu when a link is tapped (mobile)
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('is-open');
        toggle.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ---------- Sticky header shadow on scroll ---------- */
  var header = document.getElementById('site-header');
  var backToTop = document.getElementById('backToTop');
  function updateScrollState() {
    var scrolled = window.scrollY > 12;
    if (header) header.classList.toggle('is-scrolled', scrolled);
    if (backToTop) backToTop.classList.toggle('is-visible', window.scrollY > 480);
  }
  updateScrollState();
  window.addEventListener('scroll', updateScrollState, { passive: true });

  if (backToTop) {
    backToTop.addEventListener('click', function (event) {
      event.preventDefault();
      var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }

  /* ---------- Scroll-reveal ---------- */
  var revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ---------- Animated stat counters ---------- */
  var counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    var animateCount = function (el) {
      var target = parseInt(el.getAttribute('data-count'), 10) || 0;
      var duration = 1200;
      var start = null;
      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(eased * target).toLocaleString();
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString();
      }
      requestAnimationFrame(step);
    };
    if ('IntersectionObserver' in window) {
      var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCount(entry.target);
            cio.unobserve(entry.target);
          }
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { cio.observe(el); });
    } else {
      counters.forEach(animateCount);
    }
  }

  /* ---------- Gallery category filter toggle (animated, eye-catching) ---------- */
  var galleryFilters = document.getElementById('galleryFilters');
  var galleryGrid = document.getElementById('galleryGrid');
  var galleryEmpty = document.getElementById('galleryEmpty');
  var FILTER_TRANSITION_MS = 280;

  if (galleryFilters && galleryGrid) {
    var galleryItems = galleryGrid.querySelectorAll('.gallery-item');

    galleryFilters.querySelectorAll('.filter-pill').forEach(function (pill) {
      pill.addEventListener('click', function () {
        galleryFilters.querySelectorAll('.filter-pill').forEach(function (p) { p.classList.remove('is-active'); });
        pill.classList.add('is-active');

        var filter = pill.getAttribute('data-filter');
        var visibleCount = 0;

        galleryItems.forEach(function (item) {
          var match = filter === 'all' || item.getAttribute('data-category') === filter;
          if (match) visibleCount++;

          if (match) {
            // Bring a hidden item back: un-collapse it first, then let the
            // browser paint a frame before removing is-hiding so the
            // fade/scale-in transition actually plays.
            item.classList.remove('is-filtered-out');
            requestAnimationFrame(function () {
              requestAnimationFrame(function () { item.classList.remove('is-hiding'); });
            });
          } else if (!item.classList.contains('is-filtered-out')) {
            // Fade/shrink a visible item out, then collapse it out of the
            // grid once the transition finishes so the remaining photos
            // reflow smoothly instead of jumping instantly.
            item.classList.add('is-hiding');
            setTimeout(function () { item.classList.add('is-filtered-out'); }, FILTER_TRANSITION_MS);
          }
        });

        if (galleryEmpty) galleryEmpty.classList.toggle('is-hidden', visibleCount > 0);
      });
    });
  }

  /* ---------- Gallery lightbox (click a photo to view it larger) ---------- */
  var lightboxBackdrop = document.getElementById('lightboxBackdrop');
  if (lightboxBackdrop && galleryGrid) {
    var lightboxImageWrap = document.getElementById('lightboxImageWrap');
    var lightboxCaption = document.getElementById('lightboxCaption');
    var lightboxClose = document.getElementById('lightboxClose');
    var lightboxPrev = document.getElementById('lightboxPrev');
    var lightboxNext = document.getElementById('lightboxNext');
    var triggers = Array.prototype.slice.call(galleryGrid.querySelectorAll('.gallery-trigger'));
    var currentIndex = -1;

    function visibleTriggers() {
      return triggers.filter(function (t) {
        var item = t.closest('.gallery-item');
        return item && !item.classList.contains('is-filtered-out');
      });
    }

    function renderLightbox(trigger) {
      var src = trigger.getAttribute('data-src');
      var tone = trigger.getAttribute('data-tone') || '1';
      var caption = trigger.getAttribute('data-caption') || '';

      lightboxImageWrap.innerHTML = src
        ? '<img src="' + src + '" alt="' + caption.replace(/"/g, '&quot;') + '">'
        : '<div class="gallery-placeholder tone-' + tone + '"></div>';
      lightboxCaption.textContent = caption;
    }

    function openLightbox(trigger) {
      var visible = visibleTriggers();
      currentIndex = visible.indexOf(trigger);
      if (currentIndex === -1) currentIndex = 0;
      renderLightbox(visible[currentIndex] || trigger);
      lightboxBackdrop.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      lightboxBackdrop.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    function step(delta) {
      var visible = visibleTriggers();
      if (!visible.length) return;
      currentIndex = (currentIndex + delta + visible.length) % visible.length;
      renderLightbox(visible[currentIndex]);
    }

    triggers.forEach(function (trigger) {
      trigger.addEventListener('click', function () { openLightbox(trigger); });
    });
    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    if (lightboxPrev) lightboxPrev.addEventListener('click', function () { step(-1); });
    if (lightboxNext) lightboxNext.addEventListener('click', function () { step(1); });

    // Click the dark backdrop (not the image/box itself) to close.
    lightboxBackdrop.addEventListener('click', function (e) {
      if (e.target === lightboxBackdrop) closeLightbox();
    });

    document.addEventListener('keydown', function (e) {
      if (!lightboxBackdrop.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') step(-1);
      if (e.key === 'ArrowRight') step(1);
    });
  }

  /* ---------- Donation frequency toggle (one-time / monthly) ---------- */
  var freqButtons = document.querySelectorAll('.frequency-toggle button');
  var freqInput = document.getElementById('frequency');
  var donateSubmitBtn = document.getElementById('donateSubmitBtn');
  if (freqButtons.length && freqInput) {
    freqButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        freqButtons.forEach(function (b) { b.classList.remove('selected'); });
        btn.classList.add('selected');
        var freq = btn.getAttribute('data-frequency');
        freqInput.value = freq;
        if (donateSubmitBtn) {
          donateSubmitBtn.textContent = freq === 'monthly' ? 'Start Monthly Giving' : 'Give Now';
        }
      });
    });
    // Respect a ?frequency=monthly link (e.g. the homepage "Become a Monthly Partner" button)
    var params = new URLSearchParams(window.location.search);
    if (params.get('frequency') === 'monthly') {
      var monthlyBtn = document.querySelector('.frequency-toggle button[data-frequency="monthly"]');
      if (monthlyBtn) monthlyBtn.click();
    }
  }

  /* ---------- Donation amount quick-select ---------- */
  var amountButtons = document.querySelectorAll('.amount-choices button');
  var amountInput = document.getElementById('amount');
  var currencyInput = document.getElementById('currency');
  var paymentMethodInput = document.getElementById('payment_method');
  var donorPhoneInput = document.getElementById('donor_phone');
  var amountChoicesLabel = document.getElementById('amountChoicesLabel');
  function updateCurrencyChoices() {
    if (!currencyInput || !paymentMethodInput) return;
    var methodOption = paymentMethodInput.options[paymentMethodInput.selectedIndex];
    var allowedCurrencies = methodOption && methodOption.getAttribute('data-currencies')
      ? methodOption.getAttribute('data-currencies').split(',')
      : [];
    var firstAvailable = '';
    Array.prototype.forEach.call(currencyInput.options, function (option) {
      option.disabled = !allowedCurrencies.includes(option.value);
      if (!option.disabled && !firstAvailable) firstAvailable = option.value;
    });
    if (!allowedCurrencies.includes(currencyInput.value)) {
      currencyInput.value = firstAvailable;
      if (currencyInput.value) currencyInput.dispatchEvent(new Event('change'));
    }
  }

  if (amountButtons.length && amountInput) {
    function updateAmountChoices() {
      var isUgx = currencyInput && currencyInput.value === 'UGX';
      var amounts = isUgx ? [20000, 50000, 100000, 350000] : [20, 50, 100, 350];
      amountInput.min = isUgx ? '5000' : '5';
      amountInput.step = isUgx ? '1' : '0.01';
      amountInput.max = '9999999999.99';
      amountInput.value = '';
      if (amountChoicesLabel) {
        amountChoicesLabel.textContent = isUgx ? 'Choose an amount (UGX)' : 'Choose an amount (USD)';
      }
      amountButtons.forEach(function (btn, index) {
        btn.setAttribute('data-amount', amounts[index]);
        btn.textContent = isUgx ? 'USh ' + amounts[index].toLocaleString() : '$' + amounts[index];
        btn.classList.remove('selected');
      });
    }

    amountButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        amountButtons.forEach(function (b) { b.classList.remove('selected'); });
        btn.classList.add('selected');
        amountInput.value = btn.getAttribute('data-amount');
        amountInput.dispatchEvent(new Event('input'));
      });
    });
    if (currencyInput) currencyInput.addEventListener('change', updateAmountChoices);
    if (paymentMethodInput) paymentMethodInput.addEventListener('change', updateCurrencyChoices);
    updateCurrencyChoices();
    updateAmountChoices();
    amountInput.addEventListener('input', function () {
      var matched = false;
      amountButtons.forEach(function (b) {
        var match = b.getAttribute('data-amount') === amountInput.value;
        b.classList.toggle('selected', match);
        if (match) matched = true;
      });
    });
  }
  if (paymentMethodInput && donorPhoneInput) {
    function updatePhoneRequirement() {
      var isMobileMoney = paymentMethodInput.value === 'mobile_money';
      donorPhoneInput.required = isMobileMoney;
      donorPhoneInput.placeholder = isMobileMoney ? 'Required for Mobile Money' : 'Optional';
    }

    paymentMethodInput.addEventListener('change', updatePhoneRequirement);
    updatePhoneRequirement();
  }

  /* ---------- Donation form: async submit to api/donate.php ---------- */
  var donateForm = document.getElementById('donateForm');
  if (donateForm) {
    donateForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitBtn = donateForm.querySelector('button[type="submit"]');
      var resultBox = document.getElementById('donateResult');
      var originalLabel = submitBtn.textContent;

      // basic client-side validation
      var amount = parseFloat(amountInput ? amountInput.value : 0);
      if (!amount || amount <= 0) {
        showDonateMessage(resultBox, 'error', 'Please enter a valid donation amount.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Processing...';

      fetch(donateForm.getAttribute('action'), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(donateForm)
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            showDonateMessage(resultBox, 'success', data.message);
            if (data.redirect_url) {
              setTimeout(function () { window.location.href = data.redirect_url; }, 1200);
              return;
            }
            donateForm.reset();
            amountButtons.forEach(function (b) { b.classList.remove('selected'); });
          } else {
            showDonateMessage(resultBox, 'error', data.message || 'Something went wrong. Please try again.');
          }
        })
        .catch(function () {
          showDonateMessage(resultBox, 'error', 'Network error — please check your connection and try again.');
        })
        .finally(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        });
    });
  }

  function showDonateMessage(box, type, message) {
    if (!box) return;
    // Swapping the class list both restyles the box and removes .is-hidden —
    // no inline style is ever set here, all of it lives in style.css.
    box.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-error');
    box.textContent = message;
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  /* ---------- Generic contact/volunteer form via fetch (progressive enhancement) ---------- */
  document.querySelectorAll('form[data-async="true"]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitBtn = form.querySelector('button[type="submit"]');
      var resultBox = form.querySelector('.form-result');
      var originalLabel = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';

      fetch(form.getAttribute('action'), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          showDonateMessage(resultBox, data.success ? 'success' : 'error', data.message);
          if (data.success) form.reset();
        })
        .catch(function () {
          showDonateMessage(resultBox, 'error', 'Network error — please try again.');
        })
        .finally(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        });
    });
  });
});
