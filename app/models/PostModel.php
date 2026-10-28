
<?php

class PostModel
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // Get all posts, newest first
    public function getAllPosts(): array
    {
        $sql = "SELECT posts.*, users.username, users.full_name, users.profile_image
                FROM posts
                INNER JOIN users ON posts.user_id = users.id
                ORDER BY posts.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search posts by their text content
    public function searchPosts(string $keyword): array
    {
        $sql = "SELECT posts.*, users.username, users.full_name,
                       users.profile_image
                FROM posts
                INNER JOIN users ON posts.user_id = users.id
                WHERE posts.content LIKE :keyword
                ORDER BY posts.created_at DESC
                LIMIT 100";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':keyword' => '%' . $keyword . '%'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a post with an optional image or video
    public function createPost(
        int $userId,
        string $content,
        ?string $image = null,
        ?string $video = null
    ): bool {
        $sql = "INSERT INTO posts (user_id, content, image, video)
                VALUES (:user_id, :content, :image, :video)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':image' => $image,
            ':video' => $video
        ]);
    }

    // Find one post
    public function getPostById(int $postId): ?array
    {
        $sql = "SELECT * FROM posts WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $postId]);

        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        return $post ?: null;
    }

    // Update post text while preserving existing media unless replaced
    public function updatePost(
        int $postId,
        int $userId,
        string $content,
        ?string $image = null,
        ?string $video = null
    ): bool {
        $sql = "UPDATE posts
                SET content = :content,
                    image = COALESCE(:image, image),
                    video = COALESCE(:video, video)
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':content' => $content,
            ':image' => $image,
            ':video' => $video,
            ':id' => $postId,
            ':user_id' => $userId
        ]);
    }

    // Delete a post owned by the user
    public function deletePost(int $postId, int $userId): bool
    {
        $sql = "DELETE FROM posts
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $postId,
            ':user_id' => $userId
        ]);
    }

    // Get posts belonging to one user
    public function getPostsByUser(int $userId): array
    {
        $sql = "SELECT posts.*, users.username, users.full_name,
                       users.profile_image
                FROM posts
                INNER JOIN users ON posts.user_id = users.id
                WHERE posts.user_id = :user_id
                ORDER BY posts.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get comments for a post
    public function getCommentsByPost(int $postId): array
    {
        $sql = "SELECT post_comments.*, users.username, users.full_name
                FROM post_comments
                INNER JOIN users ON post_comments.user_id = users.id
                WHERE post_comments.post_id = :post_id
                ORDER BY post_comments.created_at ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':post_id' => $postId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a comment
    public function createComment(
        int $postId,
        int $userId,
        string $content
    ): bool {
        $sql = "INSERT INTO post_comments (post_id, user_id, content)
                VALUES (:post_id, :user_id, :content)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':content' => $content
        ]);
    }

    // Delete a comment owned by the user
    public function deleteComment(int $commentId, int $userId): bool
    {
        $sql = "DELETE FROM post_comments
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id' => $commentId,
            ':user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    // Count likes on a post
    public function getLikeCount(int $postId): int
    {
        $sql = "SELECT COUNT(*) FROM post_likes WHERE post_id = :post_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':post_id' => $postId]);

        return (int) $stmt->fetchColumn();
    }

    // Check whether a user has liked a post
    public function hasUserLikedPost(int $postId, int $userId): bool
    {
        $sql = "SELECT id
                FROM post_likes
                WHERE post_id = :post_id AND user_id = :user_id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // Like a post
    public function likePost(int $postId, int $userId): bool
    {
        if ($this->hasUserLikedPost($postId, $userId)) {
            return true;
        }

        $sql = "INSERT INTO post_likes (post_id, user_id)
                VALUES (:post_id, :user_id)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
    }

    // Unlike a post
    public function unlikePost(int $postId, int $userId): bool
    {
        $sql = "DELETE FROM post_likes
                WHERE post_id = :post_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }
}
