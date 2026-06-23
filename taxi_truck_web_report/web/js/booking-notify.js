(function () {
  if (
    window.IS_LOGGED_IN === false ||
    window.location.pathname === '/admin/login' ||
    window.location.pathname === '/trip/create' ||
    window.location.pathname === '/statistic'
  ) return;

  const seen = new Set();
  let lastSince = null;

  function isSnoozing() {
    const t = parseInt(localStorage.getItem('toastSnoozeUntil') || '0', 10);
    return !isNaN(t) && Date.now() < t;
  }

  function fetchNewBookings(since) {
    const data = since ? { since } : {};
    return $.ajax({
      url: '/statistic/check-new-booking',
      data,
      cache: false
    });
  }

  /** =========================
   *  Render toast booking
   *  ========================= */
  function renderBookings(items) {
    if (!Array.isArray(items) || !items.length) return;

    items.forEach(function (b) {
      if (!b || !b.id || seen.has(b.id)) return;
      seen.add(b.id);

      toastr.info(
        (b.customer_name || 'Khách') + (b.phone ? ' - ' + b.phone : ''),
        'Booking mới',
        {
          positionClass: 'toast-bottom-right',
          closeButton: true,
          timeOut: 0,
          extendedTimeOut: 0,
          onclick: function () {
            window.location.href = '/trip/create?id=' + b.id;
          }
        }
      );
    });
    setTimeout(renderSnoozeButton, 0);
  }

  function checkNew() {
    if (isSnoozing()) return;

    fetchNewBookings(lastSince)
      .done(function (res) {
        if (res && res.serverTime) lastSince = res.serverTime;
        if (res && res.data && res.data.length) {
          renderBookings(res.data);
        }
      })
      .fail(function (xhr, status) {
        console.warn('Lỗi khi check booking mới:', status);
      });
  }

  function renderSnoozeButton() {
    const $container = $('#toast-container');
    if (!$container.length) return;
    const hasToast = $container.find('.toast').length > 0;
    if (!hasToast || isSnoozing()) return;

    let $btn = $('#toast-snooze-btn');
    if (!$btn.length) {
      $btn = $('<button id="toast-snooze-btn" class="toast-snooze-btn">Ẩn toàn bộ thông báo</button>');
      $container.prepend($btn);

      $btn.on('click', function () {
        const snoozeTime = Date.now() + 5 * 60 * 1000;
        localStorage.setItem('toastSnoozeUntil', snoozeTime);
        toastr.clear();
        seen.clear();
        $('#toast-snooze-btn').remove();
      });
    } else {
      $btn.text('Ẩn toàn bộ thông báo').prop('disabled', false).show();
    }
  }

  $(function () {
    renderSnoozeButton();
    checkNew();
    setInterval(function () {
      if (!isSnoozing()) {
        renderSnoozeButton();
        checkNew();
      } else {
        $('#toast-snooze-btn').hide();
      }
    }, 10000);
  });
})();
