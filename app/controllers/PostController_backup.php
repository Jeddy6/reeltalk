
<?php

require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class PostController
{
    private PostModel $postModel;
    private UserModel $userModel;

    public function __construct(PDO $db)
    {
        $this->postModel = new PostModel($db);
        $this->userModel = new UserModel($db);
    }

    // Display the home feed
    public function index(): void
    {
        $userId = (int) $_SESSION['user_id'];

        $user = $this->userModel->findById($userId);
        $posts = $this->postModel->getAllPosts();

        foreach ($posts as &$post) {
            $postId = (int) $post['id'];

            $post['comments'] =
                $this->postModel->getCommentsByPost($postId);

            $post['like_count'] =
                $this->postModel->getLikeCount($postId);

            $post['user_liked'] =
                $this->postModel->hasUserLikedPost($postId, $userId);
        }
        unset($post);

        $csrfToken = $_SESSION['csrf_token'] ?? '';

        $flashMessage = $_SESSION['flash_message'] ?? '';
        $flashPostId = (int) ($_SESSION['flash_post_id'] ?? 0);

        unset($_SESSION['flash_message'], $_SESSION['flash_post_id']);

        require __DIR__ . '/../views/home.php';
    }

    // Create a post with an optional photo or video
    public function create(): void
    {
        $this->requirePostAndCsrf();

        $content = trim($_POST['content'] ?? '');

        if (strlen($content) > 5000) {
            $this->redirectHome(
                'Your post is too long. Maximum: 5000 characters.'
            );
        }

        $upload = $this->handleMediaUpload();

        if ($upload['error'] !== null) {
            $this->redirectHome($upload['error']);
        }

        if (
            $content === ''
            && $upload['image'] === null
            && $upload['video'] === null
        ) {
            $this->redirectHome(
                'Please write something or attach a photo or video.'
            );
        }

        try {
            $created = $this->postModel->createPost(
                (int) $_SESSION['user_id'],
                $content,
                $upload['image'],
                $upload['video']
            );

            if (!$created) {
                $this->removeUploadedFile($upload['image']);
                $this->removeUploadedFile($upload['video']);

                $this->redirectHome(
                    'Unable to publish your post. Please try again.'
                );
            }
        } catch (Throwable $e) {
            $this->removeUploadedFile($upload['image']);
            $this->removeUploadedFile($upload['video']);

            $this->redirectHome(
                'Unable to publish your post. Please try again.'
            );
        }

        $this->redirectHome('Your post was published.');
    }

    // Validate and save an optional uploaded photo or video
    private function handleMediaUpload(): array
    {
        $result = [
            'image' => null,
            'video' => null,
            'error' => null
        ];

        if (
            !isset($_FILES['media_file'])
            || !is_array($_FILES['media_file'])
        ) {
            return $result;
        }

        $file = $_FILES['media_file'];

        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return $result;
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            $result['error'] =
                'The file could not be uploaded. Please try again.';
            return $result;
        }

        $temporaryPath = $file['tmp_name'] ?? '';
        $fileSize = (int) ($file['size'] ?? 0);

        if (
            $temporaryPath === ''
            || !is_uploaded_file($temporaryPath)
            || !is_file($temporaryPath)
        ) {
            $result['error'] = 'The uploaded file is invalid.';
            return $result;
        }

        if ($fileSize <= 0) {
            $result['error'] = 'The selected file is empty.';
            return $result;
        }

        // Detect the real file type instead of trusting its filename.
        if (!class_exists('finfo')) {
            $result['error'] =
                'File validation is unavailable on this server.';
            return $result;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($temporaryPath);

        $allowedTypes = [
            'image/jpeg' => [
                'extension' => 'jpg',
                'type' => 'image',
                'limit' => 5 * 1024 * 1024
            ],
            'image/png' => [
                'extension' => 'png',
                'type' => 'image',
                'limit' => 5 * 1024 * 1024
            ],
            'image/webp' => [
                'extension' => 'webp',
                'type' => 'image',
                'limit' => 5 * 1024 * 1024
            ],
            'video/mp4' => [
                'extension' => 'mp4',
                'type' => 'video',
                'limit' => 25 * 1024 * 1024
            ],
            'video/webm' => [
                'extension' => 'webm',
                'type' => 'video',
                'limit' => 25 * 1024 * 1024
            ]
        ];

        if (
            !is_string($mimeType)
            || !isset($allowedTypes[$mimeType])
        ) {
            $result['error'] =
                'Unsupported file. Choose a JPG, PNG, WebP, MP4, or WebM file.';
            return $result;
        }

        $fileType = $allowedTypes[$mimeType];

        if ($fileSize > $fileType['limit']) {
            $result['error'] = $fileType['type'] === 'image'
                ? 'Photos must be 5 MB or smaller.'
                : 'Videos must be 25 MB or smaller.';

            return $result;
        }

        // Save uploads inside the public directory.
        $uploadDirectory =
            dirname(__DIR__, 2) . '/public/uploads/posts';

        if (
            !is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0755, true)
            && !is_dir($uploadDirectory)
        ) {
            $result['error'] =
                'The upload folder could not be created.';
            return $result;
        }

        if (!is_writable($uploadDirectory)) {
            $result['error'] =
                'The upload folder is not writable.';
            return $result;
        }

        try {
            $newFileName =
                bin2hex(random_bytes(16))
                . '.'
                . $fileType['extension'];
        } catch (Throwable $e) {
            $result['error'] =
                'Unable to prepare the uploaded file.';
            return $result;
        }

        $destination = $uploadDirectory . '/' . $newFileName;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            $result['error'] =
                'Unable to save the uploaded file.';
            return $result;
        }

        // Store a relative URL in the database.
        $relativePath = 'uploads/posts/' . $newFileName;

        if ($fileType['type'] === 'image') {
            $result['image'] = $relativePath;
        } else {
            $result['video'] = $relativePath;
        }

        return $result;
    }

    // Remove a newly uploaded file if creating the post fails.
    private function removeUploadedFile(?string $relativePath): void
    {
        if (
            $relativePath === null
            || !preg_match(
                '/\Auploads\/posts\/[a-f0-9]{32}\.(jpg|png|webp|mp4|webm)\z/',
                $relativePath
            )
        ) {
            return;
        }

        $fullPath = dirname(__DIR__, 2)
            . '/public/'
            . $relativePath;

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    // Update a post's text while keeping existing media.
    public function update(): void
    {
        $this->requirePostAndCsrf();

        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        $content = trim($_POST['content'] ?? '');

        if (!$postId || $postId < 1) {
            $this->redirectHome('Invalid post.');
        }

        if ($content === '' || strlen($content) > 5000) {
            $this->redirectHome(
                'Your post must contain 1–5000 characters.'
            );
        }

        $updated = $this->postModel->updatePost(
            (int) $postId,
            (int) $_SESSION['user_id'],
            $content
        );

        $this->redirectHome(
            $updated
                ? 'Your post was updated.'
                : 'Unable to update that post.'
        );
    }

    // Delete a post and show the message near its former position.
    public function delete(): void
    {
        $this->requirePostAndCsrf();

        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if (!$postId || $postId < 1) {
            $this->redirectHome('Invalid post.');
        }

        $allPosts = $this->postModel->getAllPosts();
        $nearbyPostId = 0;
        $foundCurrent = false;

        foreach ($allPosts as $post) {
            $currentId = (int) $post['id'];

            if ($foundCurrent) {
                $nearbyPostId = $currentId;
                break;
            }

            if ($currentId === (int) $postId) {
                $foundCurrent = true;
            }
        }

        if ($nearbyPostId === 0) {
            foreach ($allPosts as $post) {
                if ((int) $post['id'] !== (int) $postId) {
                    $nearbyPostId = (int) $post['id'];
                    break;
                }
            }
        }

        $deleted = $this->postModel->deletePost(
            (int) $postId,
            (int) $_SESSION['user_id']
        );

        if ($deleted) {
            $_SESSION['flash_message'] = 'Your post was deleted.';
            $_SESSION['flash_post_id'] = $nearbyPostId;

            $url = 'index.php?page=home';

            if ($nearbyPostId > 0) {
                $url .= '#post-' . $nearbyPostId;
            }

            header('Location: ' . $url);
            exit;
        }

        $this->redirectHome('Unable to delete that post.');
    }

    // Create a comment.
    public function createComment(): void
    {
        $this->requirePostAndCsrf();

        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        $content = trim($_POST['content'] ?? '');

        if (!$postId || $postId < 1) {
            $this->redirectBack('Invalid post.');
        }

        if ($content === '' || strlen($content) > 2000) {
            $this->redirectBack(
                'Your comment must contain 1–2000 characters.'
            );
        }

        if (!$this->postModel->getPostById((int) $postId)) {
            $this->redirectBack('That post could not be found.');
        }

        $this->postModel->createComment(
            (int) $postId,
            (int) $_SESSION['user_id'],
            $content
        );

        $this->redirectBack('Your comment was added.');
    }

    // Delete a comment and show the message inside its post.
    public function deleteComment(): void
    {
        $this->requirePostAndCsrf();

        $commentId = filter_input(
            INPUT_POST,
            'comment_id',
            FILTER_VALIDATE_INT
        );

        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if (!$commentId || $commentId < 1) {
            $this->redirectBack('Invalid comment.');
        }

        if (!$postId || $postId < 1) {
            $this->redirectHome(
                'Unable to identify the comment post.'
            );
        }

        $deleted = $this->postModel->deleteComment(
            (int) $commentId,
            (int) $_SESSION['user_id']
        );

        $_SESSION['flash_message'] = $deleted
            ? 'Your comment was deleted.'
            : 'Unable to delete that comment.';

        $_SESSION['flash_post_id'] = (int) $postId;

        $this->redirectBack();
    }

    
    // Like a post.
    public function like(): void
    {
        $this->requirePostAndCsrf();

        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if (!$postId || $postId < 1) {
            $this->sendLikeResponse(
                false,
                'Invalid post.',
                400
            );
        }

        if (!$this->postModel->getPostById((int) $postId)) {
            $this->sendLikeResponse(
                false,
                'That post could not be found.',
                404
            );
        }

        $this->postModel->likePost(
            (int) $postId,
            (int) $_SESSION['user_id']
        );

        $this->sendLikeResponse(
            true,
            'Post liked.',
            200,
            (int) $postId
        );
    }

    // Unlike a post.
    public function unlike(): void
    {
        $this->requirePostAndCsrf();

        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if (!$postId || $postId < 1) {
            $this->sendLikeResponse(
                false,
                'Invalid post.',
                400
            );
        }

        if (!$this->postModel->getPostById((int) $postId)) {
            $this->sendLikeResponse(
                false,
                'That post could not be found.',
                404
            );
        }

        $this->postModel->unlikePost(
            (int) $postId,
            (int) $_SESSION['user_id']
        );

        $this->sendLikeResponse(
            true,
            'Post unliked.',
            200,
            (int) $postId
        );
    }

    // Return JSON for AJAX requests and redirects for regular form submissions.
    private function sendLikeResponse(
        bool $success,
        string $message,
        int $statusCode = 200,
        int $postId = 0
    ): void {
        $isAjax = strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''
        ) === 'xmlhttprequest';

        if ($isAjax) {
            if ($success && $postId > 0) {
                $userId = (int) $_SESSION['user_id'];

                $likeCount = $this->postModel->getLikeCount($postId);
                $userLiked = $this->postModel->hasUserLikedPost(
                    $postId,
                    $userId
                );
            }

            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => $success,
                'message' => $message,
                'post_id' => $postId,
                'like_count' => $success && $postId > 0
                    ? $likeCount
                    : null,
                'user_liked' => $success && $postId > 0
                    ? $userLiked
                    : null
            ]);

            exit;
        }

        if (!$success) {
            $this->redirectBack($message);
        }

        $this->redirectBack();
    }

    // Require login, POST, and a valid CSRF token.
    private function requirePostAndCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=home');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (
            !is_string($submittedToken)
            || !is_string($sessionToken)
            || $sessionToken === ''
            || !hash_equals($sessionToken, $submittedToken)
        ) {
            http_response_code(403);
            exit(
                'Invalid security token. Please refresh the page and try again.'
            );
        }
    }

    // Return to search results when the form came from search.
    private function redirectBack(?string $message = null): void
    {
        if ($message !== null) {
            $_SESSION['flash_message'] = $message;
        }

        $returnTo = $_POST['return_to'] ?? '';
        $postId = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if (
            is_string($returnTo)
            && preg_match(
                '/\Aindex\.php\?page=search(?:&q=[a-zA-Z0-9%._+\-]*)?\z/',
                $returnTo
            )
        ) {
            $url = $returnTo;

            if ($postId && $postId > 0) {
                $url .= '#post-' . $postId;
            }

            header('Location: ' . $url);
            exit;
        }

        if ($postId && $postId > 0) {
            header(
                'Location: index.php?page=home#post-' . $postId
            );
            exit;
        }

        $this->redirectHome($message);
    }

    // Return to the home page.
    private function redirectHome(?string $message = null): void
    {
        if ($message !== null) {
            $_SESSION['flash_message'] = $message;
        }

        header('Location: index.php?page=home');
        exit;
    }
}