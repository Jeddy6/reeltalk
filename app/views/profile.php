
<?php

if (!function_exists('chirplyEscape')) {
    function chirplyEscape($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

$profileUser = $profileUser ?? [];
$posts = $posts ?? [];
$csrfToken = $csrfToken ?? '';
$isOwnProfile = $isOwnProfile ?? false;
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

$followerCount = (int) ($followerCount ?? 0);
$followingCount = (int) ($followingCount ?? 0);
$isFollowing = $isFollowing ?? false;

$displayName = !empty($profileUser['full_name'])
    ? $profileUser['full_name']
    : ($profileUser['username'] ?? 'User');

$initial = strtoupper(substr($displayName, 0, 1));
$profileImage = $profileUser['profile_image'] ?? '';
$username = $profileUser['username'] ?? 'user';
$profileId = (int) ($profileUser['id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= chirplyEscape($displayName) ?> - Chirply</title>

    <style>
        :root {
            --purple: #7157e8;
            --purple-dark: #5740c7;
            --purple-light: #eee9ff;
            --text: #29243b;
            --muted: #89849a;
            --border: #eeeaf6;
            --danger: #d83e5b;
            --shadow: 0 8px 30px rgba(74, 51, 139, 0.07);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background:
                radial-gradient(
                    circle at 5% 5%,
                    rgba(224, 211, 255, 0.45),
                    transparent 27%
                ),
                #f7f6fc;
            min-height: 100vh;
        }

        body.modal-open {
            overflow: hidden;
        }

        button,
        input,
        textarea {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 100;
            color: white;
            background: linear-gradient(110deg, #5942c9, #8065f0);
            box-shadow: 0 5px 20px rgba(78, 55, 173, 0.16);
        }

        .nav {
            max-width: 1050px;
            min-height: 72px;
            padding: 14px 22px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .brand {
            color: white;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -1px;
            text-decoration: none;
        }

        .brand span {
            color: #e8dfff;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            padding: 10px 12px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: white;
            background: rgba(255, 255, 255, 0.16);
        }

        .nav-links form {
            margin: 0;
        }

        .logout-btn {
            color: var(--purple-dark);
            background: white;
            border: none;
            padding: 10px 15px;
            border-radius: 9px;
            font-weight: 700;
        }

        .container {
            width: 100%;
            max-width: 790px;
            margin: 32px auto 60px;
            padding: 0 18px;
        }

        .back-link {
            display: inline-block;
            color: var(--purple-dark);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 17px;
        }

        .back-link:hover {
            color: var(--purple);
        }

        .card {
            background: white;
            border: 1px solid rgba(231, 225, 246, 0.9);
            border-radius: 19px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .profile-cover {
            height: 115px;
            background:
                radial-gradient(
                    circle at 80% 20%,
                    rgba(255, 255, 255, 0.38) 0 5%,
                    transparent 5.5%
                ),
                radial-gradient(
                    circle at 88% 65%,
                    rgba(255, 255, 255, 0.18) 0 12%,
                    transparent 12.5%
                ),
                linear-gradient(115deg, #7256e8, #a58af8 60%, #e4bdf7);
        }

        .profile-top {
            position: relative;
            text-align: center;
            padding: 0 28px 29px;
        }

        .avatar {
            width: 112px;
            height: 112px;
            border: 5px solid white;
            border-radius: 50%;
            background: linear-gradient(135deg, #e6dcff, #f5e2ff);
            color: var(--purple-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 43px;
            font-weight: 800;
            margin: -57px auto 13px;
            position: relative;
            box-shadow: 0 5px 18px rgba(65, 44, 125, 0.15);
        }

        .avatar img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        h1 {
            margin: 10px 0 7px;
            font-size: clamp(24px, 4vw, 31px);
            letter-spacing: -0.7px;
            overflow-wrap: anywhere;
        }

        .username {
            color: var(--muted);
            font-size: 14px;
            margin: 0 0 16px;
        }

        .bio {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.8;
            color: #5e586f;
            max-width: 510px;
            margin: 0 auto 19px;
            font-size: 15px;
        }

        .muted {
            color: var(--muted);
            font-size: 12px;
        }

        .member-date {
            margin: 0 0 20px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            margin: 20px auto 22px;
        }

        .stat {
            min-width: 125px;
            padding: 3px 22px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .stat + .stat {
            border-left: 1px solid var(--border);
        }

        .stat-number {
            font-size: 21px;
            font-weight: 800;
            color: var(--text);
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        .profile-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .action-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            min-width: 145px;
            padding: 12px 20px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 700;
            transition:
                transform 0.2s,
                background 0.2s,
                box-shadow 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-btn:disabled {
            opacity: 0.7;
            cursor: wait;
            transform: none;
        }

        .follow-btn,
        .edit-btn {
            color: white;
            background: linear-gradient(110deg, #7157e8, #8a69f2);
            box-shadow: 0 5px 13px rgba(103, 77, 214, 0.2);
        }

        .follow-btn:hover,
        .edit-btn:hover {
            background: var(--purple-dark);
        }

        .unfollow-btn {
            color: var(--purple-dark);
            background: var(--purple-light);
            border-color: #e2d9ff;
        }

        .unfollow-btn:hover {
            background: #e3dcff;
        }

        .edit-btn {
            text-decoration: none;
        }

        #followMessage {
            flex-basis: 100%;
            width: 100%;
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
        }

        #followMessage.error {
            color: var(--danger);
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 30px 2px 16px;
        }

        .section-heading h2 {
            font-size: 21px;
            margin: 0;
            letter-spacing: -0.4px;
        }

        .post-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 27px;
            padding: 0 9px;
            border-radius: 20px;
            background: var(--purple-light);
            color: var(--purple-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .post-card {
            padding: 22px;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .post-avatar {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--purple-light);
            color: var(--purple-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 17px;
            font-weight: 800;
            overflow: hidden;
        }

        .post-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-author-name {
            font-size: 14px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .post-date {
            color: var(--muted);
            font-size: 11px;
            margin-top: 4px;
        }

        .post-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.8;
            font-size: 15px;
            color: #484257;
            margin: 20px 0;
        }

        .post-image {
            display: block;
            width: 100%;
            max-height: 520px;
            object-fit: contain;
            border-radius: 13px;
            background: #f7f5fc;
            border: 1px solid var(--border);
        }

        .post-video {
    display: block;
    width: 100%;
    max-width: 100%;
    max-height: 520px;
    object-fit: contain;
    border-radius: 13px;
    background: #f7f5fc;
    border: 1px solid var(--border);
}

        .post-actions {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
            padding-top: 16px;
            margin-top: 17px;
            border-top: 1px solid #f0edf6;
        }

        .post-actions form,
        .comment form {
            margin: 0;
        }

        .small-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            background: #f4f1ff;
            color: var(--purple-dark);
            border: 1px solid transparent;
            border-radius: 9px;
            padding: 9px 13px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            transition: background 0.2s, transform 0.2s;
        }

        .small-btn:hover {
            background: #e9e2ff;
            transform: translateY(-1px);
        }

        .like-btn {
    color: var(--purple-dark);
}

        .liked-btn {
            background: #7157e8;
            color: white;
        }

        .liked-btn:hover {
            background: #5740c7;
        }

        .delete-btn {
            background: #fff0f2;
            color: var(--danger);
        }

        .delete-btn:hover {
            background: #ffe1e7;
        }

        .comments {
            margin-top: 22px;
            padding-top: 19px;
            border-top: 1px solid var(--border);
        }

        .comments h3 {
            margin: 0 0 10px;
            font-size: 15px;
        }

        .comment {
            padding: 13px 0;
            border-bottom: 1px solid #f3f0f7;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment strong {
            font-size: 13px;
        }

        .comment-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.7;
            color: #5d566e;
            font-size: 13px;
            margin: 7px 0 10px;
        }

        .empty {
            text-align: center;
            color: var(--muted);
            padding: 42px 20px;
        }

        .empty-icon {
            font-size: 34px;
            margin-bottom: 10px;
        }

        .empty p {
            margin: 0;
            line-height: 1.7;
        }

        /* Delete confirmation popup */
        .delete-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(28, 20, 52, 0.62);
            backdrop-filter: blur(5px);
        }

        .delete-modal-overlay.show {
            display: flex;
            animation: fadeIn 0.18s ease;
        }

        .delete-modal {
            width: 100%;
            max-width: 420px;
            padding: 25px;
            background: white;
            border-radius: 19px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            animation: modalIn 0.2s ease;
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .modal-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 13px;
            background: #fff0f2;
            color: var(--danger);
            font-size: 21px;
        }

        .modal-title {
            flex: 1;
            margin: 0;
            font-size: 20px;
        }

        .modal-close {
            border: none;
            background: transparent;
            color: #777;
            font-size: 27px;
            cursor: pointer;
            border-radius: 7px;
        }

        .modal-message {
            color: #70697d;
            line-height: 1.8;
            font-size: 14px;
            margin: 0 0 24px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        .modal-action-btn {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .modal-cancel {
            background: var(--purple-light);
            color: var(--purple-dark);
        }

        .modal-confirm {
            background: var(--danger);
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(8px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 620px) {
            .nav {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
                padding: 14px 17px;
            }

            .nav-links {
                justify-content: flex-start;
                width: 100%;
                gap: 3px;
            }

            .nav-links a {
                padding: 9px 8px;
                font-size: 12px;
            }

            .logout-btn {
                padding: 9px 10px;
                font-size: 12px;
            }

            .container {
                margin-top: 22px;
                padding: 0 12px;
            }

            .profile-top {
                padding-left: 17px;
                padding-right: 17px;
            }

            .profile-cover {
                height: 92px;
            }

            .avatar {
                width: 100px;
                height: 100px;
                margin-top: -51px;
            }

            .stat {
                min-width: 0;
                flex: 1;
                padding: 3px 12px;
            }

            .post-card {
                padding: 17px;
            }

            .section-heading h2 {
                font-size: 19px;
            }

            .modal-actions {
                flex-direction: column-reverse;
            }

            .modal-action-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="nav">
        <a class="brand" href="index.php?page=home">
            Chirply<span>.</span>
        </a>

        <nav class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a class="active" href="index.php?page=profile">My Profile</a>
            <a href="index.php?page=search">Search</a>
            <a href="index.php?page=movies">Movies</a>
            <a href="index.php?page=watchlist">Watchlist</a>

            <form action="index.php?action=logout" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= chirplyEscape($csrfToken) ?>"
                >
                <button class="logout-btn" type="submit">Log out</button>
            </form>
        </nav>
    </div>
</header>

<main class="container">

    <a class="back-link" href="index.php?page=home">
        &larr; Back to Home
    </a>

    <section class="card">
        <div class="profile-cover"></div>

        <div class="profile-top">
            <div class="avatar">
                <?php if (!empty($profileImage)): ?>
                    <img
                        src="<?= chirplyEscape($profileImage) ?>"
                        alt="Profile picture of <?= chirplyEscape($displayName) ?>"
                    >
                <?php else: ?>
                    <?= chirplyEscape($initial) ?>
                <?php endif; ?>
            </div>

            <h1><?= chirplyEscape($displayName) ?></h1>

            <p class="username">
                @<?= chirplyEscape($username) ?>
            </p>

            <div class="bio"><?= !empty($profileUser['bio'])
                ? chirplyEscape($profileUser['bio'])
                : 'This user has not added a bio yet.' ?></div>

            <?php if (!empty($profileUser['created_at'])): ?>
                <p class="muted member-date">
                    ✦ Member since
                    <?= chirplyEscape(date(
                        'F Y',
                        strtotime($profileUser['created_at'])
                    )) ?>
                </p>
            <?php endif; ?>

            <div class="profile-stats">
                <div class="stat">
                    <span
                        class="stat-number"
                        id="followerCount"
                    ><?= $followerCount ?></span>
                    <span class="stat-label">Followers</span>
                </div>

                <div class="stat">
                    <span class="stat-number"><?= $followingCount ?></span>
                    <span class="stat-label">Following</span>
                </div>

                <div class="stat">
                    <span class="stat-number"><?= count($posts) ?></span>
                    <span class="stat-label">Posts</span>
                </div>
            </div>

            <div class="profile-actions">
                <?php if ($isOwnProfile): ?>

                    <a
                        class="action-btn edit-btn"
                        href="index.php?page=edit_profile"
                    >
                        ✎ Edit Profile
                    </a>

                <?php else: ?>

                    <form
                        id="followForm"
                        action="index.php?action=<?= $isFollowing
                            ? 'unfollow_user'
                            : 'follow_user' ?>"
                        method="POST"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= chirplyEscape($csrfToken) ?>"
                        >

                        <input
                            type="hidden"
                            name="user_id"
                            value="<?= $profileId ?>"
                        >

                        <button
                            id="followButton"
                            class="action-btn <?= $isFollowing
                                ? 'unfollow-btn'
                                : 'follow-btn' ?>"
                            type="submit"
                        ><?= $isFollowing ? '✓ Following' : '+ Follow' ?></button>
                    </form>

                    <p
                        id="followMessage"
                        role="status"
                        aria-live="polite"
                        style="display:none;"
                    ></p>

                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="section-heading">
        <h2>
            <?= $isOwnProfile
                ? 'My Posts'
                : 'Posts by ' . chirplyEscape($displayName) ?>
        </h2>

        <span class="post-count"><?= count($posts) ?></span>
    </div>

    <?php if (empty($posts)): ?>
        <section class="card empty">
            <div class="empty-icon">✧</div>
            <p>
                <?= $isOwnProfile
                    ? 'You have not published any posts yet. Your posts will appear here!'
                    : 'This user has not published any posts yet.' ?>
            </p>
        </section>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <?php
        $postId = (int) $post['id'];
        $postOwnerId = (int) ($post['user_id'] ?? $profileUser['id'] ?? 0);
        $isPostOwner = $postOwnerId === $currentUserId;
        $userLiked = !empty($post['user_liked']);
        $likeCount = (int) ($post['like_count'] ?? 0);
        $comments = $post['comments'] ?? [];
        ?>

        <article class="card post-card" id="post-<?= $postId ?>">

            
<div class="post-header">
    <div class="post-author">
        <a
            href="index.php?page=profile&id=<?= $postOwnerId ?>"
            style="text-decoration:none;"
        >
            <div class="post-avatar">
                <?php if (!empty($profileImage)): ?>
                    <img
                        src="<?= chirplyEscape($profileImage) ?>"
                        alt=""
                    >
                <?php else: ?>
                    <?= chirplyEscape($initial) ?>
                <?php endif; ?>
            </div>
        </a>

        <div>
            <a
                class="post-author-name"
                href="index.php?page=profile&id=<?= $postOwnerId ?>"
                style="color:var(--text); text-decoration:none;"
            >
                <?= chirplyEscape($displayName) ?>
            </a>

            <div class="post-date">
                @<?= chirplyEscape($username) ?>
                ·
                <?= chirplyEscape($post['created_at'] ?? '') ?>
            </div>
        </div>
    </div>
</div>


            <?php if (!empty($post['content'])): ?>
                <div class="post-content"><?= chirplyEscape($post['content']) ?></div>
            <?php endif; ?>

            <?php if (!empty($post['image'])): ?>
                <p>
                    <img
                        class="post-image"
                        src="<?= chirplyEscape($post['image']) ?>"
                        alt="Post image"
                        loading="lazy"
                    >
                </p>
            <?php endif; ?>

            
            <?php if (!empty($post['video'])): ?>
                <video
                    class="post-video"
                    controls
                    preload="metadata"
                >
                    <source
                        src="<?= chirplyEscape($post['video']) ?>"
                        type="<?= str_ends_with(
                            strtolower($post['video']),
                            '.webm'
                        ) ? 'video/webm' : 'video/mp4' ?>"
                    >
                    Your browser does not support video playback.
                </video>
            <?php endif; ?>


            <div class="post-actions">

                
<form
    class="ajax-like-form"
    action="index.php?action=<?= $userLiked ? 'unlike_post' : 'like_post' ?>"
    method="POST"
>
    <input
        type="hidden"
        name="csrf_token"
        value="<?= chirplyEscape($csrfToken) ?>"
    >

    <input
        type="hidden"
        name="post_id"
        value="<?= $postId ?>"
    >

    <button
        type="submit"
        class="small-btn <?= $userLiked ? 'liked-btn' : 'like-btn' ?>"
    >
        <?= $userLiked ? '♥ Liked' : '♡ Like' ?>
        (<span class="like-count"><?= $likeCount ?></span>)
    </button>
</form>


                <?php if ($isPostOwner): ?>
                    <form
                        class="js-confirm-delete"
                        action="index.php?action=delete_post"
                        method="POST"
                        data-modal-title="Delete post?"
                        data-modal-message="Are you sure you want to delete this post? Once deleted, it may not be recoverable."
                    >
                        
<input
    type="hidden"
    name="return_to"
    value="index.php?page=profile&id=<?= $profileId ?>"
>

<input
    type="hidden"
    name="post_id"
    value="<?= $postId ?>"
>


                        <button class="small-btn delete-btn" type="submit">
                            Delete post
                        </button>
                    </form>
                <?php endif; ?>

            </div>

            <section class="comments">
                <h3>Comments (<?= count($comments) ?>)</h3>

                <?php if (empty($comments)): ?>
                    <p class="muted">No comments yet.</p>
                <?php endif; ?>

                <?php foreach ($comments as $comment): ?>
                    <?php
                    $commentOwnerId = (int) ($comment['user_id'] ?? 0);
                    $isCommentOwner = $commentOwnerId === $currentUserId;
                    ?>

                    <div class="comment">
                        <strong>
                            <?= chirplyEscape(
                                $comment['full_name']
                                ?: ($comment['username'] ?? 'User')
                            ) ?>
                        </strong>

                        <span class="muted">
                            @<?= chirplyEscape($comment['username'] ?? 'user') ?>
                            ·
                            <?= chirplyEscape($comment['created_at'] ?? '') ?>
                        </span>

                        <div class="comment-content"><?= chirplyEscape($comment['content'] ?? '') ?></div>

                        <?php if ($isCommentOwner): ?>
                            <form
                                class="js-confirm-delete"
                                action="index.php?action=delete_comment"
                                method="POST"
                                data-modal-title="Delete comment?"
                                data-modal-message="Are you sure you want to delete this comment? This action cannot be undone."
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= chirplyEscape($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="comment_id"
                                    value="<?= (int) $comment['id'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="post_id"
                                    value="<?= $postId ?>"
                                >

                                <input
    type="hidden"
    name="return_to"
    value="index.php?page=profile&id=<?= $profileId ?>"
>

<button class="small-btn delete-btn" type="submit">
    Delete comment
</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <form action="index.php?action=create_comment" method="POST">
    <input type="hidden" name="csrf_token" value="<?= chirplyEscape($csrfToken) ?>">
    <input type="hidden" name="post_id" value="<?= $postId ?>">
    <input type="hidden" name="return_to" value="index.php?page=profile&id=<?= $profileId ?>">

    <textarea
        name="content"
        maxlength="2000"
        placeholder="Write a comment..."
        required
        style="width:100%; min-height:75px; padding:12px; border:1px solid var(--border); border-radius:10px; margin-top:12px;"
    ></textarea>

    <button type="submit" class="small-btn" style="margin-top:8px;">
        Comment
    </button>
</form>
            </section>

        </article>
    <?php endforeach; ?>

</main>

<!-- Delete confirmation popup -->
<div
    class="delete-modal-overlay"
    id="deleteModal"
    aria-hidden="true"
>
    <section
        class="delete-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteModalTitle"
        aria-describedby="deleteModalMessage"
    >
        <div class="modal-header">
            <div class="modal-icon" aria-hidden="true">!</div>

            <h2 class="modal-title" id="deleteModalTitle">
                Delete post?
            </h2>

            <button
                class="modal-close"
                id="closeDeleteModal"
                type="button"
                aria-label="Close confirmation"
            >&times;</button>
        </div>

        <p class="modal-message" id="deleteModalMessage">
            Are you sure you want to delete this item?
        </p>

        <div class="modal-actions">
            <button
                class="modal-action-btn modal-cancel"
                id="cancelDelete"
                type="button"
            >Cancel</button>

            <button
                class="modal-action-btn modal-confirm"
                id="confirmDelete"
                type="button"
            >Delete</button>
        </div>
    </section>
</div>

<script>
    // Delete confirmation popup.
    const deleteModal = document.getElementById('deleteModal');
    const modalTitle = document.getElementById('deleteModalTitle');
    const modalMessage = document.getElementById('deleteModalMessage');
    const cancelButton = document.getElementById('cancelDelete');
    const closeButton = document.getElementById('closeDeleteModal');
    const confirmButton = document.getElementById('confirmDelete');

    let formToDelete = null;
    let previouslyFocusedElement = null;

    function openDeleteModal(form) {
        formToDelete = form;
        previouslyFocusedElement = document.activeElement;

        modalTitle.textContent =
            form.dataset.modalTitle || 'Confirm deletion';

        modalMessage.textContent =
            form.dataset.modalMessage ||
            'Are you sure you want to delete this item?';

        deleteModal.classList.add('show');
        deleteModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        cancelButton.focus();
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('show');
        deleteModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        formToDelete = null;

        if (previouslyFocusedElement) {
            previouslyFocusedElement.focus();
        }
    }

    document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openDeleteModal(form);
        });
    });

    cancelButton.addEventListener('click', closeDeleteModal);
    closeButton.addEventListener('click', closeDeleteModal);

    confirmButton.addEventListener('click', function () {
        if (formToDelete) {
            const selectedForm = formToDelete;
            formToDelete = null;

            deleteModal.classList.remove('show');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');

            selectedForm.submit();
        }
    });

    deleteModal.addEventListener('click', function (event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            deleteModal.classList.contains('show')
        ) {
            closeDeleteModal();
        }
    });

    // Follow/unfollow without reloading the page.
    const followForm = document.getElementById('followForm');

    if (followForm) {
        const followButton = document.getElementById('followButton');
        const followMessage = document.getElementById('followMessage');
        const followerNumber = document.getElementById('followerCount');

        followForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (followButton.disabled) {
                return;
            }

            const wasFollowing =
                followButton.dataset.following === 'true';

            followButton.disabled = true;
            followButton.textContent = 'Please wait...';

            followMessage.style.display = 'none';
            followMessage.classList.remove('error');

            const actionUrl = wasFollowing
                ? 'index.php?action=unfollow_user'
                : 'index.php?action=follow_user';

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: new FormData(followForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || 'Could not update follow status.'
                    );
                }

                const isNowFollowing = result.isFollowing;

                followButton.dataset.following =
                    isNowFollowing ? 'true' : 'false';

                followButton.textContent = isNowFollowing
                    ? '✓ Following'
                    : '+ Follow';

                followButton.classList.toggle(
                    'follow-btn',
                    !isNowFollowing
                );

                followButton.classList.toggle(
                    'unfollow-btn',
                    isNowFollowing
                );

                followForm.action = isNowFollowing
                    ? 'index.php?action=unfollow_user'
                    : 'index.php?action=follow_user';

                if (followerNumber) {
                    followerNumber.textContent = result.followerCount;
                }

                followMessage.textContent = isNowFollowing
                    ? 'You are now following this user!'
                    : 'You unfollowed this user.';

                followMessage.style.display = 'block';

            } catch (error) {
                followButton.textContent = wasFollowing
                    ? '✓ Following'
                    : '+ Follow';

                followMessage.textContent =
                    error.message ||
                    'Something went wrong. Please try again.';

                followMessage.classList.add('error');
                followMessage.style.display = 'block';

            } finally {
                followButton.disabled = false;
            }
        });


        // Initialize the button state using its rendered label.
        followButton.dataset.following =
            followButton.textContent.trim() === '✓ Following'
                ? 'true'
                : 'false';
    }
    // Like/unlike posts without refreshing the profile.
    document.querySelectorAll('.ajax-like-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const button = form.querySelector('button');

            if (button.disabled) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    alert(data.message || 'Could not update your like.');
                    return;
                }

                if (data.liked) {
                    form.action = 'index.php?action=unlike_post';
                    button.classList.remove('like-btn');
                    button.classList.add('liked-btn');
                    button.innerHTML =
                        '♥ Liked (<span class="like-count">' +
                        data.likeCount + '</span>)';
                } else {
                    form.action = 'index.php?action=like_post';
                    button.classList.remove('liked-btn');
                    button.classList.add('like-btn');
                    button.innerHTML =
                        '♡ Like (<span class="like-count">' +
                        data.likeCount + '</span>)';
                }

            } catch (error) {
                alert('Could not connect to Chirply. Please try again.');
            } finally {
                button.disabled = false;
            }
        });
    });
</script>

</body>
</html>
