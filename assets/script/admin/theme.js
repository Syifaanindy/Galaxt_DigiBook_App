(function () {
  var STORAGE_KEY = 'admin_theme';
  var body = document.body;

  function applyTheme(theme) {
    if (theme === 'dark') {
      body.classList.add('dark-mode');
    } else {
      body.classList.remove('dark-mode');
    }

    var btn = document.getElementById('themeToggle');
    if (btn) {
      btn.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
      btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    }
  }

  function savedTheme() {
    return localStorage.getItem(STORAGE_KEY) || 'light';
  }

  function toggleTheme() {
    var next = body.classList.contains('dark-mode') ? 'light' : 'dark';
    localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next);
  }

  var button = document.createElement('button');
  button.id = 'themeToggle';
  button.className = 'theme-toggle';
  button.type = 'button';
  button.addEventListener('click', toggleTheme);
  document.body.appendChild(button);

  applyTheme(savedTheme());
})();
