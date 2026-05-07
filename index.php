<?php
session_start();

if(!isset($_SESSION['feedbacks'])){
    $_SESSION['feedbacks'] = [];
    $_SESSION['next_id'] = 1;
}

//if(isset($_POST['submit'])){

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])){

    if($_POST['action'] == 'create'){
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $rating = intval($_POST['rating']);
        $message = htmlspecialchars(trim($_POST['message']));

        // $_SESSION['feedbacks'][] = $_POST;

        if($name && $email && $rating && $message){
            $id = $_SESSION['next_id']++;
            $_SESSION['feedbacks'][$id] = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'rating' => $rating,
                'message' => $message,
                'date' => date('M d, Y')
            ];
            $success = "Thank you for your feedback!";
        } else {
            $error = "Please fill in all fields.";
        }
    }

    elseif($_POST['action'] == 'update'){
        $id = intval($_POST['id']);
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $rating = intval($_POST['rating']);
        $message = htmlspecialchars(trim($_POST['message']));

        
        if(isset($_SESSION['feedbacks'][$id])){
            $_SESSION['feedbacks'][$id]['name'] = $name;
            $_SESSION['feedbacks'][$id]['email'] = $email;
            $_SESSION['feedbacks'][$id]['rating'] = $rating;
            $_SESSION['feedbacks'][$id]['message'] = $message;
            $updated = true;
        }
    }

    elseif($_POST['action'] == 'delete'){
        $id = intval($_POST['id']);
        // array_splice($_SESSION['feedbacks'], $id, 1);
        if(isset($_SESSION['feedbacks'][$id])){
            unset($_SESSION['feedbacks'][$id]);
            $deleted = true;
        }
    }
}

$editFeedback = null;
if(isset($_GET['edit'])){
    $editId = intval($_GET['edit']);
    if(isset($_SESSION['feedbacks'][$editId])){
        $editFeedback = $_SESSION['feedbacks'][$editId];
    }
}

$allFeedbacks = array_reverse($_SESSION['feedbacks'], true);
$totalReviews = count($allFeedbacks);

$avgRating = 0;
$starCounts = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];

if($totalReviews > 0){
    $sum = 0;
    foreach($allFeedbacks as $fb){
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1 class="page-title">Feedback Form</h1>

<div class="card">
    <?php if($editFeedback): ?>
        <div class="card-title">Edit Feedback</div>

        <?php if(isset($updated)): ?>
            <div class="alert alert-success">Updated successfully!</div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editFeedback['id'] ?>">

            <label class="form-label">Name:</label>
            <input type="text" name="name" placeholder="Your full name" required value="<?= $editFeedback['name'] ?>">

            <label class="form-label">Email:</label>
            <input type="email" name="email" placeholder="your@email.com" required value="<?= $editFeedback['email'] ?>">

            <span class="star-label-top" style="margin-top:16px;">Rate Your Experience:</span>
            <div class="star-row">
                <?php for($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="es<?= $i ?>" name="rating" value="<?= $i ?>" <?= ($editFeedback['rating'] == $i) ? 'checked' : '' ?>>
                    <label for="es<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>

            <label class="form-label">Feedback Message:</label>
            <textarea name="message" placeholder="Tell us what you think..." required><?= $editFeedback['message'] ?></textarea>

            <button type="submit" class="btn-update">Update Feedback</button>
            <a href="index.php" class="btn-cancel">← Cancel</a>
        </form>

    <?php else: ?>
        <div class="card-title">Share Your Feedback</div>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif(isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="create">

            <label class="form-label">Name:</label>
            <input type="text" name="name" placeholder="Your full name" required>

            <label class="form-label">Email:</label>
            <input type="email" name="email" placeholder="your@email.com" required>

            <span class="star-label-top" style="margin-top:16px;">Rate Your Experience:</span>
            <div class="star-row">
                <?php for($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="s<?= $i ?>" name="rating" value="<?= $i ?>" required>
                    <label for="s<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>

            <label class="form-label">Feedback Message:</label>
            <textarea name="message" placeholder="Tell us what you think..." required></textarea>

            <button type="submit" class="btn-submit">Submit Feedback</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-title">Feedback Results</div>

    <?php if(isset($deleted)): ?>
        <div class="alert alert-info">Feedback deleted.</div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-number"><?= $totalReviews ?></span>
            <div class="stat-label">Total Reviews</div>
        </div>
        <div class="stat-card">
            <span class="stat-number">
                <?php if($totalReviews > 0): ?>
                    <?= $avgRating ?> <span class="star-gold">★</span>
                <?php else: ?>
                    0
                <?php endif; ?>
            </span>
            <div class="stat-label">Average Rating</div>
        </div>
    </div>

    <div class="rating-bars">
        <?php for($i = 5; $i >= 1; $i--):
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

    <div class="recent-heading">Recent Feedback</div>

    <?php if(empty($allFeedbacks)): ?>
        <div class="empty-state">No feedback yet!</div>
    <?php else: ?>
        <?php foreach($allFeedbacks as $fb): ?>
        <div class="feedback-item">
            <div class="feedback-top">
                <div class="feedback-stars"><?= str_repeat('★', $fb['rating']) . str_repeat('☆', 5 - $fb['rating']) ?></div>
                <div class="feedback-date"><?= $fb['date'] ?></div>
            </div>
            <div class="feedback-text"><?= $fb['message'] ?></div>
            <div class="feedback-author"> <?= $fb['name'] ?></div>
            <div class="feedback-actions">
                <a href="index.php?edit=<?= $fb['id'] ?>" class="btn-edit">Edit</a>
                <form method="POST" action="index.php" style="display:inline" onsubmit="return confirm('Delete this?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                    <button type="submit" class="btn-delete">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>