
<?php

require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Review.php';

class MovieController
{
    private PDO $conn;
    private Movie $movieModel;
    private Review $reviewModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->movieModel = new Movie($db);
        $this->reviewModel = new Review($db);
    }

    /*
    |--------------------------------------------------------------------------
    | Display the movie catalog with search and genre filtering
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $genre = $_GET['genre'] ?? '';

        if (!is_string($search)) {
            $search = '';
        }

        if (!is_string($genre)) {
            $genre = '';
        }

        $search = trim($search);
        $genre = trim($genre);

        $movies = $this->movieModel->getMoviesFiltered(
            $search,
            $genre
        );

        // Get all movies to populate the complete genre dropdown.
        $allMovies = $this->movieModel->getAllMovies();

        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require __DIR__ . '/../views/movies.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Display movie details and reviews
    |--------------------------------------------------------------------------
    */

    public function show(): void
    {
        $movieId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$movieId || $movieId < 1) {
            http_response_code(400);
            exit('Invalid movie ID.');
        }

        $movie = $this->movieModel->getMovieById($movieId);

        if (!$movie) {
            http_response_code(404);
            exit('Movie not found.');
        }

        $reviews = $this->reviewModel->getReviewsByMovie($movieId);
        $ratingSummary = $this->reviewModel->getMovieRating($movieId);

        $userReview = null;

        if (isset($_SESSION['user_id'])) {
            $userReview = $this->reviewModel->getUserReview(
                (int) $_SESSION['user_id'],
                $movieId
            );
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require __DIR__ . '/../views/movie-details.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Save a movie review
    |--------------------------------------------------------------------------
    */

    public function saveReview(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';

        if (
            !is_string($csrfToken) ||
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $csrfToken)
        ) {
            http_response_code(403);
            exit('Invalid security token. Please refresh the page and try again.');
        }

        $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $reviewText = $_POST['review_text'] ?? '';
        $hasSpoilers = isset($_POST['has_spoilers']);

        if (
            !$movieId ||
            $movieId < 1 ||
            !$rating ||
            $rating < 1 ||
            $rating > 5 ||
            !is_string($reviewText)
        ) {
            $_SESSION['review_message'] = [
                'type' => 'error',
                'text' => 'Please select a rating and write a valid review.'
            ];

            $redirectId = max(1, (int) $movieId);
            header('Location: index.php?page=movie&id=' . $redirectId . '#review-form');
            exit;
        }

        $reviewText = trim($reviewText);

        if ($reviewText === '' || strlen($reviewText) > 10000) {
            $_SESSION['review_message'] = [
                'type' => 'error',
                'text' => 'Please write a review of up to 10,000 characters.'
            ];

            header('Location: index.php?page=movie&id=' . $movieId . '#review-form');
            exit;
        }

        $movie = $this->movieModel->getMovieById($movieId);

        if (!$movie) {
            http_response_code(404);
            exit('Movie not found.');
        }

        $this->reviewModel->saveReview(
            (int) $_SESSION['user_id'],
            $movieId,
            $rating,
            $reviewText,
            $hasSpoilers
        );

        $_SESSION['review_message'] = [
            'type' => 'success',
            'text' => 'Your review has been saved!'
        ];

        header('Location: index.php?page=movie&id=' . $movieId . '#reviews');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete the logged-in user's review
    |--------------------------------------------------------------------------
    */

    public function deleteReview(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';

        if (
            !is_string($csrfToken) ||
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $csrfToken)
        ) {
            http_response_code(403);
            exit('Invalid security token. Please refresh the page and try again.');
        }

        $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
        $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

        if (!$reviewId || !$movieId || $reviewId < 1 || $movieId < 1) {
            http_response_code(400);
            exit('Invalid review request.');
        }

        $this->reviewModel->deleteReview(
            $reviewId,
            (int) $_SESSION['user_id']
        );

        $_SESSION['review_message'] = [
            'type' => 'success',
            'text' => 'Your review has been deleted.'
        ];

        header('Location: index.php?page=movie&id=' . $movieId . '#reviews');
        exit;
    }
}
