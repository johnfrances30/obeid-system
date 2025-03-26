<?php
include 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $saudi_id = $_POST['saudi_id'];
    $file_number = $_POST['file_number'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $birthday = $_POST['birthday'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (full_name, saudi_id, file_number, email, phone_number, birthday, password)
            VALUES ('$full_name', '$saudi_id', '$file_number', '$email', '$phone_number', '$birthday', '$password')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php");
        exit();
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/register.css">
  <title>Obeid Hospital - Registration</title>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="https://obeidhospital.sa/wp-content/uploads/2024/06/للويب.png" alt="Obeid Hospital Logo" class="logo">
      <h2>Doctor's Registration</h2>
    </div>

    <div class="form-container">
      <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="text" name="full_name" id="full_name" placeholder="Enter your full name" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="saudi_id">Saudi ID / Residence Permit</label>
            <input type="text" name="saudi_id" id="saudi_id" placeholder="ID number" required>
          </div>

          <div class="form-group">
            <label for="file_number">File Number</label>
            <input type="text" name="file_number" id="file_number" placeholder="If available" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="your.email@example.com" required>
          </div>

          <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="tel" name="phone_number" id="phone_number" placeholder="05XXXXXXXX" required>
          </div>
        </div>

        <div class="form-group">
          <label for="birthday">Date of Birth</label>
          <input type="date" name="birthday" id="birthday" required>
        </div>

        <div class="form-group">
          <label for="password">Create Password</label>
          <input type="password" name="password" id="password" placeholder="Minimum 8 characters" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter your password" required>
        </div>

        <button type="submit" class="btn">Create Account</button>
      </form>

      <div class="alternate-action">
        Already have an account? <a href="login.php">Log in</a>
      </div>
    </div>
  </div>

  <script>
    // Add simple form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.getElementById('password');
      const confirmPassword = document.getElementById('confirm_password');
      
      if (password.value !== confirmPassword.value) {
        e.preventDefault();
        confirmPassword.style.borderColor = 'var(--error-color)';
        alert('Passwords do not match');
      }
      
      if (password.value.length < 8) {
        e.preventDefault();
        password.style.borderColor = 'var(--error-color)';
        alert('Password must be at least 8 characters');
      }
    });
  </script>
</body>
</html>