<?php
session_start();
require_once 'api.php';

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

$api_key = '6fb874f6316b4ed9a72d32dca03e5ec8';
$game_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$game_id) {
    header('Location: index.php');
    exit;
}

$game_url = "https://api.rawg.io/api/games/{$game_id}?key={$api_key}";
$game_data = fetchFromAPI($game_url);

$screenshots_url = "https://api.rawg.io/api/games/{$game_id}/screenshots?key={$api_key}";
$screenshots_data = fetchFromAPI($screenshots_url);
$game_screenshots = isset($screenshots_data['results']) ? $screenshots_data['results'] : [];

if (isset($game_data['detail']) && $game_data['detail'] === 'Not found.') {
    header('Location: index.php');
    exit;
}

// Inicializar arrays de sessão


if (!isset($_SESSION['game_comments'])) {
    $_SESSION['game_comments'] = [];
}
if (!isset($_SESSION['game_comments'][$game_id])) {
    $_SESSION['game_comments'][$game_id] = [];
}
if (!isset($_SESSION['comment_likes'])) {
    $_SESSION['comment_likes'] = [];
}
if (!isset($_SESSION['comment_dislikes'])) {
    $_SESSION['comment_dislikes'] = [];
}

// Manipular envios de formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar novo comentário
    if (isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
        $comment = htmlspecialchars(trim($_POST['comment']));
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $author = $_SESSION['user_name'] ?? 'Usuário Anônimo'; // Usa da sessão!
        $avatar = $_SESSION['user_avatar'] ?? 'poster/avatars/1.jpg'; // Usa da sessão!
        
        $new_comment = [
            'author' => $author,
            'date' => strftime('%d de %B de %Y'),
            'rating' => max(0, min(5, $rating)),
            'content' => $comment,
            'likes' => 0,
            'dislikes' => 0,
            'avatar' => $avatar // Agora usa o personalizado!
        ];
        
        $_SESSION['game_comments'][$game_id][] = $new_comment;
    }
    
    // Manipular curtida
    if (isset($_POST['like_comment'])) {
        $comment_index = intval($_POST['like_comment']);
        $comment_key = $game_id . '_' . $comment_index;
        
        if (!isset($_SESSION['comment_likes'][$comment_key]) && isset($_SESSION['game_comments'][$game_id][$comment_index])) {
            $_SESSION['comment_likes'][$comment_key] = true;
            $_SESSION['game_comments'][$game_id][$comment_index]['likes'] = ($_SESSION['game_comments'][$game_id][$comment_index]['likes'] ?? 0) + 1;
            
            // Remover dislike se existir
            if (isset($_SESSION['comment_dislikes'][$comment_key])) {
                unset($_SESSION['comment_dislikes'][$comment_key]);
                $_SESSION['game_comments'][$game_id][$comment_index]['dislikes'] = max(0, ($_SESSION['game_comments'][$game_id][$comment_index]['dislikes'] ?? 0) - 1);
            }
        }
    }
    
    // Manipular dislike
    if (isset($_POST['dislike_comment'])) {
        $comment_index = intval($_POST['dislike_comment']);
        $comment_key = $game_id . '_' . $comment_index;
        
        if (!isset($_SESSION['comment_dislikes'][$comment_key]) && isset($_SESSION['game_comments'][$game_id][$comment_index])) {
            $_SESSION['comment_dislikes'][$comment_key] = true;
            $_SESSION['game_comments'][$game_id][$comment_index]['dislikes'] = ($_SESSION['game_comments'][$game_id][$comment_index]['dislikes'] ?? 0) + 1;
            
            // Remover curtida se existir
            if (isset($_SESSION['comment_likes'][$comment_key])) {
                unset($_SESSION['comment_likes'][$comment_key]);
                $_SESSION['game_comments'][$game_id][$comment_index]['likes'] = max(0, ($_SESSION['game_comments'][$game_id][$comment_index]['likes'] ?? 0) - 1);
            }
        }
    }
}

$comments = $_SESSION['game_comments'][$game_id];

// Função para parsear data em português
function parse_pt_date($date_str) {
    $months = [
        'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'abril' => 4,
        'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
        'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12
    ];
    $date_str = strtolower(trim($date_str));
    $parts = explode(' de ', $date_str);
    if (count($parts) !== 3) {
        return time();
    }
    $day = intval($parts[0]);
    $month_str = trim($parts[1], ',');
    $year = intval($parts[2]);
    $month = isset($months[$month_str]) ? $months[$month_str] : 1;
    return mktime(0, 0, 0, $month, $day, $year);
}

// Ordenar comentários por data (mais recente primeiro)
usort($comments, function($a, $b) {
    return parse_pt_date($b['date']) - parse_pt_date($a['date']);
});
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($game_data['name'] ?? 'Detalhes do Jogo'); ?> | VGR</title>
    <link rel="stylesheet" href="style7.css">
    <link rel="stylesheet" href="sugestoes.css">
    <link rel="stylesheet" href="comentários.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <img src="<?php echo htmlspecialchars($game_data['background_image'] ?? 'poster/imagens fodas/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($game_data['name'] ?? 'Jogo'); ?>">
                    <div class="play-icon"><i class="fas fa-play"></i></div>
                </div>
                <div class="game-info">
                    <h1 class="game-title"><?php echo htmlspecialchars($game_data['name'] ?? 'Nome do Jogo'); ?></h1>
                    <div class="game-meta">
                        <?php if (!empty($game_data['rating'])): ?>
                        <div class="meta-item">
                            <span class="rating-badge"><?php echo htmlspecialchars($game_data['rating']); ?>/5</span> Avaliação
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($game_data['released'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i> Lançamento: <?php echo date('d/m/Y', strtotime($game_data['released'])); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($game_data['playtime'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i> Tempo de jogo: <?php echo htmlspecialchars($game_data['playtime']); ?> horas
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
                    <h3><i class="fas fa-tv"></i> Plataformas</h3>
                    <ul class="detail-list">
                        <?php foreach ($game_data['platforms'] as $platform): ?>
                        <li><i class="fas fa-gamepad"></i> <?php echo htmlspecialchars($platform['platform']['name'] ?? ''); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($game_data['genres'])): ?>
                <div class="detail-card">
                    <h3><i class="fas fa-tags"></i> Gêneros</h3>
                    <ul class="detail-list">
                        <?php foreach ($game_data['genres'] as $genre): ?>
                        <li><i class="fas fa-circle"></i> <?php echo htmlspecialchars($genre['name'] ?? ''); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($game_data['developers'])): ?>
                <div class="detail-card">
                    <h3><i class="fas fa-code"></i> Desenvolvedores</h3>
                    <ul class="detail-list">
                        <?php foreach ($game_data['developers'] as $developer): ?>
                        <li><i class="fas fa-laptop-code"></i> <?php echo htmlspecialchars($developer['name'] ?? ''); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($game_data['publishers'])): ?>
                <div class="detail-card">
                    <h3><i class="fas fa-building"></i> Publicadoras</h3>
                    <ul class="detail-list">
                        <?php foreach ($game_data['publishers'] as $publisher): ?>
                        <li><i class="fas fa-building"></i> <?php echo htmlspecialchars($publisher['name'] ?? ''); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($game_data['tags'])): ?>
                <div class="detail-card">
                    <h3><i class="fas fa-hashtag"></i> Tags</h3>
                    <ul class="detail-list">
                        <?php foreach (array_slice($game_data['tags'], 0, 10) as $tag): ?>
                        <li><i class="fas fa-tag"></i> <?php echo htmlspecialchars($tag['name'] ?? ''); ?></li>
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
                        <img src="<?php echo htmlspecialchars($screenshot['image'] ?? ''); ?>" alt="Screenshot de <?php echo htmlspecialchars($game_data['name'] ?? 'Jogo'); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="reviews-section">
                <h2 class="screenshots-title">Avaliações e Comentários</h2>
                
                <div class="user-actions">
                    <form method="POST" class="comment-form" id="commentForm">
                        <input class="nome-author" type="text" name="author" placeholder="Seu nome (opcional)" class="author-input">
                        <div class="rating-stars" id="ratingStarsComment">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                            <input type="hidden" name="rating" id="ratingValueComment" value="0">
                        </div>
                        <textarea class="comment-input" name="comment" placeholder="Escreva seu comentário..." required></textarea>
                        <button type="submit" class="submit-comment">Enviar Comentário</button>
                    </form>
                </div>
                
                <div class="reviews-list">
                    <?php foreach ($comments as $index => $comment): ?>
                    <div class="review">
                        <div class="review-header">
                            <img src="<?php echo htmlspecialchars($comment['avatar'] ?? 'poster/avatars/1.jpg'); ?>" alt="Avatar" class="user-avatar">
                            <div class="user-info">
                                <div class="review-author"><?php echo htmlspecialchars($comment['author'] ?? 'Usuário Anônimo'); ?></div>
                                <div class="review-date"><?php echo htmlspecialchars($comment['date'] ?? strftime('%d de %B de %Y')); ?></div>
                            </div>
                            <div class="review-rating">
                                <?php echo str_repeat('★', max(0, min(5, $comment['rating'] ?? 0))) . str_repeat('☆', 5 - max(0, min(5, $comment['rating'] ?? 0))); ?>
                            </div>
                        </div>
                        <div class="review-content">
                            <div class="comment-box">
                                <?php echo htmlspecialchars($comment['content'] ?? ''); ?>
                            </div>
                        </div>
                        <div class="review-actions">
                            <form method="POST" class="like-form">
                                <input type="hidden" name="like_comment" value="<?php echo $index; ?>">
                                <button type="submit" class="like-btn <?php echo isset($_SESSION['comment_likes'][$game_id . '_' . $index]) ? 'active' : ''; ?>">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="like-count"><?php echo htmlspecialchars($comment['likes'] ?? 0); ?></span>
                                </button>
                            </form>
                            <form method="POST" class="dislike-form">
                                <input type="hidden" name="dislike_comment" value="<?php echo $index; ?>">
                                <button type="submit" class="dislike-btn <?php echo isset($_SESSION['comment_dislikes'][$game_id . '_' . $index]) ? 'active' : ''; ?>">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="dislike-count"><?php echo htmlspecialchars($comment['dislikes'] ?? 0); ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
        
        const commentStars = document.querySelectorAll('#ratingStarsComment .star');
        const commentRatingInput = document.getElementById('ratingValueComment');
        let selectedCommentRating = 0;
        
        commentStars.forEach(star => {
            star.addEventListener('click', (e) => {
                e.preventDefault();
                const rating = parseInt(star.getAttribute('data-rating'));
                selectedCommentRating = rating;
                commentRatingInput.value = rating;
                
                commentStars.forEach((s, index) => {
                    s.classList.toggle('active', index < rating);
                });
            });
            
            star.addEventListener('mouseover', () => {
                const rating = parseInt(star.getAttribute('data-rating'));
                commentStars.forEach((s, index) => {
                    s.style.color = index < rating ? '#f5c518' : '#555';
                });
            });
            
            star.addEventListener('mouseout', () => {
                commentStars.forEach((s, index) => {
                    s.style.color = index < selectedCommentRating ? '#f5c518' : '#555';
                });
            });
        });

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
                displaySuggestions(data.results || []);
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
                suggestionItem.className = 'suggestion-item';
                suggestionItem.innerHTML = `
                    <img src="${game.background_image || 'poster/imagens fodas/placeholder.jpg'}" alt="${game.name}">
                    <div class="suggestion-info">
                        <div class="suggestion-name">${game.name}</div>
                        <div class="suggestion-meta">${game.released ? 'Lançamento: ' + new Date(game.released).getFullYear() : 'Data não disponível'}</div>
                    </div>
                `;
                
                suggestionItem.addEventListener('click', () => {
                    window.location.href = `detalhes-jogo.php?id=${game.id}`;
                });
                
                suggestionsContainer.appendChild(suggestionItem);
            });
        }
        
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchSuggestions(searchInput.value);
            }, 300);
        });
        
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.length >= 2) {
                fetchSuggestions(searchInput.value);
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.classList.remove('active');
            }
        });
    });
    </script>
</body>
</html>