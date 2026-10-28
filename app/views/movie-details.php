
<?php

function movieDetailsEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$reviewMessage = $_SESSION['review_message'] ?? null;
unset($_SESSION['review_message']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$hasUserReview = isset($userReview) && is_array($userReview);

$movie = $movie ?? [];
$ratingSummary = $ratingSummary ?? [];
$reviews = $reviews ?? [];
$userReview = $userReview ?? null;
$csrfToken = $csrfToken ?? '';

$average = (float) ($ratingSummary['average_rating'] ?? 0);
$average = max(0, min(5, $average));
$totalReviews = (int) ($ratingSummary['total_reviews'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= movieDetailsEscape($movie['title'] ?? 'Movie') ?> - Chirply</title>

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
            --gold: #f4c96b;
            --danger: #e87991;
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

        nav a:hover {
            background: #302747;
            color: var(--purple);
        }

        nav form {
            margin: 0;
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
            font-size: 14px;
        }

        .button:hover {
            filter: brightness(1.12);
        }

        .secondary-button {
            background: var(--surface-hover);
            color: var(--purple);
            border-color: var(--border);
        }

        main {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 22px;
            color: var(--purple);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .movie-card,
        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .movie-layout {
            display: grid;
            grid-template-columns: minmax(180px, 300px) minmax(0, 1fr);
            gap: 30px;
        }

        .poster {
            display: block;
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 10px;
            background: #252033;
        }

        .no-poster {
            width: 100%;
            min-height: 300px;
            display: grid;
            place-items: center;
            background: #252033;
            border: 1px dashed var(--border);
            border-radius: 10px;
            color: var(--muted);
            text-align: center;
            padding: 20px;
        }

        h1,
        h2,
        h3 {
            color: var(--purple);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 34px;
            overflow-wrap: anywhere;
        }

        h2 {
            margin-top: 0;
        }

        .muted {
            color: var(--muted);
            line-height: 1.7;
        }

        .rating-summary {
            padding: 18px;
            background: var(--surface-hover);
            border: 1px solid var(--border);
            border-radius: 10px;
            margin: 22px 0;
        }

        .rating-number {
            color: var(--text);
            font-size: 24px;
            font-weight: bold;
            margin-top: 8px;
        }

        .stars {
            color: var(--gold);
            font-size: 22px;
            letter-spacing: 3px;
        }

        .rating-caption {
            color: var(--muted);
            font-size: 14px;
            margin-top: 7px;
        }

        label {
            display: block;
            margin: 14px 0 8px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font: inherit;
        }

        input:focus,
        textarea:focus {
            outline: 2px solid var(--purple-dark);
            border-color: var(--purple);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .rating-options {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 12px 0 22px;
        }

        .rating-options label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 10px 13px;
            border: 1px solid var(--border);
            background: var(--surface-hover);
            border-radius: 8px;
            font-weight: normal;
            cursor: pointer;
        }

        .rating-options label:has(input:checked) {
            border-color: var(--purple);
            color: var(--purple);
        }

        .rating-options input {
            accent-color: var(--purple-dark);
        }

        .spoiler-option {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 18px 0;
            font-weight: normal;
            color: var(--muted);
        }

        .spoiler-option input {
            accent-color: var(--purple-dark);
        }

        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .success {
            background: #203c31;
            border: 1px solid #3d765d;
            color: #baf3d5;
        }

        .error {
            background: #482b30;
            border: 1px solid #8d454d;
            color: #ffc4cc;
        }

        .review {
            padding: 22px 0;
            border-bottom: 1px solid var(--border);
        }

        .review:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .review h3 {
            margin-bottom: 8px;
        }

        .review-meta {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .review-text {
            line-height: 1.8;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .spoiler-warning {
            color: var(--gold);
            font-weight: bold;
        }

        .delete-form {
            margin-top: 15px;
        }

        .delete-button {
            padding: 9px 13px;
            background: #482530;
            color: #ffc4cc;
            border: 1px solid #713b4a;
            border-radius: 7px;
            cursor: pointer;
        }

        .delete-button:hover {
            background: #61303f;
        }

        .empty-reviews {
            padding: 18px;
            border-radius: 9px;
            background: var(--surface-hover);
            color: var(--muted);
            line-height: 1.7;
        }

        @media (max-width: 700px) {
            header {
                padding: 16px;
            }

            main {
                margin-top: 22px;
                padding: 0 14px;
            }

            .movie-layout {
                grid-template-columns: 1fr;
            }

            .poster,
            .no-poster {
                max-width: 280px;
            }

            h1 {
                font-size: 28px;
            }

            .movie-card,
            .section-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <a class="logo" href="index.php?page=home">chirply.</a>

    <nav>
        <a href="index.php?page=home">Home</a>
        <a href="index.php?page=movies">Movies</a>
        <a href="index.php?page=watchlist">Watchlist</a>
        <a href="index.php?page=profile">My Profile</a>
        <a href="index.php?page=search">Search</a>

        <form method="POST" action="index.php?action=logout">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= movieDetailsEscape($csrfToken) ?>"
            >
            <button class="button" type="submit">Log Out</button>
        </form>
    </nav>
</header>

<main>

    <a class="back-link" href="index.php?page=movies">
        ← Back to Explore Movies
    </a>

    <?php if (is_array($reviewMessage)): ?>
        <?php
        $messageType = ($reviewMessage['type'] ?? '') === 'error'
            ? 'error'
            : 'success';
        ?>
        <div class="message <?= $messageType ?>">
            <?= movieDetailsEscape($reviewMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="movie-card">
        <div class="movie-layout">

            <div>
                <?php if (!empty($movie['poster_url'])): ?>
                    <img
                        class="poster"
                        src="<?= movieDetailsEscape($movie['poster_url']) ?>"
                        alt="<?= movieDetailsEscape($movie['title'] ?? 'Movie') ?> poster"
                    >
                <?php else: ?>
                    <div class="no-poster">
                        No poster available
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h1><?= movieDetailsEscape($movie['title'] ?? 'Untitled Movie') ?></h1>

                <p class="muted">
                    <strong>Genre:</strong>
                    <?= movieDetailsEscape($movie['genre'] ?? 'Unknown') ?>
                </p>

                <p class="muted">
                    <strong>Release year:</strong>
                    <?= movieDetailsEscape($movie['release_year'] ?? 'Unknown') ?>
                </p>

                <p class="muted">
                    <strong>Director:</strong>
                    <?= movieDetailsEscape(
                        !empty($movie['director']) ? $movie['director'] : 'Unknown'
                    ) ?>
                </p>

                <div class="rating-summary">
                    <div class="stars">
                        <?php
                        $roundedRating = (int) round($average);
                        echo str_repeat('★', $roundedRating);
                        echo str_repeat('☆', 5 - $roundedRating);
                        ?>
                    </div>

                    <div class="rating-number">
                        <?= number_format($average, 1) ?> / 5
                    </div>

                    <div class="rating-caption">
                        Based on <?= $totalReviews ?>
                        <?= $totalReviews === 1 ? 'review' : 'reviews' ?>
                    </div>
                </div>

                <h3>About this movie</h3>

                <p class="muted">
                    <?= nl2br(movieDetailsEscape(
                        !empty($movie['description'])
                            ? $movie['description']
                            : 'No description available.'
                    )) ?>
                </p>
            </div>

        </div>
    </section>

    <section class="section-card" id="review-form">
        <h2><?= $hasUserReview ? 'Update Your Review' : 'Write a Review' ?></h2>

        <p class="muted">
            Share your thoughts with the Chirply community.
            Choose a rating from 1 to 5 stars and tell everyone what you think.
        </p>

        <form method="POST" action="index.php?action=save_review">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= movieDetailsEscape($csrfToken) ?>"
            >

            <input
                type="hidden"
                name="movie_id"
                value="<?= (int) ($movie['id'] ?? 0) ?>"
            >

            <label>Your rating</label>

            <div class="rating-options">
                <?php for ($star = 1; $star <= 5; $star++): ?>
                    <label>
                        <input
                            type="radio"
                            name="rating"
                            value="<?= $star ?>"
                            required
                            <?= (int) ($userReview['rating'] ?? 0) === $star
                                ? 'checked'
                                : '' ?>
                        >
                        <?= $star ?> ★
                    </label>
                <?php endfor; ?>
            </div>

            <label for="review_text">Your review</label>

            <textarea
                id="review_text"
                name="review_text"
                maxlength="10000"
                required
                placeholder="What did you like or dislike about this movie?"
            ><?= movieDetailsEscape($userReview['review_text'] ?? '') ?></textarea>

            <label class="spoiler-option">
                <input
                    type="checkbox"
                    name="has_spoilers"
                    value="1"
                    <?= !empty($userReview['has_spoilers']) ? 'checked' : '' ?>
                >
                This review contains spoilers
            </label>

            <button class="button" type="submit">
                <?= $hasUserReview ? 'Update Review' : 'Submit Review' ?>
            </button>
        </form>
    </section>

    <section class="section-card" id="reviews">
        <h2>Community Reviews</h2>

        <?php if (empty($reviews)): ?>
            <div class="empty-reviews">
                No reviews yet. Be the first to share your thoughts!
            </div>
        <?php else: ?>

            <?php foreach ($reviews as $review): ?>
                <article class="review">

                    <h3>
                        <?= movieDetailsEscape($review['full_name'] ?? 'Chirply User') ?>
                    </h3>

                    <p class="review-meta">
                        @<?= movieDetailsEscape($review['username'] ?? 'user') ?>
                        ·
                        <?= movieDetailsEscape($review['created_at'] ?? '') ?>
                    </p>

                    <?php
                    $reviewRating = max(
                        0,
                        min(5, (int) ($review['rating'] ?? 0))
                    );
                    ?>

                    <div class="stars">
                        <?= str_repeat('★', $reviewRating) ?><?= str_repeat('☆', 5 - $reviewRating) ?>
                    </div>

                    <?php if (!empty($review['has_spoilers'])): ?>
                        <p class="spoiler-warning">
                            ⚠ This review contains spoilers.
                        </p>
                    <?php endif; ?>

                    <p class="review-text"><?= movieDetailsEscape($review['review_text'] ?? '') ?></p>

                    <?php if ((int) ($review['user_id'] ?? 0) === $userId): ?>
                        <a class="back-link" href="#review-form">
                            Edit your review
                        </a>

                        <form
                            class="delete-form"
                            method="POST"
                            action="index.php?action=delete_review"
                            onsubmit="return confirm('Are you sure you want to delete your review?');"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= movieDetailsEscape($csrfToken) ?>"
                            >

                            <input
                                type="hidden"
                                name="review_id"
                                value="<?= (int) ($review['id'] ?? 0) ?>"
                            >

                            <input
                                type="hidden"
                                name="movie_id"
                                value="<?= (int) ($movie['id'] ?? 0) ?>"
                            >

                            <button class="delete-button" type="submit">
                                Delete Review
                            </button>
                        </form>
                    <?php endif; ?>

                </article>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

</main>

</body>
</html>
