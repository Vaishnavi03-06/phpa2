<?php


// Starting the session to store feedback data
session_start();

// Initializing feedback storage in session 
if (!isset($_SESSION['feedbacks'])) {
    $_SESSION['feedbacks'] = [];
    $_SESSION['next_id'] = 1;
}


// CREATE: Handle new feedback submission

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create') {
        $name    = htmlspecialchars(trim($_POST['name']));
        $email   = htmlspecialchars(trim($_POST['email']));
        $rating  = intval($_POST['rating']);
        $message = htmlspecialchars(trim($_POST['message']));

        if ($name && $email && $rating && $message) {
            $id = $_SESSION['next_id']++;
            $_SESSION['feedbacks'][$id] = [
                'id'      => $id,
                'name'    => $name,
                'email'   => $email,
                'rating'  => $rating,
                'message' => $message,
                'date'    => date('M d, Y')
            ];
            $success = "Thank you for your feedback!";
        } else {
            $error = "Please fill in all fields.";
        }
    }

    
    // UPDATE: Handle feedback edit submission
    
    elseif ($_POST['action'] === 'update') {
        $id      = intval($_POST['id']);
        $name    = htmlspecialchars(trim($_POST['name']));
        $email   = htmlspecialchars(trim($_POST['email']));
        $rating  = intval($_POST['rating']);
        $message = htmlspecialchars(trim($_POST['message']));

        if (isset($_SESSION['feedbacks'][$id]) && $rating && $message) {
            $_SESSION['feedbacks'][$id]['name'] = $name;
            $_SESSION['feedbacks'][$id]['email']   = $email;
            $_SESSION['feedbacks'][$id]['rating']  = $rating;
            $_SESSION['feedbacks'][$id]['message'] = $message;
            $updated = true;
        }
    }

    
    // DELETE: Handle feedback deletion
    
    elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        if (isset($_SESSION['feedbacks'][$id])) {
            unset($_SESSION['feedbacks'][$id]);
            $deleted = true;
        }
    }
}

// Fetch edit target if editing
$editFeedback = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if (isset($_SESSION['feedbacks'][$editId])) {
        $editFeedback = $_SESSION['feedbacks'][$editId];
    }
}

// READ: Get all feedbacks (newest first)
$allFeedbacks = array_reverse($_SESSION['feedbacks'], true);
$totalReviews = count($allFeedbacks);

// Calculate average rating and per-star counts
$avgRating  = 0;
$starCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
if ($totalReviews > 0) {
    $sum = 0;
    foreach ($allFeedbacks as $fb) {
        $sum += $fb['rating'];
        $starCounts[$fb['rating']]++;
    }
    $avgRating = round($sum / $totalReviews, 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #6b73c1 0%, #7b5ea7 50%, #8b4a9c 100%);
            padding: 40px 20px;
        }

        /* Page title */
        .page-title {
            text-align: center;
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 28px;
        }

        /*White cards */
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            max-width: 600px;
            margin: 0 auto 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 22px;
        }

        /* Form labels */
        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
            margin-top: 16px;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #333;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            outline: none;
            border-color: #7b5ea7;
            box-shadow: 0 0 0 3px rgba(123,94,167,0.12);
        }
        textarea { resize: vertical; min-height: 120px; }

        /*  Star rating*/
        .star-label-top {
            font-size: 0.9rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 10px;
            display: block;
        }
        .star-row {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 4px;
            margin-bottom: 18px;
        }
        .star-row input[type="radio"] { display: none; }
        .star-row label {
            font-size: 2.2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.15s, transform 0.1s;
        }
        .star-row label:hover,
        .star-row label:hover ~ label,
        .star-row input:checked ~ label {
            color: #f5a623;
        }
        .star-row label:hover { transform: scale(1.15); }

        /* Submit / Update buttons */
        .btn-submit,
        .btn-update {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6b73c1, #8b4a9c);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-submit:hover,
        .btn-update:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #888;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .btn-cancel:hover { color: #7b5ea7; }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .alert-success { background: #eafaf1; border: 1px solid #a9dfbf; color: #27ae60; }
        .alert-error   { background: #fdf2f2; border: 1px solid #f5b7b1; color: #c0392b; }
        .alert-info    { background: #f0eeff; border: 1px solid #c4b5f4; color: #5b3fa6; }

        /*Stats row*/
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            background: linear-gradient(135deg, #6b73c1, #7b5ea7);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            display: block;
            line-height: 1.2;
        }
        .stat-number .star-gold { color: #f5a623; }
        .stat-label {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
            margin-top: 6px;
        }

        /*  Rating bars */
        .rating-bars { margin-bottom: 24px; }
        .bar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 9px;
        }
        .bar-label {
            font-size: 0.82rem;
            color: #7b5ea7;
            font-weight: 600;
            min-width: 30px;
        }
        .bar-track {
            flex: 1;
            background: #f0eeff;
            border-radius: 20px;
            height: 10px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #6b73c1, #8b4a9c);
            border-radius: 20px;
        }
        .bar-count {
            font-size: 0.82rem;
            color: #aaa;
            min-width: 14px;
            text-align: right;
        }

        /* Recent feedback heading */
        .recent-heading {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a2e;
            padding-top: 18px;
            border-top: 1px solid #f0f0f0;
            margin-bottom: 16px;
        }

        /* Feedback cards */
        .feedback-item {
            border-left: 3px solid #7b5ea7;
            padding: 14px 16px;
            margin-bottom: 14px;
            background: #fafafa;
            border-radius: 0 10px 10px 0;
        }
        .feedback-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .feedback-stars { color: #f5a623; font-size: 0.95rem; letter-spacing: 1px; }
        .feedback-date  { font-size: 0.78rem; color: #bbb; }
        .feedback-text  { font-size: 0.9rem; color: #333; line-height: 1.6; margin-bottom: 6px; }
        .feedback-author { font-size: 0.82rem; color: #999; font-style: italic; margin-bottom: 8px; }
        .feedback-actions { display: flex; gap: 8px; }

        .btn-edit {
            padding: 4px 14px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #7b5ea7;
            color: #7b5ea7;
            background: transparent;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-edit:hover { background: #7b5ea7; color: #fff; }

        .btn-delete {
            padding: 4px 14px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #e74c3c;
            color: #e74c3c;
            background: transparent;
            transition: all 0.15s;
        }
        .btn-delete:hover { background: #e74c3c; color: #fff; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 36px 20px;
            color: #bbb;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
            .page-title { font-size: 1.5rem; }
            .card { padding: 22px; }
        }
    </style>
</head>
<body>

<h1 class="page-title">Feedback Form</h1>

<!--  FORM CARD: Create / Edit -->
<div class="card">

    <?php if ($editFeedback): ?>
        <!-- EDIT MODE -->
        <div class="card-title">Edit Feedback #<?= $editFeedback['id'] ?></div>

        <?php if (isset($updated)): ?>
            <div class="alert alert-success">✔ Updated successfully!</div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id"     value="<?= $editFeedback['id'] ?>">

            <label class="form-label">Name:</label>
            <input type="text" name="name" placeholder="Your full name" required
                   value="<?= $editFeedback['name'] ?>">

            <label class="form-label">Email:</label>
            <input type="email" name="email" placeholder="your@email.com" required
                   value="<?= $editFeedback['email'] ?>">

            <span class="star-label-top" style="margin-top:16px;">Rate Your Experience:</span>
            <div class="star-row">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="es<?= $i ?>" name="rating" value="<?= $i ?>"
                        <?= ($editFeedback['rating'] == $i) ? 'checked' : '' ?>>
                    <label for="es<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>

            <label class="form-label">Your Feedback:</label>
            <textarea name="message" placeholder="Tell us what you think..." required><?= $editFeedback['message'] ?></textarea>

            <button type="submit" class="btn-update">Update Feedback</button>
            <a href="index.php" class="btn-cancel">← Cancel</a>
        </form>

    <?php else: ?>
        <!-- CREATE MODE -->
        <div class="card-title">Share Your Feedback</div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">🎉 <?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-error">⚠ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="create">

            <label class="form-label">Name:</label>
            <input type="text" name="name" placeholder="Your full name" required>

            <label class="form-label">Email:</label>
            <input type="email" name="email" placeholder="your@email.com" required>

            <span class="star-label-top" style="margin-top:16px;">Rate Your Experience:</span>
            <div class="star-row">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="s<?= $i ?>" name="rating" value="<?= $i ?>" required>
                    <label for="s<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>

            <label class="form-label">Your Feedback:</label>
            <textarea name="message" placeholder="Tell us what you think..." required></textarea>

            <button type="submit" class="btn-submit">Submit Feedback</button>
        </form>

    <?php endif; ?>
</div>

<!--  RESULTS CARD: Stats + Read -->
<div class="card">
    <div class="card-title">Feedback Results</div>

    <?php if (isset($deleted)): ?>
        <div class="alert alert-info">🗑 Feedback deleted.</div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-number"><?= $totalReviews ?></span>
            <div class="stat-label">Total Reviews</div>
        </div>
        <div class="stat-card">
            <span class="stat-number">
                <?php if ($totalReviews > 0): ?>
                    <?= $avgRating ?> <span class="star-gold">★</span>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </span>
            <div class="stat-label">Average Rating</div>
        </div>
    </div>

    <!-- Rating breakdown bars -->
    <div class="rating-bars">
        <?php for ($i = 5; $i >= 1; $i--):
            $pct = $totalReviews > 0 ? round($starCounts[$i] / $totalReviews * 100) : 0;
        ?>
        <div class="bar-row">
            <span class="bar-label"><?= $i ?> ★</span>
            <div class="bar-track">
                <div class="bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <span class="bar-count"><?= $starCounts[$i] ?></span>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Recent feedback list -->
    <div class="recent-heading">Recent Feedback</div>

    <?php if (empty($allFeedbacks)): ?>
        <div class="empty-state">No feedback yet. Be the first to share!</div>
    <?php else: ?>
        <?php foreach ($allFeedbacks as $fb): ?>
        <div class="feedback-item">
            <div class="feedback-top">
                <div class="feedback-stars"><?= str_repeat('★', $fb['rating']) . str_repeat('☆', 5 - $fb['rating']) ?></div>
                <div class="feedback-date"><?= $fb['date'] ?></div>
            </div>
            <div class="feedback-text"><?= $fb['message'] ?></div>
            <div class="feedback-author"> <?= $fb['name'] ?></div>
            <div class="feedback-actions">
                <!-- EDIT -->
                <a href="index.php?edit=<?= $fb['id'] ?>" class="btn-edit">✎ Edit</a>
                <!-- DELETE -->
                <form method="POST" action="index.php" style="display:inline"
                      onsubmit="return confirm('Delete this feedback?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id"     value="<?= $fb['id'] ?>">
                    <button type="submit" class="btn-delete">✕ Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>