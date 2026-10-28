
<?php

class WatchlistModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | Get a user's watchlist
    |--------------------------------------------------------------------------
    */

    public function getWatchlist(int $userId): array
    {
        $sql = "SELECT
                    w.id AS watchlist_id,
                    w.user_id,
                    w.movie_id,
                    w.status,
                    w.added_at,
                    m.title,
                    m.genre,
                    m.director
                FROM watchlists w
                INNER JOIN movies m ON w.movie_id = m.id
                WHERE w.user_id = :user_id
                ORDER BY w.added_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Add a movie to a user's watchlist
    |--------------------------------------------------------------------------
    */

    public function addMovie(int $userId, int $movieId): bool
    {
        // Prevent the same movie from being added twice by one user.
        $checkSql = "SELECT id
                     FROM watchlists
                     WHERE user_id = :user_id
                       AND movie_id = :movie_id
                     LIMIT 1";

        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([
            'user_id' => $userId,
            'movie_id' => $movieId
        ]);

        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        $sql = "INSERT INTO watchlists
                    (user_id, movie_id, status)
                VALUES
                    (:user_id, :movie_id, 'want_to_watch')";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'movie_id' => $movieId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update a watchlist movie's status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        int $watchlistId,
        int $userId,
        string $status
    ): bool {
        $allowedStatuses = [
            'want_to_watch',
            'watching',
            'watched'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "UPDATE watchlists
                SET status = :status
                WHERE id = :id
                  AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'status' => $status,
            'id' => $watchlistId,
            'user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Remove a movie from a user's watchlist
    |--------------------------------------------------------------------------
    */

    public function removeMovie(
        int $watchlistId,
        int $userId
    ): bool {
        $sql = "DELETE FROM watchlists
                WHERE id = :id
                  AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $watchlistId,
            'user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }
}