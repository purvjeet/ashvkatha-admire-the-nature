/**
 * Ashvkatha – Admire the Nature
 * Core JavaScript: Ambient Audio, Video Controls, Dynamic Booking, Mobile Nav, Scroll Reveal
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ------------------------------------------------------------
     1. Ambient Nature Background Music
     ------------------------------------------------------------ */
  const audio = document.getElementById('ambient-audio');
  const audioBtn = document.getElementById('audio-toggle-btn');
  const iconMuted = document.getElementById('audio-icon-muted');
  const iconPlaying = document.getElementById('audio-icon-playing');

  if (audio && audioBtn) {
    let isPlaying = false;

    // Check localStorage preference
    if (localStorage.getItem('ashvkatha_audio') === 'playing') {
      audio.play().then(() => {
        isPlaying = true;
        updateAudioIcons();
      }).catch(() => {
        // Autoplay policy prevented playback until user interaction
        isPlaying = false;
        updateAudioIcons();
      });
    }

    function updateAudioIcons() {
      if (iconMuted && iconPlaying) {
        iconMuted.style.display = isPlaying ? 'none' : 'block';
        iconPlaying.style.display = isPlaying ? 'block' : 'none';
      }
    }

    audioBtn.addEventListener('click', () => {
      if (audio.paused) {
        audio.play().then(() => {
          isPlaying = true;
          localStorage.setItem('ashvkatha_audio', 'playing');
          updateAudioIcons();
        }).catch(err => console.log('Audio playback prevented:', err));
      } else {
        audio.pause();
        isPlaying = false;
        localStorage.setItem('ashvkatha_audio', 'paused');
        updateAudioIcons();
      }
    });
  }

  /* ------------------------------------------------------------
     2. Hero Video Play / Pause Control
     ------------------------------------------------------------ */
  const heroVideo = document.getElementById('hero-bg-video');
  const videoBtn = document.getElementById('video-toggle-btn');
  const videoPauseIcon = document.getElementById('video-icon-pause');
  const videoPlayIcon = document.getElementById('video-icon-play');

  if (heroVideo && videoBtn) {
    videoBtn.addEventListener('click', () => {
      if (heroVideo.paused) {
        heroVideo.play();
        if (videoPauseIcon && videoPlayIcon) {
          videoPauseIcon.style.display = 'block';
          videoPlayIcon.style.display = 'none';
        }
        videoBtn.setAttribute('title', 'Pause Video');
      } else {
        heroVideo.pause();
        if (videoPauseIcon && videoPlayIcon) {
          videoPauseIcon.style.display = 'none';
          videoPlayIcon.style.display = 'block';
        }
        videoBtn.setAttribute('title', 'Play Video');
      }
    });
  }

  /* ------------------------------------------------------------
     3. Sanctuary Navigation & Info Drawer
     ------------------------------------------------------------ */
  const menuTriggers = document.querySelectorAll('#sanctuary-menu-trigger, #mobile-toggle-btn');
  const overlay = document.getElementById('sanctuary-drawer-overlay') || document.getElementById('mobile-nav-overlay');
  const closeBtns = document.querySelectorAll('#sanctuary-drawer-close, #mobile-close-btn');

  if (menuTriggers.length && overlay) {
    menuTriggers.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
      });
    });

    const closeNav = () => {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    };

    closeBtns.forEach(cBtn => cBtn.addEventListener('click', closeNav));
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeNav();
    });

    // Support ESC key to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && overlay.classList.contains('open')) {
        closeNav();
      }
    });
  }

  /* ------------------------------------------------------------
     4. Date Inputs Validation (Check-in / Check-out)
     ------------------------------------------------------------ */
  const checkInInput = document.getElementById('check_in');
  const checkOutInput = document.getElementById('check_out');

  if (checkInInput && checkOutInput) {
    const today = new Date().toISOString().split('T')[0];
    if (!checkInInput.getAttribute('min')) {
      checkInInput.setAttribute('min', today);
    }

    const updateCheckOutMin = () => {
      const selectedCheckIn = checkInInput.value;
      if (selectedCheckIn) {
        const nextDay = new Date(selectedCheckIn);
        nextDay.setDate(nextDay.getDate() + 1);
        const nextDayStr = nextDay.toISOString().split('T')[0];
        checkOutInput.setAttribute('min', nextDayStr);
        if (checkOutInput.value && checkOutInput.value <= selectedCheckIn) {
          checkOutInput.value = nextDayStr;
        }
      }
      calculateBookingSummary();
    };

    checkInInput.addEventListener('change', updateCheckOutMin);
    checkOutInput.addEventListener('change', () => calculateBookingSummary());
  }

  /* ------------------------------------------------------------
     5. Dynamic Booking Price Calculation
     ------------------------------------------------------------ */
  function calculateBookingSummary() {
    const checkInEl = document.getElementById('check_in');
    const checkOutEl = document.getElementById('check_out');
    const basePriceEl = document.getElementById('base-price-val');
    const nightsEl = document.getElementById('calculated-nights');
    const roomSubtotalEl = document.getElementById('room-subtotal');
    const taxEl = document.getElementById('tax-amount');
    const totalEl = document.getElementById('total-amount');

    if (!checkInEl || !checkOutEl || !basePriceEl) return;

    const basePrice = parseFloat(basePriceEl.dataset.price || 0);
    const taxRatePercent = parseFloat(basePriceEl.dataset.tax || 12);

    const d1 = new Date(checkInEl.value);
    const d2 = new Date(checkOutEl.value);

    let nights = 1;
    if (d1 && d2 && d2 > d1) {
      const diffTime = Math.abs(d2 - d1);
      nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }

    if (nightsEl) nightsEl.innerText = nights + (nights === 1 ? ' Night' : ' Nights');

    // Calculate add-on services sum
    let servicesTotal = 0;
    document.querySelectorAll('.service-checkbox:checked').forEach(cb => {
      servicesTotal += parseFloat(cb.dataset.price || 0);
      const parentCard = cb.closest('.service-select-card');
      if (parentCard) parentCard.classList.add('selected');
      const sidebarLabel = cb.closest('.sidebar-service-item');
      if (sidebarLabel) {
        sidebarLabel.style.borderColor = 'var(--warm-gold)';
        sidebarLabel.style.background = 'rgba(230, 161, 90, 0.12)';
      }
    });
    document.querySelectorAll('.service-checkbox:not(:checked)').forEach(cb => {
      const parentCard = cb.closest('.service-select-card');
      if (parentCard) parentCard.classList.remove('selected');
      const sidebarLabel = cb.closest('.sidebar-service-item');
      if (sidebarLabel) {
        sidebarLabel.style.borderColor = 'rgba(74, 92, 108, 0.35)';
        sidebarLabel.style.background = 'rgba(6, 20, 27, 0.75)';
      }
    });

    // Update experience breakdown row in room-details.php sidebar
    const expRow = document.getElementById('experiences-breakdown-row');
    const expAmount = document.getElementById('experiences-amount');
    if (expRow && expAmount) {
      expAmount.innerText = '+$' + servicesTotal.toFixed(2);
      expRow.style.display = servicesTotal > 0 ? 'flex' : 'none';
    }

    // Update experience breakdown row in booking.php summary sidebar
    const summarySvcRow = document.getElementById('summary-services-row');
    const summarySvcAmount = document.getElementById('summary-services-amount');
    if (summarySvcRow && summarySvcAmount) {
      summarySvcAmount.innerText = '+$' + servicesTotal.toFixed(2);
      summarySvcRow.style.display = servicesTotal > 0 ? 'flex' : 'none';
    }

    const roomSubtotal = basePrice * nights;
    const subtotalWithServices = roomSubtotal + servicesTotal;
    const taxAmount = (subtotalWithServices * taxRatePercent) / 100;
    const grandTotal = subtotalWithServices + taxAmount;

    if (roomSubtotalEl) roomSubtotalEl.innerText = '$' + roomSubtotal.toFixed(2);
    if (taxEl) taxEl.innerText = '$' + taxAmount.toFixed(2);
    if (totalEl) totalEl.innerText = '$' + grandTotal.toFixed(2);
  }

  // Attach change event to add-on services checkboxes
  document.querySelectorAll('.service-checkbox').forEach(cb => {
    cb.addEventListener('change', calculateBookingSummary);
  });

  // Calculate immediately on load if base price element is present
  if (document.getElementById('base-price-val')) {
    calculateBookingSummary();
  }

  /* ------------------------------------------------------------
     6. Scroll Reveal Animations
     ------------------------------------------------------------ */
  const revealElements = document.querySelectorAll('.reveal-on-scroll');
  if ('IntersectionObserver' in window && revealElements.length > 0) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal-active');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    revealElements.forEach(el => observer.observe(el));
  } else {
    revealElements.forEach(el => el.classList.add('reveal-active'));
  }

  /* ------------------------------------------------------------
     7. Sanctuary Healing Statistics Slide Dots
     ------------------------------------------------------------ */
  const statDots = document.querySelectorAll('.healing-stat-nav-dot');
  const statVal = document.getElementById('healing-stat-val');
  const statLbl = document.getElementById('healing-stat-lbl');
  const statTxt = document.getElementById('healing-stat-txt');

  if (statDots.length > 0 && statVal && statLbl && statTxt) {
    const slidesData = [
      {
        val: '3,500+',
        lbl: 'ACRES OF SAFE HAVEN',
        txt: 'Set within vast, carefully designed grounds in Junagadh, Ashvkatha is equipped with state-of-the-art facilities.'
      },
      {
        val: '24',
        lbl: 'EXCLUSIVE LUXURY SANCTUARIES',
        txt: 'Handcrafted suites and penthouses featuring private plunge pools, heated jacuzzis, and panoramic forest vistas.'
      },
      {
        val: '2,000+',
        lbl: 'PROTECTED WILDLIFE SPECIES',
        txt: 'A sanctuary dedicated to compassionate rescue, holistic animal welfare, and harmonious co-existence.'
      }
    ];

    let currentSlide = 0;
    function showSlide(index) {
      statDots.forEach((d, i) => d.classList.toggle('active', i === index));
      const s = slidesData[index];
      statVal.innerText = s.val;
      statLbl.innerText = s.lbl;
      statTxt.innerText = s.txt;
    }

    statDots.forEach((dot, idx) => {
      dot.addEventListener('click', () => {
        currentSlide = idx;
        showSlide(currentSlide);
      });
    });

    // Auto rotate every 6 seconds
    setInterval(() => {
      currentSlide = (currentSlide + 1) % slidesData.length;
      showSlide(currentSlide);
    }, 6000);
  }

});
