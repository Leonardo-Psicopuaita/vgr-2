<?php include("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>VGR | Video Games Rating</title>
  <link rel="stylesheet" href="style.css" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
</head>

<body>
  <!-- Barra de categorias -->
  <nav class="categories-bar">
    <ul class="categories-list">
      <li><a href="#mais-jogados">Mais Jogados</a></li>
      <li><a href="#feitos-para-voce">Feito para Você</a></li>
      <li><a href="#lancamentos">Lançamentos Recentes</a></li>
      <li><a href="#indies">Indies em Alta</a></li>
      <li><a href="#favoritos">Favoritos da Comunidade</a></li>
    </ul>
  </nav>

  <main>
    <div class="container">
      <!-- Cabeçalho -->
      <header>
        <div class="logo">
          <a href="index.php"><img src="poster/imagens fodas/logocortada.png" alt="Logo"></a>
        </div>
        <div class="search-container">
  <form action="pesquisa.php" method="GET" id="search-form">
    <input type="text" name="query" id="search-input" placeholder="Pesquisar jogos..." required />
  </form>
</div>
        <a href="cadastro.php"><button class="signup-btn">Cadastre-se</button></a>
        <a href="perfil.html"><button class="signup-btn">Perfil</button></a>
      </header>

      <!-- Seção: Mais Jogados -->
      <div class="section" id="mais-jogados">
        <h2>Mais jogados da semana</h2>
        <div class="content">
          <div class="card">
            <img src="poster/imagens fodas/miner.png" alt="Mineirinho Ultra Adventures" class="card-img" />
            <div class="card-title">Mineirinho Ultra Adventures</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>

          <div class="card">
            <img src="poster/imagens fodas/gow.jpg" alt="God of War Ascension" class="card-img" />
            <div class="card-title">God of War Ascension</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>

          <div class="card" onclick="window.location.href='detalhes-jogo.php?id=1'">
            <img src="poster/imagens fodas/Hollow K.avif" alt="Hollow Knight" class="card-img" />
            <div class="card-title">Hollow Knight</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>

          <div class="card">
            <img src="poster/imagens fodas/hotline.jpg" alt="Hotline Miami" class="card-img" />
            <div class="card-title">Hotline Miami</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>
        </div>
      </div>

      <!-- Seção: Mundo Aberto -->
      <div class="section" id="lancamentos">
        <h2>Mundos abertos</h2>
        <div class="content">
          <div class="card">
            <img src="poster/imagens fodas/gta san anders.jpg" alt="GTA San Andreas" class="card-img" />
            <div class="card-title">GTA San Andreas</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>

          <div class="card">
            <img src="poster/imagens fodas/outerW.webp" alt="Outer Wilds" class="card-img" />
            <div class="card-title">Outer Wilds</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>

          <div class="card">
            <img src="poster/imagens fodas/ultra kill.jpg" alt="Ultrakill" class="card-img" />
            <div class="card-title">Ultrakill</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>

          <div class="card">
            <img src="poster/imagens fodas/minezin.jpg" alt="Minecraft" class="card-img" />
            <div class="card-title">Minecraft</div>
            <div class="card-subtitle">PC / Switch / PS4 / PS5 / Xbox</div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    
    // Submeter o formulário quando pressionar Enter
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (searchInput.value.trim()) {
          searchForm.submit();
        }
      }
    });
  });
</script>
</body>
</html>
