document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('adminMenuToggle');
  var sidebar = document.getElementById('adminSidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () { sidebar.classList.toggle('is-open'); });
  }

  // Confirm before any destructive action (delete buttons carry data-confirm)
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Generic modal open/close (used by events.php add/edit modal)
  document.querySelectorAll('[data-modal-target]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      var modal = document.getElementById(trigger.getAttribute('data-modal-target'));
      if (modal) modal.classList.add('is-open');
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('.modal-backdrop').classList.remove('is-open');
    });
  });

  var notificationRoot = document.getElementById('adminNotifications');
  if (notificationRoot) {
    var notificationToggle = document.getElementById('adminNotificationsToggle');
    var notificationPanel = document.getElementById('adminNotificationsPanel');
    var notificationList = document.getElementById('adminNotificationList');
    var notificationCount = document.getElementById('adminNotificationCount');
    var notificationEndpoint = notificationRoot.dataset.endpoint;
    var lastNotificationKey = 'yggc-admin-last-notification';

    function loadNotifications() {
      fetch(notificationEndpoint, { credentials: 'same-origin', cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (!data.success) return;
          notificationCount.textContent = data.unread_count;
          notificationCount.hidden = data.unread_count === 0;
          notificationList.replaceChildren();

          if (!data.notifications.length) {
            var empty = document.createElement('li');
            empty.className = 'admin-notification-empty';
            empty.textContent = 'No payment notifications yet.';
            notificationList.appendChild(empty);
            return;
          }

          data.notifications.forEach(function (item) {
            var row = document.createElement('li');
            var link = document.createElement('a');
            link.href = notificationRoot.dataset.donationsUrl;
            link.className = item.is_read === '0' || item.is_read === 0 ? 'is-unread' : '';
            link.textContent = 'Payment confirmed: ' + item.donor_name + ' · ' + item.currency + ' ' + Number(item.amount).toFixed(2) + ' · ' + item.transaction_ref;
            row.appendChild(link);
            notificationList.appendChild(row);
          });

          var newestId = data.notifications[0].id;
          var lastId = sessionStorage.getItem(lastNotificationKey);
          if (lastId === null) {
            sessionStorage.setItem(lastNotificationKey, newestId);
          } else if (lastId !== String(newestId) && document.body.dataset.adminPage === 'dashboard') {
            sessionStorage.setItem(lastNotificationKey, newestId);
            window.location.reload();
          }
        })
        .catch(function () {});
    }

    notificationToggle.addEventListener('click', function () {
      var opening = notificationPanel.hidden;
      notificationPanel.hidden = !opening;
      notificationToggle.setAttribute('aria-expanded', String(opening));
      if (opening) {
        fetch(notificationEndpoint, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'csrf_token=' + encodeURIComponent(notificationRoot.dataset.csrf)
        }).then(loadNotifications).catch(function () {});
      }
    });

    loadNotifications();
    window.setInterval(loadNotifications, 7000);
  }
});
