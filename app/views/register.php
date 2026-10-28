
<?php
function registerEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - ReelTalk</title>

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
            max-width: 430px;
            padding: 30px;
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
            margin-top: 15px;
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

        .error {
            padding: 12px;
            margin: 15px 0;
            border-radius: 7px;
            background: #48282b;
            color: #ffb2b2;
        }

        .link {
            text-align: center;
            margin-top: 20px;
            color: #c1c4d0;
        }

        a { color: #f4b860; }

        .hint {
            margin-top: 5px;
            color: #aeb2c2;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>ReelTalk</h1>
        <p class="subtitle">Join the movie conversation.</p>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?= registerEscape($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=register">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= registerEscape($csrfToken) ?>"
            >

            <label for="full_name">Full Name</label>
            <input
                id="full_name"
                type="text"
                name="full_name"
                maxlength="100"
                autocomplete="name"
                required
            >

            <label for="username">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                minlength="3"
                maxlength="30"
                pattern="[A-Za-z0-9_]{3,30}"
                autocomplete="username"
                required
            >
            <p class="hint">Use 3-30 letters, numbers, or underscores.</p>

            <label for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                maxlength="100"
                autocomplete="email"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                required
            >

            <label for="confirm_password">Confirm Password</label>
            <input
                id="confirm_password"
                type="password"
                name="confirm_password"
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                required
            >

            <button type="submit">Create Account</button>
        </form>

        <p class="link">
            Already have an account?
            <a href="index.php?page=login">Log in</a>
        </p>
    </main>
</body>
</html>
