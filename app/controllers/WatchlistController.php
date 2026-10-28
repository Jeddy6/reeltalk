
<?php

require_once __DIR__ . '/../models/WatchlistModel.php';
require_once __DIR__ . '/../models/Movie.php';

class WatchlistController
{
    private PDO $conn;
    private WatchlistModel $watchlistModel;
    private Movie $movieModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->watchlistModel = new WatchlistModel($db);
        $this->movieModel = new Movie($db);
    }

    /*
    |--------------------------------------------------------------------------
    | Display the user's watchlist
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];

        $watchlist = $this->watchlistModel->getWatchlist($userId);
        $movies = $this->movieModel->getAllMovies();
        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require __DIR__ . '/../views/watchlist.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Add a movie to the watchlist
    |--------------------------------------------------------------------------
    */

    public function add(): void
    {
        $userId = $this->requireLogin();
        $this->validateCsrf();

        $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);

        if (!$movieId || $movieId < 1) {
            $this->setMessage('error', 'Please select a valid movie.');
            $this->redirectToWatchlist();
        }

        $movie = $this->movieModel->getMovieById($movieId);

        if (!$movie) {
            $this->setMessage('error', 'Movie not found.');
            $this->redirectToWatchlist();
        }

        try {
            $added = $this->watchlistModel->addMovie($userId, $movieId);

            if ($added) {
                $this->setMessage(
                    'success',
                    'Movie added to your watchlist!'
                );
            } else {
                $this->setMessage(
                    'info',
                    'This movie is already in your watchlist.'
                );
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->setMessage(
                'error',
                'Unable to add this movie. Please try again.'
            );
        }

        $this->redirectToWatchlist();
    }

    /*
    |--------------------------------------------------------------------------
    | Change a watchlist movie's status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(): void
    {
        $userId = $this->requireLogin();
        $this->validateCsrf();

        $watchlistId = filter_input(
            INPUT_POST,
            'watchlist_id',
            FILTER_VALIDATE_INT
        );

        $status = $_POST['status'] ?? '';

        $allowedStatuses = [
            'want_to_watch',
            'watching',
            'watched'
        ];

        if (
            !$watchlistId ||
            $watchlistId < 1 ||
            !is_string($status) ||
            !in_array($status, $allowedStatuses, true)
        ) {
            $this->setMessage('error', 'Invalid watchlist update.');
            $this->redirectToWatchlist();
        }

        try {
            $updated = $this->watchlistModel->updateStatus(
                $watchlistId,
                $userId,
                $status
            );

            $this->setMessage(
                $updated ? 'success' : 'info',
                $updated
                    ? 'Watch status updated!'
                    : 'No changes were made, or the movie was not found in your watchlist.'
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->setMessage(
                'error',
                'Unable to update the watch status.'
            );
        }

        $this->redirectToWatchlist();
    }

    /*
    |--------------------------------------------------------------------------
    | Remove a movie from the watchlist
    |--------------------------------------------------------------------------
    */

    public function remove(): void
    {
        $userId = $this->requireLogin();
        $this->validateCsrf();

        $watchlistId = filter_input(
            INPUT_POST,
            'watchlist_id',
            FILTER_VALIDATE_INT
        );

        if (!$watchlistId || $watchlistId < 1) {
            $this->setMessage('error', 'Invalid watchlist entry.');
            $this->redirectToWatchlist();
        }

        try {
            $removed = $this->watchlistModel->removeMovie(
                $watchlistId,
                $userId
            );

            $this->setMessage(
                $removed ? 'success' : 'error',
                $removed
                    ? 'Movie removed from your watchlist.'
                    : 'Movie was not found in your watchlist.'
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->setMessage(
                'error',
                'Unable to remove the movie.'
            );
        }

        $this->redirectToWatchlist();
    }

    /*
    |--------------------------------------------------------------------------
    | Require login
    |--------------------------------------------------------------------------
    */

    private function requireLogin(): int
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        return (int) $_SESSION['user_id'];
    }

    /*
    |--------------------------------------------------------------------------
    | Validate the CSRF token
    |--------------------------------------------------------------------------
    */

    private function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !isset($_SESSION['csrf_token']) ||
            !is_string($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Invalid security token. Please refresh the page and try again.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save a flash message
    |--------------------------------------------------------------------------
    */

    private function setMessage(string $type, string $text): void
    {
        $_SESSION['watchlist_message'] = [
            'type' => $type,
            'text' => $text
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect back to the watchlist
    |--------------------------------------------------------------------------
    */

    private function redirectToWatchlist(): void
    {
        header('Location: index.php?page=watchlist');
        exit;
    }
}