
<?php

class FollowModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Check whether one user follows another user.
    public function isFollowing(int $followerId, int $followingId): bool
    {
        $sql = "
            SELECT id
            FROM user_follows
            WHERE follower_id = :follower_id
              AND following_id = :following_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // Follow another user.
    public function follow(int $followerId, int $followingId): bool
    {
        if ($followerId < 1 || $followingId < 1) {
            return false;
        }

        if ($followerId === $followingId) {
            return false;
        }

        if ($this->isFollowing($followerId, $followingId)) {
            return false;
        }

        $sql = "
            INSERT INTO user_follows (follower_id, following_id)
            VALUES (:follower_id, :following_id)
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
    }

    // Unfollow a user.
    public function unfollow(int $followerId, int $followingId): bool
    {
        if ($followerId < 1 || $followingId < 1) {
            return false;
        }

        if ($followerId === $followingId) {
            return false;
        }

        $sql = "
            DELETE FROM user_follows
            WHERE follower_id = :follower_id
              AND following_id = :following_id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
    }

    // Count the people following a user.
    public function getFollowerCount(int $userId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM user_follows
            WHERE following_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    // Count the people a user follows.
    public function getFollowingCount(int $userId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM user_follows
            WHERE follower_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }
}
