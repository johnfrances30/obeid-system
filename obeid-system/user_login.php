<?php
session_start();
include 'config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_type = $_POST['login_type'];
    $id = $_POST['id'];
    $password = $_POST['password'];

    if ($login_type == "file") {
        $sql = "SELECT * FROM users WHERE file_number='$id'";
    } else {
        $sql = "SELECT * FROM users WHERE saudi_id='$id'";
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: user.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/login.css">
  <title>Obeid Hospital Portal</title>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="https://obeidhospital.sa/wp-content/uploads/2024/06/للويب.png" alt="Obeid Hospital Logo" class="logo">
      <h2>Patient Portal</h2>
    </div>

    <div class="form-container">
      <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="login_type">Login Method</label>
          <select name="login_type" id="login_type" required>
            <option value="file">File Number</option>
            <option value="saudi_id">Saudi ID/Residence Permit</option>
          </select>
        </div>

        <div class="form-group">
          <label for="id">ID Number</label>
          <input type="text" name="id" id="id" placeholder="Enter your ID number" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn">Log In</button>
      </form>

      <div class="alternate-action">
        New Patient? <a href="user_register.php">Create an account</a>
      </div>
    </div>
  </div>

  <script>
    // form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const idInput = document.getElementById('id');
      const passwordInput = document.getElementById('password');
      
      if (idInput.value.trim() === '') {
        e.preventDefault();
        idInput.style.borderColor = 'var(--error-color)';
      }
      
      if (passwordInput.value.trim() === '') {
        e.preventDefault();
        passwordInput.style.borderColor = 'var(--error-color)';
      }
    });
  </script>
</body>
</html>