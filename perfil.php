<?php
session_start();

// Inicializar variáveis de sessão
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Usuário Anônimo';
}
if (!isset($_SESSION['user_avatar'])) {
    $_SESSION['user_avatar'] = 'poster/avatars/1.jpg'; // Avatar padrão
}
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = []; // Jogos favoritos
}
if (!isset($_SESSION['custom_list'])) {
    $_SESSION['custom_list'] = []; // Lista personalizada
}
if (!isset($_SESSION['game_comments'])) {
    $_SESSION['game_comments'] = []; // Comentários
}

// API para buscar detalhes dos jogos (favoritos/listas)
require_once 'api.php';
$api_key = '6fb874f6316b4ed9a72d32dca03e5ec8';

// Função para buscar detalhes de um jogo
function getGameDetails($game_id) {
    global $api_key;
    $game_url = "https://api.rawg.io/api/games/{$game_id}?key={$api_key}";
    return fetchFromAPI($game_url);
}

// Buscar detalhes dos jogos favoritos e da lista
$favorites = [];
foreach ($_SESSION['favorites'] as $game_id) {
    $game = getGameDetails($game_id);
    if ($game && !isset($game['detail'])) {
        $favorites[] = $game;
    }
}
$custom_list = [];
foreach ($_SESSION['custom_list'] as $game_id) {
    $game = getGameDetails($game_id);
    if ($game && !isset($game['detail'])) {
        $custom_list[] = $game;
    }
}

// Buscar comentários (de todos os jogos)
$comments = [];
foreach ($_SESSION['game_comments'] as $game_id => $game_comments) {
    $game = getGameDetails($game_id);
    foreach ($game_comments as $comment) {
        $comments[] = [
            'game_name' => $game['name'] ?? 'Jogo Desconhecido',
            'game_id' => $game_id,
            'content' => $comment['content'],
            'rating' => $comment['rating'],
            'date' => $comment['date']
        ];
    }
}
// Ordenar comentários por data (mais recente primeiro)
usort($comments, function($a, $b) {
    $months = [
        'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'abril' => 4,
        'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
        'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12
    ];
    $parseDate = function($date_str) use ($months) {
        $date_str = strtolower(trim($date_str));
        $parts = explode(' de ', $date_str);
        if (count($parts) !== 3) return time();
        $day = intval($parts[0]);
        $month = $months[trim($parts[1])] ?? 1;
        $year = intval($parts[2]);
        return mktime(0, 0, 0, $month, $day, $year);
    };
    return $parseDate($b['date']) - $parseDate($a['date']);
});

// Manipular formulário de busca para adicionar jogos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $game_id = intval($_POST['game_id']);
    $list_type = $_POST['list_type'];
    if ($game_id > 0) {
        if ($list_type === 'favorites' && !in_array($game_id, $_SESSION['favorites'])) {
            $_SESSION['favorites'][] = $game_id;
        } elseif ($list_type === 'custom_list' && !in_array($game_id, $_SESSION['custom_list'])) {
            $_SESSION['custom_list'][] = $game_id;
        }
        header('Location: perfil.php'); // Recarrega a página
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Perfil do Usuário | VGR</title>
  <link rel="stylesheet" href="style4.css" />
  <link rel="stylesheet" href="sugestoes.css" />
  <link rel="stylesheet" href="header.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body>
  <main class="Cabeçalho">
    <div class="logo">
      <a href="index.php"><img src="poster/imagens fodas/logocortada.png" alt="Logo"></a>
    </div>
    <div class="search-container">
      <form id="search-form" method="GET" action="pesquisa.php">
        <input type="text" id="search-input" name="query" placeholder="Pesquisar jogos..." autocomplete="off">
        <div id="suggestions-container" class="suggestions-container"></div>
      </form>
    </div>
    <a href="cadastro.php"><button class="signup-btn">Cadastre-se</button></a>
    <a href="perfil.php"><button class="signup-btn">Perfil</button></a>
  </main>

  <div class="container profile-container">
    <!-- Header do Perfil -->
    <header class="profile-header">
      <div class="profile-avatar-container">
        <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Foto de Perfil" class="profile-avatar" />
        <button class="change-avatar-btn" onclick="document.getElementById('avatarUpload').click()">
          <i class="fas fa-camera"></i>
        </button>
        <input type="file" id="avatarUpload" accept="image/*" style="display:none" onchange="handleAvatarUpload(event)">
      </div>
      <div class="profile-info">
        <form id="profileForm">
          <input type="text" id="userName" name="user_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" class="username-input" />
          <button type="submit" class="save-profile-btn">Salvar Perfil</button>
        </form>
        <p class="bio">Personalize seu nome e avatar! Eles aparecerão nos seus comentários.</p>
        <div class="stats">
          <div><strong><?php echo count($_SESSION['favorites']); ?></strong> favoritos</div>
          <div><strong><?php echo count($_SESSION['custom_list']); ?></strong> na lista</div>
          <div><strong><?php echo count($comments); ?></strong> reviews</div>
        </div>
      </div>
    </header>

    <!-- Adicionar Jogo -->
    <section class="section">
      <h2>Adicionar Jogo</h2>
      <form id="addGameForm" method="POST" class="game-search-form">
        <select name="list_type" class="game-list-select">
          <option value="favorites">Favoritos</option>
          <option value="custom_list">Minha Lista</option>
        </select>
        <input type="hidden" name="game_id" id="game_id">
        <div class="game-search-container">
          <input type="text" id="game_search" placeholder="Buscar jogo para adicionar..." autocomplete="off">
          <div id="game_suggestions" class="suggestions-container"></div>
        </div>
        <button type="submit" name="add_game" class="add-game-btn">Adicionar</button>
      </form>
    </section>

    <!-- Favoritos -->
    <section class="section">
      <h2>Favoritos</h2>
      <div class="card-list">
        <?php if (empty($favorites)): ?>
          <p>Nenhum jogo favorito adicionado.</p>
        <?php else: ?>
          <?php foreach ($favorites as $game): ?>
            <div class="activity-card">
              <img src="<?php echo htmlspecialchars($game['background_image'] ?? 'poster/imagens fodas/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>" />
              <div class="card-info">
                <h3><?php echo htmlspecialchars($game['name']); ?></h3>
                <p>Adicionado aos favoritos</p>
                <div class="extra-info">
                  <p><strong>Plataformas:</strong> <?php echo htmlspecialchars(implode(', ', array_map(function($p) { return $p['platform']['name']; }, $game['platforms'] ?? []))); ?></p>
                  <p><strong>Descrição:</strong> <?php echo htmlspecialchars(substr(strip_tags($game['description'] ?? 'Sem descrição'), 0, 100)) . '...'; ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Minha Lista -->
    <section class="section">
      <h2>Minha Lista</h2>
      <div class="card-list">
        <?php if (empty($custom_list)): ?>
          <p>Nenhum jogo na sua lista.</p>
        <?php else: ?>
          <?php foreach ($custom_list as $game): ?>
            <div class="activity-card">
              <img src="<?php echo htmlspecialchars($game['background_image'] ?? 'poster/imagens fodas/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>" />
              <div class="card-info">
                <h3><?php echo htmlspecialchars($game['name']); ?></h3>
                <p>Adicionado à lista</p>
                <div class="extra-info">
                  <p><strong>Plataformas:</strong> <?php echo htmlspecialchars(implode(', ', array_map(function($p) { return $p['platform']['name']; }, $game['platforms'] ?? []))); ?></p>
                  <p><strong>Descrição:</strong> <?php echo htmlspecialchars(substr(strip_tags($game['description'] ?? 'Sem descrição'), 0, 100)) . '...'; ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Histórico de Comentários -->
    <section class="section">
      <h2>Histórico de Comentários</h2>
      <div class="card-list">
        <?php if (empty($comments)): ?>
          <p>Você ainda não fez comentários.</p>
        <?php else: ?>
          <?php foreach ($comments as $comment): ?>
            <div class="activity-card">
              <div class="card-info">
                <h3><?php echo htmlspecialchars($comment['game_name']); ?></h3>
                <p>Avaliado em <?php echo str_repeat('★', $comment['rating']) . str_repeat('☆', 5 - $comment['rating']); ?></p>
                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                <p><small><?php echo htmlspecialchars($comment['date']); ?></small></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <script>
    // Upload de avatar
    let uploadedAvatarBase64 = null;
    function handleAvatarUpload(evt) {
      const file = evt.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => {
        document.querySelector('.profile-avatar').src = reader.result;
        uploadedAvatarBase64 = reader.result;
      };
      reader.readAsDataURL(file);
    }

    // Salvar perfil
    document.getElementById('profileForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData();
      formData.append('user_name', document.getElementById('userName').value);
      if (uploadedAvatarBase64) {
        formData.append('avatar', uploadedAvatarBase64);
      }
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'save-profile.php');
      xhr.onload = () => {
        if (xhr.status === 200) {
          alert('Perfil salvo com sucesso!');
          location.reload();
        } else {
          alert('Erro ao salvar perfil.');
        }
      };
      xhr.send(formData);
    });

    // Busca de jogos
    const API_KEY = '6fb874f6316b4ed9a72d32dca03e5ec8';
    const gameSearchInput = document.getElementById('game_search');
    const gameSuggestions = document.getElementById('game_suggestions');
    const gameIdInput = document.getElementById('game_id');

    async function fetchGameSuggestions(query) {
      if (!query || query.length < 2) {
        gameSuggestions.classList.remove('active');
        return;
      }
      try {
        const response = await fetch(`https://api.rawg.io/api/games?key=${API_KEY}&search=${encodeURIComponent(query)}&page_size=5`);
        const data = await response.json();
        displayGameSuggestions(data.results || []);
      } catch (error) {
        console.error('Erro ao buscar jogos:', error);
      }
    }

    function displayGameSuggestions(games) {
      gameSuggestions.innerHTML = '';
      if (!games || games.length === 0) {
        gameSuggestions.classList.remove('active');
        return;
      }
      gameSuggestions.classList.add('active');
      games.forEach(game => {
        const suggestionItem = document.createElement('div');
        suggestionItem.className = 'suggestion-item';
        suggestionItem.innerHTML = `
          <img src="${game.background_image || 'poster/imagens fodas/placeholder.jpg'}" alt="${game.name}">
          <div class="suggestion-info">
            <div class="suggestion-name">${game.name}</div>
            <div class="suggestion-meta">${game.released ? 'Lançamento: ' + new Date(game.released).getFullYear() : 'Data não disponível'}</div>
          </div>
        `;
        suggestionItem.addEventListener('click', () => {
          gameSearchInput.value = game.name;
          gameIdInput.value = game.id;
          gameSuggestions.classList.remove('active');
        });
        gameSuggestions.appendChild(suggestionItem);
      });
    }

    gameSearchInput.addEventListener('input', () => {
      clearTimeout(window.searchTimeout);
      window.searchTimeout = setTimeout(() => {
        fetchGameSuggestions(gameSearchInput.value);
      }, 300);
    });

    gameSearchInput.addEventListener('focus', () => {
      if (gameSearchInput.value.length >= 2) {
        fetchGameSuggestions(gameSearchInput.value);
      }
    });

    document.addEventListener('click', (e) => {
      if (!gameSearchInput.contains(e.target) && !gameSuggestions.contains(e.target)) {
        gameSuggestions.classList.remove('active');
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
  const main = document.querySelector('.Cabeçalho');
  const overlapElements = document.querySelectorAll('.activity-card, .profile-header, .section'); // Elementos que acionam a transparência
  function checkHeaderOverlap() {
    let isOverElement = false;
    if (!main || overlapElements.length === 0) return;
    const headerRect = main.getBoundingClientRect();
    const headerBottom = headerRect.bottom;

    overlapElements.forEach(element => {
      const elementRect = element.getBoundingClientRect();
      const elementTop = elementRect.top;
      // Verifica se o header está sobrepondo o elemento e se o elemento está visível na tela
      if (headerBottom > elementTop && elementTop > 0 && elementRect.bottom > 0) {
        isOverElement = true;
      }
    });

    if (isOverElement) {
      main.classList.add('transparent');
    } else {
      main.classList.remove('transparent');
    }
  }

  if (window.innerWidth > 768) {
    window.addEventListener('scroll', checkHeaderOverlap);
    checkHeaderOverlap();
  }

  // Ajustar padding para evitar sobreposição do header
  const profileContainer = document.querySelector('.profile-container');
  if (main && profileContainer) {
    const headerHeight = main.offsetHeight;
    profileContainer.style.paddingTop = `${headerHeight + 20}px`; // Adiciona padding dinâmico
  }
});
  </script>
</body>
</html>