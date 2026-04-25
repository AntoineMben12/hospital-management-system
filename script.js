// ============================================================
// script.js — MediCore HMS
// ============================================================

/* ── Sidebar toggle (mobile) ─────────────────────────────────── */
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  if (sidebar) sidebar.classList.toggle('open');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function (e) {
  const sidebar = document.getElementById('sidebar');
  const toggle  = document.querySelector('.menu-toggle');
  if (!sidebar) return;
  if (window.innerWidth > 768) return;
  if (sidebar.classList.contains('open') &&
      !sidebar.contains(e.target) &&
      e.target !== toggle) {
    sidebar.classList.remove('open');
  }
});

/* ── Confirm delete ──────────────────────────────────────────── */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.confirm-delete');
  if (!btn) return;
  const name = btn.dataset.name || 'this record';
  if (!confirm('Are you sure you want to delete ' + name + '?\n\nThis action cannot be undone.')) {
    e.preventDefault();
  }
});

/* ── Auto-dismiss flash alerts ───────────────────────────────── */
(function () {
  const flash = document.getElementById('flashAlert');
  if (flash) {
    setTimeout(function () {
      flash.style.transition = 'opacity .4s ease, transform .4s ease';
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-8px)';
      setTimeout(function () { flash.remove(); }, 400);
    }, 4000);
  }
})();

/* ── Client-side form validation ─────────────────────────────── */
document.addEventListener('submit', function (e) {
  const form = e.target;
  if (!form.hasAttribute('novalidate')) return;

  const requiredFields = form.querySelectorAll('[required]');
  let firstInvalid = null;

  requiredFields.forEach(function (field) {
    field.classList.remove('field-error');
    const val = field.value.trim();

    if (val === '') {
      field.classList.add('field-error');
      if (!firstInvalid) firstInvalid = field;
    } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      field.classList.add('field-error');
      if (!firstInvalid) firstInvalid = field;
    } else if (field.type === 'number') {
      const min = field.getAttribute('min');
      const max = field.getAttribute('max');
      const num = parseFloat(val);
      if (isNaN(num) || (min !== null && num < +min) || (max !== null && num > +max)) {
        field.classList.add('field-error');
        if (!firstInvalid) firstInvalid = field;
      }
    }
  });

  if (firstInvalid) {
    e.preventDefault();
    firstInvalid.focus();
    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    showInlineError(firstInvalid);
  }
});

function showInlineError(field) {
  const existing = field.parentNode.querySelector('.inline-error');
  if (existing) return;
  const msg = document.createElement('p');
  msg.className  = 'inline-error';
  msg.style.cssText = 'color:#dc2626;font-size:12px;margin-top:4px;';
  msg.textContent = field.type === 'email'
    ? 'Please enter a valid email address.'
    : 'This field is required.';
  field.parentNode.appendChild(msg);
  setTimeout(function () { msg.remove(); }, 3500);
}

/* Remove error styling on input ──────────────────────────────── */
document.addEventListener('input', function (e) {
  if (e.target.classList.contains('field-error')) {
    e.target.classList.remove('field-error');
    const msg = e.target.parentNode.querySelector('.inline-error');
    if (msg) msg.remove();
  }
});

/* Add field-error CSS rule dynamically ───────────────────────── */
(function () {
  const style = document.createElement('style');
  style.textContent = '.field-error { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,.12) !important; }';
  document.head.appendChild(style);
})();
