<?php
$api_key = '6fb874f6316b4ed9a72d32dca03e5ec8';
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Função para fazer requisição à API RAWG
function fetchFromAPI($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Se não tem pesquisa, buscar jogos populares
if (!$query) {
    $url = "https://api.rawg.io/api/games?key={$api_key}&ordering=-added&page_size=12";
} else {
    $url = "https://api.rawg.io/api/games?key={$api_key}&search=" . urlencode($query) . "&page_size=12";
}

$search_results = fetchFromAPI($url);

// Se não encontrar resultados para a pesquisa, buscar jogos populares
if (empty($search_results['results']) && $query) {
    $url = "https://api.rawg.io/api/games?key={$api_key}&ordering=-added&page_size=12";
    $search_results = fetchFromAPI($url);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pesquisa: <?php echo htmlspecialchars($query); ?> | VGR</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <!-- Cabeçalho -->
    <header>
      <div class="logo">
        <a href="index.php"><img src="poster/imagens fodas/logocortada.png" alt="Logo"></a>
      </div>
      <div class="search-container">
        <form action="pesquisa.php" method="GET" id="search-form">
          <input type="text" name="query" id="search-input" placeholder="Pesquisar jogos..." value="<?php echo htmlspecialchars($query); ?>" required />
        </form>
      </div>
      <a href="cadastro.php"><button class="signup-btn">Cadastre-se</button></a>
      <a href="perfil.html"><button class="signup-btn">Perfil</button></a>
    </header>

    <section class="section">
      <h2>
        <?php if ($query): ?>
          Resultados para "<?php echo htmlspecialchars($query); ?>"
        <?php else: ?>
          Jogos Populares
        <?php endif; ?>
      </h2>
      <div class="content">
        <?php if (!empty($search_results['results'])): ?>
          <?php foreach ($search_results['results'] as $game): ?>
            <div class="card" onclick="window.location.href='detalhes-jogo.php?id=<?php echo $game['id']; ?>'">
              <img src="<?php echo $game['background_image'] ?: 'poster/imagens fodas/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($game['name']); ?>" class="card-img" />
              <div class="card-title"><?php echo htmlspecialchars($game['name']); ?></div>
              <div class="card-subtitle">
                <?php if (!empty($game['released'])): ?>
                  Lançamento: <?php echo date('Y', strtotime($game['released'])); ?>
                <?php else: ?>
                  Data de lançamento não disponível
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Nenhum resultado encontrado.</p>
        <?php endif; ?>
      </div>
    </section>
  </div>

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