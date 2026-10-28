
<?php
$fullName = htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
$bio = htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8');
$profileImage = $user['profile_image'] ?? '';
$username = htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Chirply</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            color: #29243a;
        }

        .navbar {
            background: #6c4ab6;
            color: white;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #6c4ab6;
        }

        .subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-weight: bold;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #d8d0e8;
            border-radius: 8px;
            font: inherit;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 15px 0;
            border: 3px solid #e5dcff;
        }

        .avatar-placeholder {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: #e5dcff;
            color: #6c4ab6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: bold;
            margin: 15px 0;
        }

        .hint {
            color: #777;
            font-size: 13px;
            margin-top: 8px;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        button,
        .cancel {
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #6c4ab6;
            color: white;
        }

        button:hover {
            background: #573797;
        }

        .cancel {
            background: #eee9f8;
            color: #49356f;
        }

        .error {
            background: #ffe5e5;
            color: #9b2222;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px 12px;
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <h2>Chirply</h2>

        <a href="index.php?page=profile">Back to Profile</a>
    </nav>

    <main class="container">
        <h1>Edit Your Profile</h1>
        <p class="subtitle">
            Update how other people see you on Chirply.
        </p>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <label>Username</label>
            <input type="text" value="@<?= $username ?>" disabled>

            <label for="full_name">Display Name</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?= $fullName ?>"
                maxlength="100"
                required
            >

            <label for="bio">Bio</label>
            <textarea
                id="bio"
                name="bio"
                maxlength="500"
                placeholder="Tell people a little about yourself..."
            ><?= $bio ?></textarea>

            <label>Current Profile Picture</label>

            <?php if (!empty($profileImage)): ?>
                <img
                    class="avatar"
                    src="<?= htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Current profile picture"
                >
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user['full_name'] ?? $user['username'] ?? '?', 0, 1)) ?>
                </div>
            <?php endif; ?>

            <label for="profile_image">Upload a New Profile Picture</label>
            <input
                type="file"
                id="profile_image"
                name="profile_image"
                accept="image/jpeg,image/png,image/webp"
            >

            <p class="hint">
                Optional. Accepted formats: JPG, PNG, and WebP. Maximum size: 2 MB.
            </p>

            <div class="buttons">
                <button type="submit">Save Changes</button>
                <a class="cancel" href="index.php?page=profile">Cancel</a>
            </div>
        </form>
    </main>

</body>
</html>