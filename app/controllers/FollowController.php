
<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/FollowModel.php';

class FollowController
{
    private UserModel $userModel;
    private FollowModel $followModel;

    public function __construct(PDO $db)
    {
        $this->userModel = new UserModel($db);
        $this->followModel = new FollowModel($db);
    }

    public function follow(): void
    {
        $this->handleFollowAction(true);
    }

    public function unfollow(): void
    {
        $this->handleFollowAction(false);
    }

    private function handleFollowAction(bool $shouldFollow): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            $this->respond(false, 'Method not allowed.');
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            $this->respond(false, 'Please log in again.');
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (
            !is_string($submittedToken) ||
            !is_string($sessionToken) ||
            $sessionToken === '' ||
            !hash_equals($sessionToken, $submittedToken)
        ) {
            http_response_code(403);
            $this->respond(
                false,
                'Your security token is invalid. Please refresh and try again.'
            );
        }

        $targetId = filter_input(
            INPUT_POST,
            'user_id',
            FILTER_VALIDATE_INT
        );

        if ($targetId === false || $targetId === null || $targetId < 1) {
            http_response_code(400);
            $this->respond(false, 'Invalid user profile.');
        }

        $currentUserId = (int) $_SESSION['user_id'];
        $targetId = (int) $targetId;

        if ($currentUserId === $targetId) {
            http_response_code(400);
            $this->respond(false, 'You cannot follow yourself.');
        }

        $targetUser = $this->userModel->findById($targetId);

        if (!$targetUser) {
            http_response_code(404);
            $this->respond(false, 'User profile not found.');
        }

        try {
            if ($shouldFollow) {
                $this->followModel->follow(
                    $currentUserId,
                    $targetId
                );
            } else {
                $this->followModel->unfollow(
                    $currentUserId,
                    $targetId
                );
            }

            $isFollowing = $this->followModel->isFollowing(
                $currentUserId,
                $targetId
            );

            $followerCount = $this->followModel->getFollowerCount(
                $targetId
            );

            $this->respond(true, 'Follow status updated.', [
                'isFollowing' => $isFollowing,
                'followerCount' => $followerCount
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);

            $this->respond(
                false,
                'Could not update follow status. Please try again.'
            );
        }
    }

    private function respond(
        bool $success,
        string $message,
        array $data = []
    ): void {
        $wantsJson =
            isset($_SERVER['HTTP_ACCEPT']) &&
            str_contains(
                $_SERVER['HTTP_ACCEPT'],
                'application/json'
            );

        if ($wantsJson) {
            header('Content-Type: application/json; charset=UTF-8');

            echo json_encode(array_merge([
                'success' => $success,
                'message' => $message
            ], $data));

            exit;
        }

        if (!$success) {
            $_SESSION['follow_error'] = $message;
        }

        $targetId = filter_input(
            INPUT_POST,
            'user_id',
            FILTER_VALIDATE_INT
        );

        if ($targetId && $targetId > 0) {
            header(
                'Location: index.php?page=profile&id=' . (int) $targetId
            );
        } else {
            header('Location: index.php?page=profile');
        }

        exit;
    }
}
