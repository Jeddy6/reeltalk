
<?php

require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new UserModel($db);
    }

    private function postString($key, $trim = true)
    {
        $value = $_POST[$key] ?? '';

        if (!is_string($value)) {
            return '';
        }

        return $trim ? trim($value) : $value;
    }

    private function createCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function validCsrfToken()
    {
        return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
            && is_string($_POST['csrf_token'])
            && hash_equals(
                $_SESSION['csrf_token'],
                $_POST['csrf_token']
            );
    }

    private function render($page, $data = [])
    {
        extract($data, EXTR_SKIP);

        require __DIR__ . '/../views/' . $page . '.php';
    }

    public function showLogin()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        $this->render('login', [
            'error' => '',
            'csrfToken' => $this->createCsrfToken()
        ]);
    }

    public function showRegister()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        $this->render('register', [
            'error' => '',
            'csrfToken' => $this->createCsrfToken()
        ]);
    }

    public function register()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        if (!$this->validCsrfToken()) {
            http_response_code(403);

            $this->render('register', [
                'error' => 'Invalid form request. Please refresh and try again.',
                'csrfToken' => $this->createCsrfToken()
            ]);
            return;
        }

        $username = $this->postString('username');
        $email = $this->postString('email');
        $fullName = $this->postString('full_name');
        $password = $this->postString('password', false);
        $confirmPassword = $this->postString('confirm_password', false);

        $error = '';

        if (
            $username === '' ||
            $email === '' ||
            $fullName === '' ||
            $password === '' ||
            $confirmPassword === ''
        ) {
            $error = 'Please complete all fields.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $error = 'Username must be 3-30 characters using letters, numbers, or underscores.';
        } elseif (strlen($fullName) > 100) {
            $error = 'Full name must be 100 characters or fewer.';
        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 100
        ) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (strlen($password) > 72) {
            $error = 'Password must not exceed 72 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif ($this->userModel->usernameExists($username)) {
            $error = 'That username is already taken.';
        } elseif ($this->userModel->emailExists($email)) {
            $error = 'That email is already registered.';
        }

        if ($error !== '') {
            $this->render('register', [
                'error' => $error,
                'csrfToken' => $this->createCsrfToken()
            ]);
            return;
        }

        try {
            $this->userModel->createUser(
                $username,
                $email,
                $password,
                $fullName
            );

            $_SESSION['success_message'] =
                'Account created successfully! You can now log in.';

            header('Location: index.php?page=login');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());

            $this->render('register', [
                'error' => 'Unable to create your account. Please try again.',
                'csrfToken' => $this->createCsrfToken()
            ]);
        }
    }

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        if (!$this->validCsrfToken()) {
            http_response_code(403);

            $this->render('login', [
                'error' => 'Invalid form request. Please refresh and try again.',
                'csrfToken' => $this->createCsrfToken()
            ]);
            return;
        }

        $email = $this->postString('email');
        $password = $this->postString('password', false);

        $user = null;

        if ($email !== '' && $password !== '') {
            $user = $this->userModel->findByEmail($email);
        }

        if (
            $user &&
            password_verify($password, $user['password'])
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            unset($_SESSION['csrf_token']);

            header('Location: index.php');
            exit;
        }

        $this->render('login', [
            'error' => 'Incorrect email or password.',
            'csrfToken' => $this->createCsrfToken()
        ]);
    }

    public function logout()
    {
        if (!$this->validCsrfToken()) {
            http_response_code(403);
            exit('Invalid logout request. Please return to the home page and try again.');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: index.php?page=login');
        exit;
    }

    public function getCsrfToken()
    {
        return $this->createCsrfToken();
    }
}
