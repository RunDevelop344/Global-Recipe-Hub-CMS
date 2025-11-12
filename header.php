
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Recipe Hub</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<header>
    <h1> Global Recipe Hub</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="create.php">Add Recipe</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
<hr>