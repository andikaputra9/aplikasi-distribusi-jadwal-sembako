document.querySelectorAll('[data-toggle-pw]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = document.getElementById(btn.dataset.togglePw);
    var tersembunyi = input.type === 'password';

    input.type = tersembunyi ? 'text' : 'password';
    btn.classList.toggle('aktif', tersembunyi);

    var label = tersembunyi ? 'Sembunyikan password' : 'Tampilkan password';
    btn.setAttribute('aria-label', label);
    btn.title = label;
  });
});