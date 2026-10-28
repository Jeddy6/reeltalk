
<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/FollowModel.php';

class ProfileController
{
    private UserModel $userModel;
    private PostModel $postModel;
    private FollowModel $followModel;

    public function __construct(PDO $db)
    {
        $this->userModel = new UserModel($db);
        $this->postModel = new PostModel($db);
        $this->followModel = new FollowModel($db);
    }

    // Display a user's profile.
    public function show(): void
    {
        $currentUserId = (int) $_SESSION['user_id'];

        $profileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($profileId === null || $profileId === false) {
            $profileId = $currentUserId;
        }

        if ($profileId < 1) {
            http_response_code(400);
            exit('Invalid profile.');
        }

        $profileUser = $this->userModel->findById((int) $profileId);

        if (!$profileUser) {
            http_response_code(404);
            exit('User profile not found.');
        }

        $posts = $this->postModel->getPostsByUser((int) $profileId);

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

        $isOwnProfile = $currentUserId === (int) $profileUser['id'];

        // Follow information for the displayed profile.
        $followerCount = $this->followModel->getFollowerCount(
            (int) $profileUser['id']
        );

        $followingCount = $this->followModel->getFollowingCount(
            (int) $profileUser['id']
        );

        $isFollowing = false;

        if (!$isOwnProfile) {
            $isFollowing = $this->followModel->isFollowing(
                $currentUserId,
                (int) $profileUser['id']
            );
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require __DIR__ . '/../views/profile.php';
    }

    // Display the edit-profile form.
    public function edit(): void
    {
        $currentUserId = (int) $_SESSION['user_id'];

        $user = $this->userModel->findById($currentUserId);

        if (!$user) {
            http_response_code(404);
            exit('User not found.');
        }

        $error = $_SESSION['profile_error'] ?? '';
        unset($_SESSION['profile_error']);

        require __DIR__ . '/../views/edit_profile.php';
    }

    // Save profile changes.
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
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
            exit('Invalid security token. Please refresh and try again.');
        }

        $userId = (int) $_SESSION['user_id'];

        $user = $this->userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            exit('User not found.');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if ($fullName === '') {
            $_SESSION['profile_error'] = 'Display name is required.';
            header('Location: index.php?page=edit_profile');
            exit;
        }

        if (mb_strlen($fullName) > 100) {
            $_SESSION['profile_error'] =
                'Display name must be 100 characters or fewer.';
            header('Location: index.php?page=edit_profile');
            exit;
        }

        if (mb_strlen($bio) > 500) {
            $_SESSION['profile_error'] =
                'Bio must be 500 characters or fewer.';
            header('Location: index.php?page=edit_profile');
            exit;
        }

        $profileImage = null;
        $upload = $_FILES['profile_image'] ?? null;

        if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($upload['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['profile_error'] =
                    'The image upload failed. Please try again.';
                header('Location: index.php?page=edit_profile');
                exit;
            }

            if ($upload['size'] > 2 * 1024 * 1024) {
                $_SESSION['profile_error'] =
                    'The image must be 2 MB or smaller.';
                header('Location: index.php?page=edit_profile');
                exit;
            }

            $imageInfo = @getimagesize($upload['tmp_name']);

            if ($imageInfo === false) {
                $_SESSION['profile_error'] =
                    'Please select a valid image.';
                header('Location: index.php?page=edit_profile');
                exit;
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($upload['tmp_name']);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($allowedTypes[$mimeType])) {
                $_SESSION['profile_error'] =
                    'Only JPG, PNG, and WebP images are allowed.';
                header('Location: index.php?page=edit_profile');
                exit;
            }

            $uploadDirectory = __DIR__ . '/../../public/uploads/profiles';

            if (
                !is_dir($uploadDirectory) &&
                !mkdir($uploadDirectory, 0755, true)
            ) {
                $_SESSION['profile_error'] =
                    'Could not prepare the image folder.';
                header('Location: index.php?page=edit_profile');
                exit;
            }

            $fileName = bin2hex(random_bytes(16))
                . '.'
                . $allowedTypes[$mimeType];

            $destination = $uploadDirectory
                . DIRECTORY_SEPARATOR
                . $fileName;

            if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                $_SESSION['profile_error'] =
                    'Could not save the uploaded image.';
                header('Location: index.php?page=edit_profile');
                exit;
            }

            $profileImage = 'uploads/profiles/' . $fileName;
        }

        $updated = $this->userModel->updateProfile(
            $userId,
            $fullName,
            $bio,
            $profileImage
        );

        if (!$updated) {
            $_SESSION['profile_error'] =
                'Could not update your profile. Please try again.';
            header('Location: index.php?page=edit_profile');
            exit;
        }

        header('Location: index.php?page=profile');
        exit;
    }
}
