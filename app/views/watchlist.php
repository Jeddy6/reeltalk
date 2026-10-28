
<?php

if (!function_exists('chirplyWatchlistEscape')) {
    function chirplyWatchlistEscape($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

$watchlist = $watchlist ?? [];
$movies = $movies ?? [];
$csrfToken = $csrfToken ?? '';

$message = $_SESSION['watchlist_message'] ?? null;
unset($_SESSION['watchlist_message']);

$statusLabels = [
    'want_to_watch' => 'Want to Watch',
    'watching' => 'Watching',
    'watched' => 'Watched'
];

$selectedMovieIds = [];

foreach ($watchlist as $entry) {
    $selectedMovieIds[] = (int) $entry['movie_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Watchlist - Chirply</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            color-scheme: dark;
            --background: #100e17;
            --surface: #191624;
            --surface-hover: #211d30;
            --border: #302a43;
            --purple: #a78bfa;
            --purple-dark: #7c5ce0;
            --text: #f4f0ff;
            --muted: #aaa2bf;
            --danger: #ff8e9b;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 15px 20px;
        }

        .nav {
            max-width: 1050px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .brand {
            color: var(--purple);
            font-size: 25px;
            font-weight: 800;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            padding: 9px 11px;
            border-radius: 8px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #302747;
            color: var(--purple);
        }

        .nav-links form {
            margin: 0;
        }

        button,
        .button {
            display: inline-block;
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 10px 13px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            background: var(--surface-hover);
            color: var(--text);
        }

        button:hover,
        .button:hover {
            filter: brightness(1.12);
        }

        .primary {
            background: var(--purple-dark);
            color: white;
            border-color: transparent;
        }

        .danger {
            color: var(--danger);
            background: #3b202b;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 16px;
        }

        .intro,
        .movie-card,
        .message {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 22px;
            margin-bottom: 18px;
        }

        h1 {
            font-size: 28px;
            margin: 0 0 7px;
        }

        h2 {
            font-size: 19px;
            margin: 0 0 15px;
        }

        h3 {
            font-size: 17px;
            margin: 0 0 8px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .add-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            margin-top: 20px;
        }

        select {
            width: 100%;
            background: var(--background);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 11px;
            font: inherit;
        }

        select:focus {
            outline: 1px solid var(--purple);
        }

        .watchlist-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .movie-card {
            margin: 0;
        }

        .movie-meta {
            margin: 0 0 15px;
            color: var(--muted);
            font-size: 13px;
        }

        .status-label {
            display: inline-block;
            padding: 5px 10px;
            margin-bottom: 12px;
            border-radius: 20px;
            background: #302747;
            color: var(--purple);
            font-size: 12px;
            font-weight: bold;
        }

        .movie-actions {
            display: grid;
            gap: 10px;
        }

        .movie-actions form {
            margin: 0;
        }

        .movie-actions button {
            width: 100%;
        }

        .empty {
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: 15px;
            text-align: center;
            padding: 35px 20px;
            color: var(--muted);
        }

        .message {
            padding: 13px 17px;
        }

        .message.success {
            border-color: #376b53;
            color: #a8efc5;
        }

        .message.error {
            border-color: #74374a;
            color: #ffb3bf;
        }

        .message.info {
            border-color: #594785;
            color: #d2c2ff;
        }

        .section-heading {
            margin: 28px 0 15px;
        }

        @media (max-width: 650px) {
            .nav {
                align-items: flex-start;
            }

            .nav-links {
                width: 100%;
            }

            .watchlist-grid {
                grid-template-columns: 1fr;
            }

            .add-form {
                grid-template-columns: 1fr;
            }

            .intro,
            .movie-card {
                padding: 17px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="nav">
        <a class="brand" href="index.php?page=home">chirply.</a>

        <nav class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=profile">My Profile</a>
            <a href="index.php?page=search">Search</a>
            <a href="index.php?page=movies">Movies</a>
            <a class="active" href="index.php?page=watchlist">My Watchlist</a>
            <a href="index.php?page=notifications">Notifications</a>

            <form action="index.php?action=logout" method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= chirplyWatchlistEscape($csrfToken) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </div>
</header>

<main class="container">

    <section class="intro">
        <h1>My Watchlist</h1>

        <p class="muted">
            Keep track of the movies you want to watch, are watching,
            or have already watched.
        </p>

        <form class="add-form"
              action="index.php?action=watchlist_add"
              method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= chirplyWatchlistEscape($csrfToken) ?>">

            <select name="movie_id" required>
                <option value="">Choose a movie to add...</option>

                <?php foreach ($movies as $movie): ?>
                    <?php if (!in_array((int) $movie['id'], $selectedMovieIds, true)): ?>
                        <option value="<?= (int) $movie['id'] ?>">
                            <?= chirplyWatchlistEscape($movie['title']) ?>
                            <?php if (!empty($movie['genre'])): ?>
                                — <?= chirplyWatchlistEscape($movie['genre']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>

            <button class="primary" type="submit">+ Add Movie</button>
        </form>
    </section>

    <?php if (is_array($message) && !empty($message['text'])): ?>
        <?php
        $messageType = in_array(
            $message['type'] ?? '',
            ['success', 'error', 'info'],
            true
        ) ? $message['type'] : 'info';
        ?>

        <div class="message <?= chirplyWatchlistEscape($messageType) ?>">
            <?= chirplyWatchlistEscape($message['text']) ?>
        </div>
    <?php endif; ?>

    <div class="section-heading">
        <h2>Your Saved Movies (<?= count($watchlist) ?>)</h2>
    </div>

    <?php if (empty($watchlist)): ?>

        <section class="empty">
            <h3>Your watchlist is empty.</h3>
            <p>Add a movie above to start building your personal list.</p>
            <a class="button" href="index.php?page=movies">
                Browse Movies
            </a>
        </section>

    <?php else: ?>

        <div class="watchlist-grid">

            <?php foreach ($watchlist as $entry): ?>
                <?php
                $watchlistId = (int) $entry['watchlist_id'];
                $movieId = (int) $entry['movie_id'];
                $status = $entry['status'] ?? 'want_to_watch';

                if (!isset($statusLabels[$status])) {
                    $status = 'want_to_watch';
                }
                ?>

                <article class="movie-card">
                    <span class="status-label">
                        <?= chirplyWatchlistEscape($statusLabels[$status]) ?>
                    </span>

                    <h3>
                        <?= chirplyWatchlistEscape($entry['title'] ?? 'Untitled movie') ?>
                    </h3>

                    <p class="movie-meta">
                        <?php if (!empty($entry['genre'])): ?>
                            Genre: <?= chirplyWatchlistEscape($entry['genre']) ?>
                            <br>
                        <?php endif; ?>

                        <?php if (!empty($entry['director'])): ?>
                            Director: <?= chirplyWatchlistEscape($entry['director']) ?>
                            <br>
                        <?php endif; ?>

                        Added:
                        <?= chirplyWatchlistEscape($entry['added_at'] ?? '') ?>
                    </p>

                    <div class="movie-actions">

                        <a class="button"
                           href="index.php?page=movie&id=<?= $movieId ?>">
                            View Movie Details
                        </a>

                        <form action="index.php?action=watchlist_update"
                              method="POST">

                            <input type="hidden" name="csrf_token"
                                   value="<?= chirplyWatchlistEscape($csrfToken) ?>">

                            <input type="hidden" name="watchlist_id"
                                   value="<?= $watchlistId ?>">

                            <select name="status" aria-label="Update watch status">
                                <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                                    <option
                                        value="<?= chirplyWatchlistEscape($statusValue) ?>"
                                        <?= $status === $statusValue ? 'selected' : '' ?>>
                                        <?= chirplyWatchlistEscape($statusLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button class="primary" type="submit">
                                Update Status
                            </button>
                        </form>

                        <form action="index.php?action=watchlist_remove"
                              method="POST"
                              class="js-remove-watchlist">

                            <input type="hidden" name="csrf_token"
                                   value="<?= chirplyWatchlistEscape($csrfToken) ?>">

                            <input type="hidden" name="watchlist_id"
                                   value="<?= $watchlistId ?>">

                            <button class="danger" type="submit">
                                Remove from Watchlist
                            </button>
                        </form>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<script>
    document.querySelectorAll('.js-remove-watchlist').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = confirm(
                'Remove this movie from your watchlist?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
</script>

</body>
</html>