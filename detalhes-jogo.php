<?php
require_once 'api.php';

if (function_exists('fetchFromAPI')) {
  echo 'Função já existe';
  debug_print_backtrace();
  exit;
}

$api_key = '6fb874f6316b4ed9a72d32dca03e5ec8';
$game_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$game_id) {
    header('Location: index.php');
    exit;
}

?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($game_data['name']); ?> | VGR</title>
  <link rel="stylesheet" href="style6.css">
  <link rel="stylesheet" href="sugestoes.css">
</head>
<body>
  <div class="container">
    <header>
      <div class="logo">
        <a href="index.php"><img src="poster/imagens fodas/logocortada.png" alt="Logo"></a>
      </div>
      <div class="search-container">
        <form action="pesquisa.php" method="GET" id="search-form">
          <input type="text" name="query" id="search-input" placeholder="Pesquisar jogos..." autocomplete="off" required />
          <div id="suggestions-container" class="suggestions-container"></div>
        </form>
      </div>
      <a href="cadastro.php"><button class="signup-btn">Cadastre-se</button></a>
      <a href="perfil.html"><button class="signup-btn">Perfil</button></a>
    </header>

    <a href="javascript:history.back()" class="back-btn">← Voltar</a>

    <div class="game-detail">
      <div class="game-hero">
        <div class="game-poster">
          <img src="<?php echo $game_data['background_image'] ?: 'poster/imagens fodas/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($game_data['name']); ?>">
        </div>
        <div class="game-info">
          <h1 class="game-title"><?php echo htmlspecialchars($game_data['name']); ?></h1>
          <div class="game-meta">
            <?php if (!empty($game_data['rating'])): ?>
            <div class="meta-item">
              <span class="rating-badge"><?php echo $game_data['rating']; ?>/5x'</span> Avaliação
            </div>
            <?php endif; ?>
            <?php if (!empty($game_data['released'])): ?>
            <div class="meta-item">
              Lançamento: <?php echo date('d/m/Y', strtotime($game_data['released'])); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($game_data['playtime'])): ?>
            <div class="meta-item">
              Tempo de jogo: <?php echo $game_data['playtime']; ?> horas
            </div>
            <?php endif; ?>
          </div>
          <?php if (!empty($game_data['description_raw'])): ?>
          <div class="game-description">
            <?php echo nl2br(htmlspecialchars($game_data['description_raw'])); ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="game-details">
        <?php if (!empty($game_data['platforms'])): ?>
        <div class="detail-card">
          <h3>Plataformas</h3>
          <ul class="detail-list">
            <?php foreach ($game_data['platforms'] as $platform): ?>
            <li><?php echo htmlspecialchars($platform['platform']['name']); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($game_data['genres'])): ?>
        <div class="detail-card">
          <h3>Gêneros</h3>
          <ul class="detail-list">
            <?php foreach ($game_data['genres'] as $genre): ?>
            <li><?php echo htmlspecialchars($genre['name']); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($game_data['developers'])): ?>
        <div class="detail-card">
          <h3>Desenvolvedores</h3>
          <ul class="detail-list">
            <?php foreach ($game_data['developers'] as $developer): ?>
            <li><?php echo htmlspecialchars($developer['name']); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($game_data['publishers'])): ?>
        <div class="detail-card">
          <h3>Publicadoras</h3>
          <ul class="detail-list">
            <?php foreach ($game_data['publishers'] as $publisher): ?>
            <li><?php echo htmlspecialchars($publisher['name']); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($game_data['tags'])): ?>
        <div class="detail-card">
          <h3>Tags</h3>
          <ul class="detail-list">
            <?php foreach (array_slice($game_data['tags'], 0, 10) as $tag): ?>
            <li><?php echo htmlspecialchars($tag['name']); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($game_screenshots)): ?>
      <div class="screenshots-section">
        <h2 class="screenshots-title">Imagens</h2>
        <div class="screenshots-grid">
          <?php foreach (array_slice($game_screenshots, 0, 6) as $screenshot): ?>
          <div class="screenshot-item">
            <img src="<?php echo $screenshot['image']; ?>" alt="Screenshot de <?php echo htmlspecialchars($game_data['name']); ?>">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const header = document.querySelector('header');
      const gameDetail = document.querySelector('.game-detail');
      function checkHeaderOverlap() {
        if (!header || !gameDetail) return;
        const headerRect = header.getBoundingClientRect();
        const headerBottom = headerRect.bottom;
        const gameDetailTop = gameDetail.getBoundingClientRect().top;
        if (headerBottom > gameDetailTop && gameDetailTop > 0) {
          header.classList.add('transparent');
        } else {
          header.classList.remove('transparent');
        }
      }
      window.addEventListener('scroll', checkHeaderOverlap);
      checkHeaderOverlap();
    });

    document.addEventListener('DOMContentLoaded', function() {
      const API_KEY = '6fb874f6316b4ed9a72d32dca03e5ec8';
      const searchForm = document.getElementById('search-form');
      const searchInput = document.getElementById('search-input');
      const suggestionsContainer = document.getElementById('suggestions-container');
      let searchTimeout;
      async function fetchSuggestions(query) {
        if (!query || query.length < 2) {
          suggestionsContainer.classList.remove('active');
          return;
        }
        try {
          const response = await fetch(`https://api.rawg.io/api/games?key=${API_KEY}&search=${encodeURIComponent(query)}&page_size=5`);
          const data = await response.json();
          displaySuggestions(data.results);
        } catch (error) {
          console.error('Erro ao buscar sugestões:', error);
        }
      }
      function displaySuggestions(games) {
        suggestionsContainer.innerHTML = '';
        if (!games || games.length === 0) {
          suggestionsContainer.classList.remove('active');
          return;
        }
        suggestionsContainer.classList.add('active');
        games.forEach(game => {
          const suggestionItem = document.createElement('div');
          suggestion
