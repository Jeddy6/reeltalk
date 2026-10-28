
<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';

class SearchController
{
    private UserModel $userModel;
    private PostModel $postModel;

    public function __construct(PDO $db)
    {
        $this->userModel = new UserModel($db);
        $this->postModel = new PostModel($db);
    }

    // Display matching users and posts
    public function index(): void
    {
        $keyword = trim($_GET['q'] ?? '');

        // Prevent unexpectedly long search queries
        if (strlen($keyword) > 100) {
            $keyword = substr($keyword, 0, 100);
        }

        $users = [];
        $posts = [];

        // Search only when the user enters a keyword
        if ($keyword !== '') {
            $users = $this->userModel->searchUsers($keyword);
            $posts = $this->postModel->searchPosts($keyword);
        }

        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $csrfToken = $_SESSION['csrf_token'] ?? '';

        // Add likes and comments to each matching post
        foreach ($posts as &$post) {
            $postId = (int) $post['id'];

            $post['comments'] =
                $this->postModel->getCommentsByPost($postId);

            $post['like_count'] =
                $this->postModel->getLikeCount($postId);

            $post['user_liked'] =
                $this->postModel->hasUserLikedPost(
                    $postId,
                    $currentUserId
                );
        }

        unset($post);

        require __DIR__ . '/../views/search.php';
    }
}
