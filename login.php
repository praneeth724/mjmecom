<?php
$pageTitle = 'Login';
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $safeRedirect = filter_var($redirect, FILTER_VALIDATE_URL) ? 'index.php' : $redirect;
            header('Location: ' . $safeRedirect);
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Login</h1>
        <div class="breadcrumb"><a href="index.php">Home</a><span>/</span><span>Login</span></div>
    </div>
</div>

<div class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">🛒</div>
                <h2>Welcome Back!</h2>
                <p>Sign in to your MRM Grocery account</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= sanitize($error) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">✅ Registration successful! Please login.</div>
                <?php endif; ?>

                <form method="POST" action="login.php?redirect=<?= urlencode($redirect) ?>">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= sanitize($_POST['email'] ?? '') ?>"
                               placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Your password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
                        🔐 Login
                    </button>
                </form>

                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
