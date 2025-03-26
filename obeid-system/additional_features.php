<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Obeid Hospital - Additional Services</title>
  <link rel="stylesheet" href="css/additional_features.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="header-left">
        <img src="https://obeidhospital.sa/wp-content/uploads/2024/06/للويب.png" alt="Obeid Hospital Logo" class="logo">
        <h2>Patient Portal</h2>
      </div>
      <div class="nav-links">
        <a href="index.php" class="nav-link">Dashboard</a>
        <a href="appointments.php" class="nav-link">Appointments</a>
        <a href="medical_records.php" class="nav-link">Medical Records</a>
        <a href="additional_features.php" class="nav-link active">Additional Services</a>
        <a href="logout.php" class="nav-link">Logout</a>
      </div>
    </header>

    <main>
      <a href="index.php" class="back-btn">
        ← Return to Dashboard
      </a>
      
      <h1 class="page-title">Additional Services</h1>
      
      <div class="services-grid">
        <div class="service-card" onclick="location.href='schedule.php'">
          <div class="service-image">
            <div class="service-icon">📅</div>
          </div>
          <div class="service-content">
            <h3 class="service-title">Schedule</h3>
            <p class="service-description">View and manage your appointments and hospital visits schedule.</p>
            <a href="schedule.php" class="service-link">Access Schedule →</a>
          </div>
        </div>
        
        <div class="service-card" onclick="location.href='calendar.php'">
          <div class="service-image">
            <div class="service-icon">🗓️</div>
          </div>
          <div class="service-content">
            <h3 class="service-title">Calendar</h3>
            <p class="service-description">View all your appointments and medical events in calendar format.</p>
            <a href="calendar.php" class="service-link">Open Calendar →</a>
          </div>
        </div>
        
        <div class="service-card" onclick="location.href='search.php'">
          <div class="service-image">
            <div class="service-icon">🔍</div>
          </div>
          <div class="service-content">
            <h3 class="service-title">Search</h3>
            <p class="service-description">Search for doctors, departments, services, and medical information.</p>
            <a href="search.php" class="service-link">Search Now →</a>
          </div>
        </div>
        
        <div class="service-card" onclick="location.href='file_record.php'">
          <div class="service-image">
            <div class="service-icon">📁</div>
          </div>
          <div class="service-content">
            <h3 class="service-title">File Record</h3>
            <p class="service-description">Access and manage your complete medical file and documentation.</p>
            <a href="file_record.php" class="service-link">View Records →</a>
          </div>
        </div>
      </div>
    </main>

    <footer class="footer">
      © <?php echo date('Y'); ?> Obeid Hospital - All rights reserved
    </footer>
  </div>
</body>
</html>
