document.addEventListener('DOMContentLoaded', function () {
  var programaSelect = document.getElementById('programa-select');
  var planoSelect = document.getElementById('plano-select');
  var valorInput = document.getElementById('valor-input');
  var inicioInput = document.querySelector('input[name="data_inicio"]');
  var fimInput = document.querySelector('input[name="data_fim"]');
  var formAluguel = document.querySelector('.cartao-form form[action="aluguel-salvar.php"]');

  function precoAtual() {
    if (!programaSelect || !programaSelect.selectedOptions[0]) return null;
    var opt = programaSelect.selectedOptions[0];
    var campo = 'data-' + (planoSelect ? planoSelect.value : 'dia');
    return opt.getAttribute(campo);
  }

  function preencherValor() {
    if (!valorInput) return;
    var preco = precoAtual();
    if (preco === null || preco === '') return;
    var v = preco.replace('.', ',');
    valorInput.value = v;
  }

  function dataFim(passado, dias) {
    var d = new Date(passado);
    d.setDate(d.getDate() + dias);
    return d.toISOString().slice(0, 10);
  }

  if (planoSelect) planoSelect.addEventListener('change', function () {
    preencherValor();
    if (inicioInput && inicioInput.value && fimInput) {
      var dias = parseInt(planoSelect.selectedOptions[0].value === 'dia' ? 1 : planoSelect.selectedOptions[0].value === 'semana' ? 7 : 30, 10);
      fimInput.value = dataFim(inicioInput.value, dias);
    }
  });

  if (programaSelect) programaSelect.addEventListener('change', preencherValor);
  if (inicioInput) inicioInput.addEventListener('change', function () {
    if (!fimInput || !planoSelect) return;
    var dias = parseInt(planoSelect.value === 'dia' ? 1 : planoSelect.value === 'semana' ? 7 : 30, 10);
    fimInput.value = dataFim(inicioInput.value, dias);
  });

  if (formAluguel) {
    var criandoNovo = !formAluguel.querySelector('input[name="id"]') || formAluguel.querySelector('input[name="id"]').value === '0';
    if (criandoNovo && valorInput && (!valorInput.value || valorInput.value === '0,00' || valorInput.value === 'R$ 0,00')) {
      setTimeout(preencherValor, 0);
    }
  }

  var alertas = document.querySelectorAll('.alerta');
  alertas.forEach(function (a) {
    setTimeout(function () { a.remove(); }, 5000);
  });
});