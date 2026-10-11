document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.aviso').forEach(function (aviso) {
    setTimeout(function () { aviso.style.transition = 'opacity 0.5s'; aviso.style.opacity = '0'; }, 5000);
    setTimeout(function () { aviso.remove(); }, 5600);
  });
});