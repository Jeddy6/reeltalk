
<?php
function loginEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - ReelTalk</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #171923;
            color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 420px;
            padding: 32px;
            background: #242735;
            border: 1px solid #383c4d;
            border-radius: 15px;
        }

        h1 {
            color: #f4b860;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #c1c4d0;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            padding: 12px;
            color: white;
            background: #171923;
            border: 1px solid #484d60;
            border-radius: 7px;
        }

        input:focus {
            outline: 2px solid #f4b860;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #f4b860;
            color: #171923;
            font-weight: bold;
            cursor: pointer;
        }

        .error, .success {
            padding: 12px;
            margin-top: 15px;
            border-radius: 7px;
        }

        .error {
            background: #48282b;
            color: #ffb2b2;
        }

        .success {
            background: #183c30;
            color: #91e0b9;
        }

        .link {
            text-align: center;
            margin-top: 20px;
            color: #c1c4d0;
        }

        a { color: #f4b860; }
    </style>
</head>
<body>
    <main class="card">
        <h1>ReelTalk</h1>
        <p class="subtitle">Welcome back, movie lover.</p>

        <?php if ($successMessage !== ''): ?>
            <div class="success">
                <?= loginEscape($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?= loginEscape($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=login">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= loginEscape($csrfToken) ?>"
            >

            <label for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                autocomplete="email"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Log In</button>
        </form>

        <p class="link">
            New to ReelTalk?
            <a href="index.php?page=register">Create an account</a>
        </p>
    </main>
</body>
</html>
