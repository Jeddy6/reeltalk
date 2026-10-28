
<?php

if (!function_exists('movieEscape')) {
    function movieEscape($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

$movies = $movies ?? [];
$csrfToken = $csrfToken ?? '';

$genres = [];

foreach (($allMovies ?? $movies) as $movie) {
    $genre = trim((string) ($movie['genre'] ?? ''));

    if ($genre !== '') {
        $genres[$genre] = $genre;
    }
}

natcasesort($genres);

$selectedGenre = $_GET['genre'] ?? '';
if (!is_string($selectedGenre)) {
    $selectedGenre = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Movies - Chirply</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --background: #100e17;
            --surface: #191624;
            --surface-hover: #211d30;
            --border: #302a43;
            --purple: #a78bfa;
            --purple-dark: #7c5ce0;
            --text: #f4f0ff;
            --muted: #aaa2bf;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 18px 7%;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .logo {
            color: var(--purple);
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        nav a {
            color: var(--muted);
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 8px;
        }

        nav a:hover,
        nav a.active {
            background: #302747;
            color: var(--purple);
        }

        nav form {
            margin: 0;
        }

        main {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }

        h1 {
            color: var(--purple);
            margin-bottom: 8px;
        }

        .muted {
            color: var(--muted);
            line-height: 1.6;
        }

        .page-intro {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .page-intro p {
            margin-bottom: 0;
        }

        .button {
            display: inline-block;
            padding: 11px 16px;
            background: var(--purple-dark);
            color: white;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }

        .button:hover {
            filter: brightness(1.12);
        }

        .secondary-button {
            background: var(--surface-hover);
            color: var(--purple);
            border-color: var(--border);
        }

        .search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(160px, 220px) auto;
            gap: 10px;
            margin: 25px 0;
            padding: 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .search-form input,
        .search-form select {
            width: 100%;
            min-width: 0;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--background);
            color: var(--text);
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 22px;
        }

        .movie-card {
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            transition: transform 0.15s ease;
        }

        .movie-card:hover {
            transform: translateY(-3px);
        }

        .poster,
        .no-poster {
            display: flex;
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: #252033;
        }

        .no-poster {
            justify-content: center;
            align-items: center;
            color: var(--muted);
        }

        .movie-info {
            padding: 16px;
        }

        .movie-info h2 {
            margin-top: 0;
            color: var(--purple);
            font-size: 20px;
            overflow-wrap: anywhere;
        }

        .description {
            overflow-wrap: anywhere;
        }

        .details-link {
            display: inline-block;
            margin-top: 8px;
            color: var(--purple);
            text-decoration: none;
            font-weight: bold;
        }

        .details-link:hover {
            text-decoration: underline;
        }

        .empty {
            padding: 25px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--muted);
            text-align: center;
        }

        .results-count {
            color: var(--muted);
            font-size: 14px;
            margin: 18px 0;
        }

        @media (max-width: 700px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            header {
                padding: 16px;
            }

            .page-intro {
                align-items: stretch;
            }

            .page-intro .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<header>
    <a class="logo" href="index.php?page=home">chirply.</a>

    <nav>
        <a href="index.php?page=home">Home</a>
        <a class="active" href="index.php?page=movies">Movies</a>
        <a href="index.php?page=watchlist">Watchlist</a>
        <a href="index.php?page=profile">My Profile</a>
        <a href="index.php?page=search">Search</a>

        <form method="POST" action="index.php?action=logout">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= movieEscape($csrfToken) ?>"
            >
            <button class="button" type="submit">Log Out</button>
        </form>
    </nav>
</header>

<main>

    <section class="page-intro">
        <div>
            <h1>Explore Movies</h1>

            <p class="muted">
                Find your next favorite movie, keep track of what you watch,
                and share your movie interests on Chirply.
            </p>
        </div>

        <a class="button" href="index.php?page=watchlist">
            Go to My Watchlist →
        </a>
    </section>

    <form class="search-form" method="GET" action="index.php">
        <input type="hidden" name="page" value="movies">

        <input
            type="text"
            name="search"
            placeholder="Search by title, genre, or director..."
            value="<?= movieEscape($_GET['search'] ?? '') ?>"
        >

        <select name="genre">
            <option value="">All genres</option>

            <?php foreach ($genres as $genre): ?>
                <option
                    value="<?= movieEscape($genre) ?>"
                    <?= $selectedGenre === $genre ? 'selected' : '' ?>
                >
                    <?= movieEscape($genre) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="button" type="submit">Search & Filter</button>
    </form>

    <?php if (empty($movies)): ?>

        <div class="empty">
            <h2>No movies found.</h2>
            <p>Try another search, or check back when the catalogue has more movies.</p>
            <a class="button secondary-button" href="index.php?page=movies">
                Clear Search
            </a>
        </div>

    <?php else: ?>

        <p class="results-count">
            Showing <?= count($movies) ?> movie(s) from the catalogue.
        </p>

        <div class="movie-grid">

            <?php foreach ($movies as $movie): ?>

                <?php
                $movieGenre = trim((string) ($movie['genre'] ?? ''));

                if (
                    $selectedGenre !== ''
                    && strcasecmp($selectedGenre, $movieGenre) !== 0
                ) {
                    continue;
                }
                ?>

                <article class="movie-card">

                    <?php if (!empty($movie['poster_url'])): ?>

                        <img
                            class="poster"
                            src="<?= movieEscape($movie['poster_url']) ?>"
                            alt="<?= movieEscape($movie['title']) ?> poster"
                            loading="lazy"
                        >

                    <?php else: ?>

                        <div class="no-poster">
                            No poster available
                        </div>

                    <?php endif; ?>

                    <div class="movie-info">

                        <h2><?= movieEscape($movie['title']) ?></h2>

                        <p class="muted">
                            Genre:
                            <?= movieEscape($movie['genre'] ?: 'Unknown') ?>
                        </p>

                        <p class="muted">
                            Year:
                            <?= movieEscape($movie['release_year'] ?: 'Unknown') ?>
                        </p>

                        <p class="muted">
                            Director:
                            <?= movieEscape($movie['director'] ?: 'Unknown') ?>
                        </p>

                        <p class="muted description">
                            <?= movieEscape(
                                $movie['description'] ?: 'No description available.'
                            ) ?>
                        </p>

                        <a
                            class="details-link"
                            href="index.php?page=movie&id=<?= (int) $movie['id'] ?>"
                        >
                            View Details →
                        </a>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

</body>
</html>