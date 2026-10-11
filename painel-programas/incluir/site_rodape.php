<footer class="rodape">
  <div class="container rodape-inner">
    <div class="rodape-col">
      <h4><?= e(SITE_NOME) ?></h4>
      <p><?= e(SITE_DESCRICAO) ?>.</p>
    </div>
    <div class="rodape-col">
      <h4>Contato</h4>
      <p><i class="fab fa-whatsapp"></i> <a href="<?= e(url_whats('Olá! Quero saber mais.')) ?>" target="_blank">Suporte via WhatsApp</a></p>
    </div>
    <div class="rodape-col">
      <h4>Navegação</h4>
      <p><a href="index.php">Programas</a> &middot; <a href="painel/login.php">Área do administrador</a></p>
    </div>
  </div>
  <div class="rodape-base">
    <div class="container">&copy; <?= date('Y') ?> <?= e(SITE_NOME) ?>. Todos os direitos reservados.</div>
  </div>
</footer>
<script src="assets/js/site.js"></script>
</body>
</html>