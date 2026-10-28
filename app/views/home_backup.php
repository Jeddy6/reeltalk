
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

$user = $user ?? [];
$csrfToken = $csrfToken ?? '';
$posts = $posts ?? [];

$flashMessage = $_SESSION['flash_message'] ?? '';
$flashPostId = (int) ($_SESSION['flash_post_id'] ?? 0);

unset($_SESSION['flash_message'], $_SESSION['flash_post_id']);

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chirply - Home</title>

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

        body.modal-open {
            overflow: hidden;
        }

        a {
            color: var(--purple);
        }

        header {
            background: rgba(25, 22, 36, 0.96);
            border-bottom: 1px solid var(--border);
            padding: 14px 22px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav {
            max-width: 1050px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 26px;
            font-weight: 800;
            color: var(--purple);
            text-decoration: none;
            letter-spacing: -1px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex: 1;
            flex-wrap: wrap;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: 14px;
            transition: 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #302747;
            color: var(--purple);
        }

        .nav-search {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .nav-search input {
            width: 190px;
            padding: 10px 12px;
            background: var(--background);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 20px;
            outline: none;
        }

        .nav-search input:focus {
            border-color: var(--purple);
        }

        button {
            font-family: inherit;
        }

        .nav-search button,
        .logout-btn {
            border: none;
            border-radius: 9px;
            padding: 10px 13px;
            cursor: pointer;
            font-weight: bold;
        }

        .nav-search button {
            background: var(--purple-dark);
            color: white;
        }

        .logout-btn {
            background: #302438;
            color: #ffb6c0;
        }

        .container {
            width: 100%;
            max-width: 760px;
            margin: 30px auto;
            padding: 0 16px;
        }

        .welcome {
            margin-bottom: 22px;
        }

        .welcome h1 {
            font-size: 28px;
            margin: 0 0 5px;
            letter-spacing: -0.6px;
        }

        .welcome p {
            margin: 0;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 21px;
            margin-bottom: 18px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .composer-title {
            font-size: 17px;
            margin: 0 0 14px;
        }

        textarea,
        input[type="text"],
        input[type="search"] {
            width: 100%;
            padding: 13px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--background);
            color: var(--text);
            font-family: inherit;
            font-size: 15px;
            outline: none;
        }

        textarea {
            min-height: 105px;
            resize: vertical;
            line-height: 1.5;
        }

        textarea:focus,
        input[type="text"]:focus,
        input[type="search"]:focus {
            border-color: var(--purple);
        }

        textarea::placeholder,
        input::placeholder {
            color: #817993;
        }

        input[type="file"] {
            display: block;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 8px;
            color: var(--text);
            font-family: inherit;
        }

        .media-help {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
            margin-bottom: 12px;
        }

        .primary-btn,
        .small-btn {
            border: 1px solid transparent;
            border-radius: 9px;
            padding: 9px 14px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: 0.2s;
        }

        .primary-btn {
            background: var(--purple-dark);
            color: white;
            margin-top: 12px;
        }

        .primary-btn:hover,
        .nav-search button:hover {
            background: #916ff0;
        }

        .small-btn {
            background: var(--surface-hover);
            border-color: var(--border);
            color: var(--text);
        }

        .small-btn:hover {
            filter: brightness(1.2);
        }

        .like-btn {
            color: var(--purple);
            background: #29213c;
            border-color: #44345f;
        }

        .liked-btn {
            background: #493477;
            color: #f1e8ff;
        }

        .delete-btn {
            background: #3b202b;
            color: var(--danger);
            border-color: #59303d;
        }

        .flash {
            padding: 11px 13px;
            background: #193628;
            color: #a7f3c3;
            border: 1px solid #28533b;
            border-radius: 10px;
            margin: 12px 0;
            font-size: 14px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 28px 2px 15px;
        }

        .section-heading h2 {
            font-size: 20px;
            margin: 0;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .post-author {
            color: var(--text);
            font-weight: bold;
            font-size: 15px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .post-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.7;
            margin: 16px 0;
        }

        .post-image,
        .post-video {
            display: block;
            width: 100%;
            max-width: 100%;
            max-height: 520px;
            object-fit: contain;
            border-radius: 12px;
            margin-top: 12px;
            background: #120e1c;
        }

        .post-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin: 14px 0;
        }

        .post-actions form,
        .nav form,
        .inline-form {
            display: inline;
            margin: 0;
        }

        .comments {
            border-top: 1px solid var(--border);
            margin-top: 18px;
            padding-top: 15px;
        }

        .comments h3 {
            font-size: 15px;
            margin: 0 0 10px;
        }

        .comment {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .comment-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            margin: 7px 0;
            font-size: 14px;
        }

        .comment-form {
            margin-top: 15px;
        }

        .comment-form textarea,
        .inline-form textarea {
            min-height: 70px;
        }

        .empty {
            text-align: center;
            color: var(--muted);
            padding: 28px 10px;
        }

        .empty strong {
            display: block;
            color: var(--text);
            font-size: 16px;
            margin-bottom: 5px;
        }

        /* Delete confirmation modal */

        .delete-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.72);
            backdrop-filter: blur(5px);
        }

        .delete-modal-overlay.show {
            display: flex;
            animation: overlayFade 0.18s ease;
        }

        .delete-modal {
            width: 100%;
            max-width: 420px;
            background: #191624;
            border: 1px solid #493b64;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 20px 70px rgba(0, 0, 0, 0.5);
            animation: modalAppear 0.2s ease;
        }

        .delete-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .delete-modal-icon {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #302747;
            color: #c4b5fd;
            border-radius: 13px;
            font-size: 22px;
        }

        .delete-modal-title {
            flex: 1;
            margin: 0;
            font-size: 21px;
            color: #f4f0ff;
        }

        .delete-modal-close {
            border: none;
            background: transparent;
            color: #aaa2bf;
            font-size: 25px;
            cursor: pointer;
            padding: 3px 7px;
            border-radius: 7px;
        }

        .delete-modal-close:hover {
            background: #302747;
            color: white;
        }

        .delete-modal-message {
            color: #c7c0d8;
            font-size: 14px;
            line-height: 1.7;
            margin: 0 0 24px;
        }

        .delete-modal-actions {
            display: flex;
            gap: 10px;
        }

        .modal-action-btn {
            flex: 1;
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .modal-action-btn:active {
            transform: scale(0.98);
        }

        .modal-cancel-btn {
            background: #302747;
            color: #f4f0ff;
        }

        .modal-cancel-btn:hover {
            background: #403457;
        }

        .modal-delete-btn {
            background: #b93856;
            color: white;
        }

        .modal-delete-btn:hover {
            background: #d14363;
        }

        @keyframes overlayFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: translateY(8px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 700px) {
            header {
                padding: 13px 15px;
            }

            .nav {
                align-items: flex-start;
            }

            .nav-right {
                width: 100%;
                justify-content: flex-start;
            }

            .nav-search {
                width: 100%;
            }

            .nav-search input {
                width: 100%;
                min-width: 0;
            }

            .container {
                margin-top: 22px;
            }
        }

        @media (max-width: 450px) {
            .card {
                padding: 15px;
            }

            .brand {
                font-size: 24px;
            }

            .welcome h1 {
                font-size: 24px;
            }

            .nav-links a {
                padding: 8px;
            }

            .delete-modal {
                padding: 20px;
            }

            .delete-modal-title {
                font-size: 19px;
            }

            .delete-modal-actions {
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
        <a class="brand" href="index.php?page=home">chirply.</a>

        <div class="nav-right">
            <nav class="nav-links">
                <a class="active" href="index.php?page=home">Home</a>
                <a href="index.php?page=profile">My Profile</a>
                <a href="index.php?page=movies">Movies</a>
            </nav>

            <form class="nav-search" action="index.php" method="GET">
                <input type="hidden" name="page" value="search">

                <input
                    type="search"
                    name="q"
                    placeholder="Search people or posts..."
                    aria-label="Search people or posts"
                    maxlength="100"
                    required
                >

                <button type="submit">Search</button>
            </form>

            <form action="index.php?action=logout" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= chirplyEscape($csrfToken) ?>"
                >

                <button class="logout-btn" type="submit">Log out</button>
            </form>
        </div>
    </div>
</header>

<main class="container">

    <section class="welcome">
        <h1>
            Welcome back,
            <?= chirplyEscape($user['full_name'] ?? $user['username'] ?? 'User') ?>!
        </h1>

        <p class="muted">
            Your little corner of the internet. What's on your mind today?
        </p>
    </section>

    <!-- Create a post with optional photo or video -->
    <section class="card">
        <h2 class="composer-title">Create a post</h2>

        <form
            action="index.php?action=create_post"
            method="POST"
            enctype="multipart/form-data"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= chirplyEscape($csrfToken) ?>"
            >

            <textarea
                name="content"
                maxlength="5000"
                placeholder="Share something with the Chirply community..."
            ></textarea>

            <label for="media_file">
                Attach a photo or video (optional)
            </label>

            <input
                type="file"
                id="media_file"
                name="media_file"
                accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,image/jpeg,image/png,image/webp,video/mp4,video/webm"
            >

            <p class="media-help">
                JPG, PNG, WebP (maximum 5 MB) or MP4, WebM (maximum 25 MB).
            </p>

            <button class="primary-btn" type="submit">
                Publish post
            </button>
        </form>
    </section>

    <div class="section-heading">
        <h2>Latest posts</h2>
    </div>

    <?php if ($flashMessage !== '' && $flashPostId === 0): ?>
        <div class="flash" role="status">
            <?= chirplyEscape($flashMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <section class="card empty">
            <strong>Your feed is waiting for its first post.</strong>

            Share something to get the conversation started!

            <?php if ($flashMessage !== '' && $flashPostId > 0): ?>
                <div class="flash" role="status">
                    <?= chirplyEscape($flashMessage) ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <?php
        $postId = (int) $post['id'];
        $postUserId = (int) $post['user_id'];
        $isOwner = $postUserId === $currentUserId;
        $userLiked = !empty($post['user_liked']);
        $likeCount = (int) ($post['like_count'] ?? 0);
        $comments = $post['comments'] ?? [];
        ?>

        <article class="card" id="post-<?= $postId ?>">

            <?php if ($flashMessage !== '' && $flashPostId === $postId): ?>
                <div class="flash" role="status">
                    <?= chirplyEscape($flashMessage) ?>
                </div>
            <?php endif; ?>

            <div class="post-header">
                <div>
                    <div class="post-author">
                        <?= chirplyEscape(
                            $post['full_name']
                            ?: ($post['username'] ?? 'User')
                        ) ?>
                    </div>

                    <div class="muted">
                        @<?= chirplyEscape($post['username'] ?? 'user') ?>
                        ·
                        <?= chirplyEscape($post['created_at'] ?? '') ?>
                    </div>
                </div>
            </div>

            <?php if (trim($post['content'] ?? '') !== ''): ?>
                <div class="post-content"><?= nl2br(
                    chirplyEscape($post['content'])
                ) ?></div>
            <?php endif; ?>

            <?php if (!empty($post['image'])): ?>
                <img
                    class="post-image"
                    src="<?= chirplyEscape($post['image']) ?>"
                    alt="Photo attached to post"
                    loading="lazy"
                >
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
                    action="index.php?action=<?= $userLiked
                        ? 'unlike_post'
                        : 'like_post' ?>"
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
                        class="small-btn <?= $userLiked
                            ? 'liked-btn'
                            : 'like-btn' ?>"
                        type="submit"
                    >
                        <?= $userLiked ? '♥ Liked' : '♡ Like' ?>
                        (<?= $likeCount ?>)
                    </button>
                </form>

                <?php if ($isOwner): ?>

                    <button
                        class="small-btn"
                        type="button"
                        onclick="document.getElementById('edit-post-<?= $postId ?>').hidden = !document.getElementById('edit-post-<?= $postId ?>').hidden"
                    >
                        Edit
                    </button>

                    <form
                        class="inline-form js-confirm-delete"
                        action="index.php?action=delete_post"
                        method="POST"
                        data-modal-title="Delete post?"
                        data-modal-message="Are you sure you want to delete this post? Once deleted, it may not be recoverable."
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
                            class="small-btn delete-btn"
                            type="submit"
                        >
                            Delete
                        </button>
                    </form>

                <?php endif; ?>

            </div>

            <?php if ($isOwner): ?>
                <form
                    id="edit-post-<?= $postId ?>"
                    action="index.php?action=update_post"
                    method="POST"
                    hidden
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

                    <textarea
                        name="content"
                        maxlength="5000"
                        required
                    ><?= chirplyEscape($post['content'] ?? '') ?></textarea>

                    <button class="primary-btn" type="submit">
                        Save changes
                    </button>
                </form>
            <?php endif; ?>

            <section class="comments">
                <h3>Comments (<?= count($comments) ?>)</h3>

                <?php if (empty($comments)): ?>
                    <p class="muted">
                        No comments yet. Start the conversation!
                    </p>
                <?php endif; ?>

                <?php foreach ($comments as $comment): ?>
                    <?php
                    $commentOwner =
                        (int) $comment['user_id'] === $currentUserId;
                    ?>

                    <div class="comment">
                        <strong>
                            <?= chirplyEscape(
                                $comment['full_name']
                                ?: ($comment['username'] ?? 'User')
                            ) ?>
                        </strong>

                        <span class="muted">
                            @<?= chirplyEscape(
                                $comment['username'] ?? 'user'
                            ) ?>
                            ·
                            <?= chirplyEscape(
                                $comment['created_at'] ?? ''
                            ) ?>
                        </span>

                        <div class="comment-content"><?= chirplyEscape(
                            $comment['content'] ?? ''
                        ) ?></div>

                        <?php if ($commentOwner): ?>
                            <form
                                class="inline-form js-confirm-delete"
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

                                <button
                                    class="small-btn delete-btn"
                                    type="submit"
                                >
                                    Delete comment
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <form
                    class="comment-form"
                    action="index.php?action=create_comment"
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

                    <textarea
                        name="content"
                        maxlength="2000"
                        placeholder="Write a comment..."
                        required
                    ></textarea>

                    <button class="primary-btn" type="submit">
                        Comment
                    </button>
                </form>
            </section>

        </article>
    <?php endforeach; ?>

</main>

<!-- Delete confirmation modal -->
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
        <div class="delete-modal-header">
            <div class="delete-modal-icon" aria-hidden="true">🗑</div>

            <h2 class="delete-modal-title" id="deleteModalTitle">
                Delete post?
            </h2>

            <button
                class="delete-modal-close"
                id="closeDeleteModal"
                type="button"
                aria-label="Close confirmation"
            >&times;</button>
        </div>

        <p class="delete-modal-message" id="deleteModalMessage">
            Are you sure you want to delete this item?
        </p>

        <div class="delete-modal-actions">
            <button
                class="modal-action-btn modal-cancel-btn"
                id="cancelDelete"
                type="button"
            >
                Cancel
            </button>

            <button
                class="modal-action-btn modal-delete-btn"
                id="confirmDelete"
                type="button"
            >
                Delete
            </button>
        </div>
    </section>
</div>

<script>
    const deleteModal = document.getElementById('deleteModal');
    const deleteModalTitle = document.getElementById('deleteModalTitle');
    const deleteModalMessage = document.getElementById('deleteModalMessage');
    const cancelDeleteButton = document.getElementById('cancelDelete');
    const closeDeleteButton = document.getElementById('closeDeleteModal');
    const confirmDeleteButton = document.getElementById('confirmDelete');

    let formToDelete = null;
    let previouslyFocusedElement = null;

    function openDeleteModal(form) {
        formToDelete = form;
        previouslyFocusedElement = document.activeElement;

        deleteModalTitle.textContent =
            form.dataset.modalTitle || 'Confirm deletion';

        deleteModalMessage.textContent =
            form.dataset.modalMessage ||
            'Are you sure you want to delete this item?';

        deleteModal.classList.add('show');
        deleteModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        cancelDeleteButton.focus();
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

    cancelDeleteButton.addEventListener('click', closeDeleteModal);
    closeDeleteButton.addEventListener('click', closeDeleteModal);

    confirmDeleteButton.addEventListener('click', function () {
        if (formToDelete) {
            const form = formToDelete;

            deleteModal.classList.remove('show');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');

            formToDelete = null;

            form.submit();
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
</script>

</body>
</html>
