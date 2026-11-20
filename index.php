<?php
declare(strict_types=1);
session_start();

$config = require __DIR__ . '/config.php';
$dbDir = __DIR__ . '/data';
$dbFile = $dbDir . '/messages.sqlite';
$success = '';
$error = '';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Your form session expired. Refresh the page and try again.';
    } else {
        $senderName = trim((string)($_POST['sender_name'] ?? ''));
        $senderEmail = trim((string)($_POST['sender_email'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($senderName === '' || $senderEmail === '' || $message === '') {
            $error = 'Please complete all fields.';
        } elseif (mb_strlen($senderName) > 100 || mb_strlen($senderEmail) > 254 || mb_strlen($message) > 5000) {
            $error = 'One or more fields are too long.';
        } elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                if (!is_dir($dbDir) && !mkdir($dbDir, 0750, true) && !is_dir($dbDir)) {
                    throw new RuntimeException('Could not create the database directory.');
                }
                $pdo = new PDO('sqlite:' . $dbFile, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS messages (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        sender_name TEXT NOT NULL,
                        sender_email TEXT NOT NULL,
                        message TEXT NOT NULL,
                        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                    )'
                );
                $stmt = $pdo->prepare(
                    'INSERT INTO messages (sender_name, sender_email, message)
                     VALUES (:name, :email, :message)'
                );
                $stmt->execute([
                    ':name' => $senderName,
                    ':email' => $senderEmail,
                    ':message' => $message,
                ]);
                $success = 'Your message has been saved successfully. Thank you for reaching out.';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (Throwable $e) {
                error_log('Portfolio contact form error: ' . $e->getMessage());
                $error = 'The message could not be saved right now. Please try again later.';
            }
        }
    }
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($config['name']) ?>'s professional portfolio">
    <title><?= e($config['name']) ?> | Portfolio</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="#home"><?= e($config['name']) ?><span>.</span></a>
    <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>
    <nav class="nav-links" aria-label="Main navigation">
        <a href="#about">About</a><a href="#work">Work</a><a href="#contact">Contact</a>
    </nav>
</header>

<main>
    <section class="hero section" id="home">
        <p class="eyebrow">PORTFOLIO / <?= e(strtoupper($config['location'])) ?></p>
        <h1>Ideas into<br><span>meaningful work.</span></h1>
        <p class="lead"><?= e($config['intro']) ?></p>
        <div class="hero-actions">
            <a class="button primary" href="#work">Explore my work</a>
            <a class="text-link" href="#contact">Let's talk about work ↗</a>
        </div>
        <div class="hero-foot"><span>AVAILABLE FOR OPPORTUNITIES</span><span>SCROLL TO EXPLORE ↓</span></div>
    </section>

    <section class="section about" id="about">
        <p class="eyebrow">01 / ABOUT</p>
        <div class="section-grid">
            <h2>A little<br>about me.</h2>
            <div>
                <p class="body-copy">I'm <?= e($config['name']) ?>, based in <?= e($config['location']) ?>. This portfolio is a place to share my work, interests, and professional journey.</p>
                <p class="muted">Update this introduction with accurate details about Roza's background and goals.</p>
                <h3>Skills & strengths</h3>
                <div class="chips">
                    <?php foreach ($config['skills'] as $skill): ?>
                        <span class="chip"><?= e($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section work" id="work">
        <p class="eyebrow">02 / SELECTED WORK</p>
        <div class="work-heading"><h2>Things I've<br>worked on.</h2><p class="muted">Replace sample project cards with real work.</p></div>
        <div class="project-list">
            <?php foreach ($config['projects'] as $project): ?>
                <article class="project">
                    <span class="project-number"><?= e($project['number']) ?></span>
                    <div><p class="eyebrow"><?= e($project['type']) ?></p><h3><?= e($project['title']) ?></h3><p class="muted"><?= e($project['description']) ?></p></div>
                    <span class="project-arrow">↗</span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section contact" id="contact">
        <p class="eyebrow">03 / CONTACT</p>
        <div class="section-grid">
            <div><h2>Let's talk<br>about work.</h2><p class="muted">Have a question, an idea, or an opportunity? Send a message using the form.</p><a class="email-link" href="mailto:<?= e($config['email']) ?>"><?= e($config['email']) ?></a></div>
            <div class="form-wrap">
                <?php if ($success !== ''): ?><p class="notice success" role="status"><?= e($success) ?></p><?php endif; ?>
                <?php if ($error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
                <form method="post" action="#contact">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <label for="sender_name">Your name</label>
                    <input id="sender_name" name="sender_name" maxlength="100" required>
                    <label for="sender_email">Your email</label>
                    <input id="sender_email" name="sender_email" type="email" maxlength="254" required>
                    <label for="message">Your message</label>
                    <textarea id="message" name="message" rows="5" maxlength="5000" required></textarea>
                    <button class="button primary" type="submit">Send message ↗</button>
                </form>
                <p class="form-note">Messages are stored in a private SQLite database on the hosting server. This form does not send an email.</p>
            </div>
        </div>
    </section>
</main>

<footer class="footer"><span>© <?= date('Y') ?> <?= e($config['name']) ?></span><a href="#home">Back to top ↑</a></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
