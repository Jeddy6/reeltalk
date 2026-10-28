
<?php

class Review
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | Get all reviews for a movie
    |--------------------------------------------------------------------------
    */

    public function getReviewsByMovie(int $movieId): array
    {
        $sql = "SELECT
                    reviews.id,
                    reviews.user_id,
                    reviews.movie_id,
                    reviews.rating,
                    reviews.review_text,
                    reviews.has_spoilers,
                    reviews.created_at,
                    reviews.updated_at,
                    users.username,
                    users.full_name
                FROM reviews
                INNER JOIN users
                    ON reviews.user_id = users.id
                WHERE reviews.movie_id = :movie_id
                ORDER BY reviews.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            'movie_id' => $movieId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get the current user's review for a movie
    |--------------------------------------------------------------------------
    */

    public function getUserReview(
        int $userId,
        int $movieId
    ): ?array {
        $sql = "SELECT *
                FROM reviews
                WHERE user_id = :user_id
                AND movie_id = :movie_id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'movie_id' => $movieId
        ]);

        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        return $review ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Add or update a review
    |--------------------------------------------------------------------------
    */

    public function saveReview(
        int $userId,
        int $movieId,
        int $rating,
        string $reviewText,
        bool $hasSpoilers
    ): bool {
        $sql = "INSERT INTO reviews (
                    user_id,
                    movie_id,
                    rating,
                    review_text,
                    has_spoilers
                )
                VALUES (
                    :user_id,
                    :movie_id,
                    :rating,
                    :review_text,
                    :has_spoilers
                )
                ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    review_text = VALUES(review_text),
                    has_spoilers = VALUES(has_spoilers)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'user_id' => $userId,
            'movie_id' => $movieId,
            'rating' => $rating,
            'review_text' => $reviewText,
            'has_spoilers' => $hasSpoilers ? 1 : 0
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get average rating and total number of reviews
    |--------------------------------------------------------------------------
    */

    public function getMovieRating(int $movieId): array
    {
        $sql = "SELECT
                    COUNT(*) AS total_reviews,
                    ROUND(AVG(rating), 1) AS average_rating
                FROM reviews
                WHERE movie_id = :movie_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            'movie_id' => $movieId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_reviews' => (int) ($result['total_reviews'] ?? 0),
            'average_rating' => (float) ($result['average_rating'] ?? 0)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Delete a user's own review
    |--------------------------------------------------------------------------
    */

    public function deleteReview(
        int $reviewId,
        int $userId
    ): bool {
        $sql = "DELETE FROM reviews
                WHERE id = :review_id
                AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'review_id' => $reviewId,
            'user_id' => $userId
        ]);
    }
}
