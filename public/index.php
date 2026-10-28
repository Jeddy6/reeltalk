
<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Movie.php';
require_once __DIR__ . '/../app/models/Review.php';
require_once __DIR__ . '/../app/controllers/MovieController.php';
require_once __DIR__ . '/../app/controllers/PostController.php';
require_once __DIR__ . '/../app/controllers/ProfileController.php';
require_once __DIR__ . '/../app/controllers/FollowController.php';
require_once __DIR__ . '/../app/controllers/SearchController.php';
require_once __DIR__ . '/../app/models/WatchlistModel.php';
require_once __DIR__ . '/../app/controllers/WatchlistController.php';

try {
    $database = new Database();
    $conn = $database->connect();
} catch (Throwable $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit('Unable to connect to Chirply. Please check the database configuration.');
}

$auth = new AuthController($conn);
$movieController = new MovieController($conn);
$postController = new PostController($conn);
$profileController = new ProfileController($conn);
$followController = new FollowController($conn);
$searchController = new SearchController($conn);
$watchlistController = new WatchlistController($conn);

$method = $_SERVER['REQUEST_METHOD'];
$page = $_GET['page'] ?? 'home';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!is_string($page) || !is_string($action)) {
    http_response_code(400);
    exit('Invalid request.');
}

// Handle form submissions.
if ($method === 'POST') {
    switch ($action) {
        case 'register':
            $auth->register();
            break;

        case 'login':
            $auth->login();
            break;

        case 'logout':
            $auth->logout();
            break;

        case 'save_review':
            $movieController->saveReview();
            break;

        case 'delete_review':
            $movieController->deleteReview();
            break;

        case 'create_post':
            $postController->create();
            break;

        case 'update_post':
            $postController->update();
            break;

        case 'delete_post':
            $postController->delete();
            break;

        case 'create_comment':
            $postController->createComment();
            break;

        case 'delete_comment':
            $postController->deleteComment();
            break;

        case 'like_post':
            $postController->like();
            break;

        case 'unlike_post':
            $postController->unlike();
            break;

        case 'update_profile':
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?page=login');
                exit;
            }

            $profileController->update();
            break;

        case 'follow_user':
            $followController->follow();
            break;

        case 'unfollow_user':
            $followController->unfollow();
            break;

        case 'watchlist_add':
            $watchlistController->add();
            break;

        case 'watchlist_update':
            $watchlistController->updateStatus();
            break;

        case 'watchlist_remove':
            $watchlistController->remove();
            break;

        default:
            http_response_code(404);
            echo 'Action not found.';
            break;
    }

    exit;
}

// Only allow GET and POST.
if ($method !== 'GET') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Method not allowed.');
}

// Public pages.
switch ($page) {
    case 'register':
        $auth->showRegister();
        exit;

    case 'login':
        $auth->showLogin();
        exit;
}

// Other pages require login.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$userModel = new UserModel($conn);
$user = $userModel->findById((int) $_SESSION['user_id']);

if (!$user) {
    $_SESSION = [];
    session_destroy();

    header('Location: index.php?page=login');
    exit;
}

// Create a CSRF token if needed.
if (
    !isset($_SESSION['csrf_token'])
    || !is_string($_SESSION['csrf_token'])
    || $_SESSION['csrf_token'] === ''
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Display the requested page.
switch ($page) {
    case 'home':
        $postController->index();
        break;

    case 'profile':
        $profileController->show();
        break;

    case 'edit_profile':
        $profileController->edit();
        break;

    case 'search':
        $searchController->index();
        break;

    case 'movies':
        $movieController->index();
        break;

    case 'movie':
        $movieController->show();
        break;

    case 'watchlist':
        $watchlistController->index();
        break;

    default:
        http_response_code(404);
        echo 'Page not found.';
        break;
}
