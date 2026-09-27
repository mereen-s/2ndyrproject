
'use strict';

// patient live search
function attachPatientSearch(inputId, resultsId, openPage) {
  var input = document.getElementById(inputId);
  var box   = document.getElementById(resultsId);
  if (!input || !box) return;

  var timer = null;

  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = input.value.trim();
    if (q.length < 2) { box.innerHTML = ''; return; }

    timer = setTimeout(function () {
      fetch('index.php?page=api_patient_search&q=' + encodeURIComponent(q))
        .then(function (r) {
          if (!r.ok) throw new Error('Search failed (' + r.status + ')');
          return r.json();
        })
        .then(function (rows) {
          if (!rows.length) {
            box.innerHTML = '<p class="note" style="padding:8px">No patients found.</p>';
            return;
          }
          var html = '<table><thead><tr>'
                   + '<th>ID</th><th>Name</th><th>NIC</th><th>DOB</th><th></th>'
                   + '</tr></thead><tbody>';
          rows.forEach(function (p) {
            html += '<tr>'
                  + '<td><b>' + escHtml(p.patient_id) + '</b></td>'
                  + '<td>' + escHtml(p.name) + '</td>'
                  + '<td>' + escHtml(p.nic || '–') + '</td>'
                  + '<td>' + escHtml(p.dob || '–') + '</td>'
                  + '<td><a class="btn secondary" href="index.php?page='
                  + openPage + '&pid=' + encodeURIComponent(p.patient_id) + '">Open</a></td>'
                  + '</tr>';
          });
          box.innerHTML = html + '</tbody></table>';
        })
        .catch(function (err) {
          box.innerHTML = '<p class="note" style="color:#c0392b;padding:8px">'
                        + 'Search error – please try again.</p>';
          console.error('Patient search error:', err);
        });
    }, 280);  // 280 ms debounce
  });

  // Clear results when the field is emptied
  input.addEventListener('blur', function () {
    setTimeout(function () { box.innerHTML = ''; }, 200);
  });
}

// HTML escaping
function escHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// flash message auto-dismiss
document.addEventListener('DOMContentLoaded', function () {
  var flash = document.querySelector('.flash');
  if (flash) {
    setTimeout(function () {
      flash.style.transition = 'opacity 0.6s';
      flash.style.opacity    = '0';
      setTimeout(function () { flash.remove(); }, 650);
    }, 5000);
  }
});

// confirm-on-submit helper
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });
});

// numeric-only input filter
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[data-type="numeric"]').forEach(function (el) {
    el.addEventListener('keydown', function (e) {
      var allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End','.'];
      if (allowed.includes(e.key)) return;
      if (e.key >= '0' && e.key <= '9') return;
      e.preventDefault();
    });
    // Prevent pasting non-numeric content
    el.addEventListener('paste', function (e) {
      var text = (e.clipboardData || window.clipboardData).getData('text');
      if (!/^\d*\.?\d*$/.test(text)) e.preventDefault();
    });
  });
});

// print helper
function ccwPrint() { window.print(); }
