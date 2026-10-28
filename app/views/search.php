
<?php

if (!function_exists('chirplySearchEscape')) {
    function chirplySearchEscape($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

$keyword = $keyword ?? '';
$users = $users ?? [];
$posts = $posts ?? [];
$csrfToken = $csrfToken ?? '';
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

$returnTo = 'index.php?page=search&q=' . urlencode($keyword);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Chirply</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            color-scheme: dark;
            --background: #100e17;
            --surface: #191624;
            --surface-hover: #211d30;
            --border: #302a43;
            --purple: #a78bfa;
            --purple-dark: #7c5ce0;
            --text: #f4f0ff;
            --muted: #aaa2bf;
            --danger: #ff8e9b;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.5;
        }

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 15px 20px;
        }

        .nav {
            max-width: 1050px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .brand {
            color: var(--purple);
            font-size: 25px;
            font-weight: 800;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            padding: 9px 11px;
            border-radius: 8px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #302747;
            color: var(--purple);
        }

        .nav-links form {
            margin: 0;
        }

        .container {
            max-width: 780px;
            margin: 28px auto;
            padding: 0 16px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 27px;
        }

        h2 {
            font-size: 19px;
            margin: 26px 0 13px;
        }

        h3 {
            font-size: 16px;
            margin: 0 0 5px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .search-form {
            display: flex;
            gap: 9px;
            margin-top: 18px;
        }

        input[type="search"],
        textarea {
            width: 100%;
            background: var(--background);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            font: inherit;
            outline: none;
        }

        input[type="search"]:focus,
        textarea:focus {
            border-color: var(--purple);
        }

        textarea {
            resize: vertical;
            min-height: 65px;
        }

        button,
        .button {
            display: inline-block;
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 9px 13px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            background: var(--surface-hover);
            color: var(--text);
        }

        button:hover,
        .button:hover {
            filter: brightness(1.12);
        }

        .primary {
            background: var(--purple-dark);
            color: white;
            border-color: transparent;
        }

        .like {
            color: var(--purple);
            background: #29213c;
        }

        .liked {
            background: #493477;
            color: white;
        }

        .delete {
            color: var(--danger);
            background: #3b202b;
        }

        .search-form button {
            padding: 12px 17px;
        }

        .user-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
            background: #302747;
            flex-shrink: 0;
        }

        .bio {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            margin: 7px 0 12px;
        }

        .post-content,
        .comment-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .post-content {
            line-height: 1.7;
            margin: 15px 0;
        }

        .post-image {
            max-width: 100%;
            max-height: 480px;
            border-radius: 11px;
            object-fit: contain;
            margin-top: 10px;
        }

        .post-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            margin: 14px 0;
        }

        .post-actions form {
            margin: 0;
        }

        .comments {
            border-top: 1px solid var(--border);
            margin-top: 18px;
            padding-top: 14px;
        }

        .comment {
            border-bottom: 1px solid var(--border);
            padding: 12px 0;
        }

        .comment-content {
            margin: 7px 0;
            font-size: 14px;
        }

        .comment-form {
            display: grid;
            gap: 9px;
            margin-top: 15px;
        }

        .empty {
            text-align: center;
            color: var(--muted);
            padding: 20px 5px;
        }

        .count {
            color: var(--muted);
            font-size: 14px;
        }

        /* Custom delete confirmation popup */
        .delete-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(5, 4, 12, 0.78);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .delete-modal-overlay.show {
            display: flex;
        }

        .delete-modal {
            width: 100%;
            max-width: 400px;
            padding: 25px;
            border-radius: 18px;
            border: 1px solid #493a66;
            background: #191624;
            box-shadow: 0 20px 70px rgba(0, 0, 0, 0.45);
            text-align: center;
        }

        .delete-modal-icon {
            width: 55px;
            height: 55px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #3b202b;
            color: var(--danger);
            font-size: 27px;
            font-weight: bold;
        }

        .delete-modal h2 {
            margin: 0 0 10px;
            font-size: 21px;
        }

        .delete-modal p {
            color: var(--muted);
            font-size: 14px;
            margin: 0 0 22px;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .modal-actions button {
            min-width: 110px;
            padding: 11px 16px;
        }

        .cancel-delete {
            background: #292536;
            color: var(--text);
        }

        .confirm-delete {
            background: #b83250;
            color: white;
            border-color: transparent;
        }

        @media (max-width: 600px) {
            .nav {
                align-items: flex-start;
            }

            .nav-links {
                width: 100%;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form button {
                width: 100%;
            }

            .card {
                padding: 15px;
            }

            .modal-actions button {
                flex: 1;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="nav">
        <a class="brand" href="index.php?page=home">chirply.</a>

        <nav class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=profile">My Profile</a>
            <a class="active" href="index.php?page=search">Search</a>
            <a href="index.php?page=movies">Movies</a>
            <a href="index.php?page=notifications">Notifications</a>

            <form action="index.php?action=logout" method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= chirplySearchEscape($csrfToken) ?>">
                <button type="submit">Log out</button>
            </form>
        </nav>
    </div>
</header>

<main class="container">

    <section class="card">
        <h1>Explore Chirply</h1>
        <p class="muted">
            Find people, discover conversations, and join the discussion.
        </p>

        <form class="search-form" action="index.php" method="GET">
            <input type="hidden" name="page" value="search">

            <input
                type="search"
                name="q"
                value="<?= chirplySearchEscape($keyword) ?>"
                placeholder="Search people or posts..."
                maxlength="100"
                aria-label="Search people or posts"
                required
            >

            <button class="primary" type="submit">Search</button>
        </form>
    </section>

    <?php if ($keyword === ''): ?>

        <section class="card empty">
            Enter a name, username, or keyword to find matching results.
        </section>

    <?php else: ?>

        <p class="count">
            Results for <strong><?= chirplySearchEscape($keyword) ?></strong>
        </p>

        <h2>People · <?= count($users) ?></h2>

        <?php if (empty($users)): ?>
            <section class="card empty">No matching people found.</section>
        <?php else: ?>

            <?php foreach ($users as $person): ?>
                <section class="card">
                    <div class="user-row">

                        <?php if (!empty($person['profile_image'])): ?>
                            <img
                                class="avatar"
                                src="<?= chirplySearchEscape($person['profile_image']) ?>"
                                alt="Profile picture"
                            >
                        <?php else: ?>
                            <div class="avatar"></div>
                        <?php endif; ?>

                        <div>
                            <h3>
                                <?= chirplySearchEscape(
                                    $person['full_name'] ?: $person['username']
                                ) ?>
                            </h3>

                            <div class="muted">
                                @<?= chirplySearchEscape($person['username']) ?>
                            </div>

                            <?php if (!empty($person['bio'])): ?>
                                <p class="bio">
                                    <?= chirplySearchEscape($person['bio']) ?>
                                </p>
                            <?php endif; ?>

                            <a class="button"
                               href="index.php?page=profile&id=<?= (int) $person['id'] ?>">
                                View profile
                            </a>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>

        <?php endif; ?>

        <h2>Posts · <?= count($posts) ?></h2>

        <?php if (empty($posts)): ?>
            <section class="card empty">No matching posts found.</section>
        <?php else: ?>

            <?php foreach ($posts as $post): ?>
                <?php
                $postId = (int) $post['id'];
                $postOwnerId = (int) $post['user_id'];
                $isOwner = $postOwnerId === $currentUserId;
                $userLiked = !empty($post['user_liked']);
                $likeCount = (int) ($post['like_count'] ?? 0);
                $comments = $post['comments'] ?? [];
                ?>

                <article class="card" id="post-<?= $postId ?>">

                    <h3>
                        <?= chirplySearchEscape(
                            $post['full_name'] ?: ($post['username'] ?? 'User')
                        ) ?>
                    </h3>

                    <div class="muted">
                        @<?= chirplySearchEscape($post['username'] ?? 'user') ?>
                        ·
                        <?= chirplySearchEscape($post['created_at'] ?? '') ?>
                    </div>

                    <div class="post-content">
                        <?= chirplySearchEscape($post['content'] ?? '') ?>
                    </div>

                    <?php if (!empty($post['image'])): ?>
                        <img
                            class="post-image"
                            src="<?= chirplySearchEscape($post['image']) ?>"
                            alt="Post image"
                        >
                    <?php endif; ?>

                    <div class="post-actions">
                        <form
                            action="index.php?action=<?= $userLiked ? 'unlike_post' : 'like_post' ?>"
                            method="POST">

                            <input type="hidden" name="csrf_token"
                                   value="<?= chirplySearchEscape($csrfToken) ?>">
                            <input type="hidden" name="post_id" value="<?= $postId ?>">
                            <input type="hidden" name="return_to"
                                   value="<?= chirplySearchEscape($returnTo) ?>">

                            <button class="<?= $userLiked ? 'liked' : 'like' ?>"
                                    type="submit">
                                <?= $userLiked ? '♥ Liked' : '♡ Like' ?>
                                (<?= $likeCount ?>)
                            </button>
                        </form>

                        <a class="button"
                           href="index.php?page=profile&id=<?= $postOwnerId ?>">
                            <?= $isOwner ? 'View your profile' : 'View profile' ?>
                        </a>

                        <?php if ($isOwner): ?>
                            <form action="index.php?action=delete_post"
                                  method="POST"
                                  class="js-confirm-delete"
                                  data-modal-title="Delete post?"
                                  data-modal-message="Are you sure you want to delete this post? This action cannot be undone.">

                                <input type="hidden" name="csrf_token"
                                       value="<?= chirplySearchEscape($csrfToken) ?>">
                                <input type="hidden" name="post_id"
                                       value="<?= $postId ?>">
                                <input type="hidden" name="return_to"
                                       value="<?= chirplySearchEscape($returnTo) ?>">

                                <button class="delete" type="submit">
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
                            $commentIsMine =
                                (int) ($comment['user_id'] ?? 0) === $currentUserId;
                            ?>

                            <div class="comment">
                                <strong>
                                    <?= chirplySearchEscape(
                                        $comment['full_name']
                                            ?: ($comment['username'] ?? 'User')
                                    ) ?>
                                </strong>

                                <span class="muted">
                                    @<?= chirplySearchEscape($comment['username'] ?? 'user') ?>
                                    ·
                                    <?= chirplySearchEscape($comment['created_at'] ?? '') ?>
                                </span>

                                <div class="comment-content">
                                    <?= chirplySearchEscape($comment['content'] ?? '') ?>
                                </div>

                                <?php if ($commentIsMine): ?>
                                    <form action="index.php?action=delete_comment"
                                          method="POST"
                                          class="js-confirm-delete"
                                          data-modal-title="Delete comment?"
                                          data-modal-message="Are you sure you want to delete this comment? This action cannot be undone.">

                                        <input type="hidden" name="csrf_token"
                                               value="<?= chirplySearchEscape($csrfToken) ?>">
                                        <input type="hidden" name="comment_id"
                                               value="<?= (int) $comment['id'] ?>">
                                        <input type="hidden" name="return_to"
                                               value="<?= chirplySearchEscape($returnTo) ?>">

                                        <button class="delete" type="submit">
                                            Delete comment
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <form class="comment-form"
                              action="index.php?action=create_comment"
                              method="POST">

                            <input type="hidden" name="csrf_token"
                                   value="<?= chirplySearchEscape($csrfToken) ?>">
                            <input type="hidden" name="post_id" value="<?= $postId ?>">
                            <input type="hidden" name="return_to"
                                   value="<?= chirplySearchEscape($returnTo) ?>">

                            <textarea
                                name="content"
                                maxlength="2000"
                                placeholder="Write a comment..."
                                required
                            ></textarea>

                            <button class="primary" type="submit">
                                Add comment
                            </button>
                        </form>
                    </section>

                </article>
            <?php endforeach; ?>

        <?php endif; ?>

    <?php endif; ?>

</main>

<!-- Custom delete confirmation popup -->
<div class="delete-modal-overlay" id="deleteModal"
     role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

    <div class="delete-modal">
        <div class="delete-modal-icon">!</div>

        <h2 id="deleteModalTitle">Confirm deletion</h2>

        <p id="deleteModalMessage">
            Are you sure you want to delete this item?
        </p>

        <div class="modal-actions">
            <button type="button" class="cancel-delete" id="cancelDelete">
                Cancel
            </button>

            <button type="button" class="confirm-delete" id="confirmDelete">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('deleteModal');
    const deleteModalTitle = document.getElementById('deleteModalTitle');
    const deleteModalMessage = document.getElementById('deleteModalMessage');
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');

    let selectedDeleteForm = null;

    document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            selectedDeleteForm = form;

            deleteModalTitle.textContent =
                form.dataset.modalTitle || 'Confirm deletion';

            deleteModalMessage.textContent =
                form.dataset.modalMessage ||
                'Are you sure you want to delete this item?';

            deleteModal.classList.add('show');
            cancelDelete.focus();
        });
    });

    function closeDeleteModal() {
        deleteModal.classList.remove('show');
        selectedDeleteForm = null;
    }

    cancelDelete.addEventListener('click', function () {
        closeDeleteModal();
    });

    confirmDelete.addEventListener('click', function () {
        if (selectedDeleteForm) {
            const formToSubmit = selectedDeleteForm;

            closeDeleteModal();

            // Submit the original form, including its CSRF token.
            formToSubmit.submit();
        }
    });

    deleteModal.addEventListener('click', function (event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && deleteModal.classList.contains('show')) {
            closeDeleteModal();
        }
    });
</script>

</body>
</html>
