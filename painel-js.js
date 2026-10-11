function abrirWhatsApp(texto) {
  if (!texto) return;
  const url = 'https://wa.me/?text=' + encodeURIComponent(texto);
  window.open(url, '_blank');
}
document.addEventListener('click', function(e){
  if (e.target.closest('.js-whats')) {
    const texto = e.target.closest('.js-whats').dataset.text;
    if (texto) abrirWhatsApp(texto);
  }
});