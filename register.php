<?php
$pageTitle = 'Register';
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param('sssss', $name, $email, $hash, $phone, $address);
            if ($ins->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Create Account</h1>
        <div class="breadcrumb"><a href="index.php">Home</a><span>/</span><span>Register</span></div>
    </div>
</div>

<div class="auth-page">
    <div class="container">
        <div class="auth-card" style="max-width:520px;">
            <div class="auth-header">
                <div class="auth-logo">🌿</div>
                <h2>Join MRM Grocery</h2>
                <p>Create your account to start shopping fresh!</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= sanitize($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= sanitize($_POST['name'] ?? '') ?>"
                               placeholder="Your full name">
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= sanitize($_POST['email'] ?? '') ?>"
                               placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control"
                               value="<?= sanitize($_POST['phone'] ?? '') ?>"
                               placeholder="0771234567">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2"
                                  placeholder="Your delivery address"><?= sanitize($_POST['address'] ?? '') ?></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
                        📝 Create Account
                    </button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
