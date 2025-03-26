<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user's medical records with error handling
try {
    $stmt = $conn->prepare("SELECT * FROM medical_records WHERE user_id = ? ORDER BY record_date DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} finally {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       :root {
  --primary-color: #1a73e8;
  --primary-dark: #1557b0;
  --primary-light: #e8f0fe;
  --secondary-color: #34a853;
  --accent-color: #fbbc05;
  --light-gray: #f8f9fa;
  --border-color: #e0e0e0;
  --text-primary: #202124;
  --text-secondary: #5f6368;
  --danger-color: #ea4335;
  --danger-hover: #d33426;
  --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  --radius: 12px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
  background-color: #f8fafc;
  line-height: 1.6;
  color: var(--text-primary);
}

a {
  text-decoration: none;
  color: inherit;
}

.wrapper {
  display: flex;
  min-height: 100vh;
}

/* Sidebar Navigation */
.sidebar {
  width: 240px;
  background-color: white;
  box-shadow: var(--shadow);
  position: fixed;
  height: 100%;
  overflow-y: auto;
  z-index: 10;
}

.logo {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid var(--border-color);
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  color: white;
}

.logo img {
  height: 40px;
}

.nav-menu {
  padding: 1rem 0;
}

.nav-item {
  padding: 0.5rem 1.5rem;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: var(--text-primary);
  text-decoration: none;
  border-radius: var(--radius);
  transition: var(--transition);
}

.nav-link:hover {
  background-color: var(--primary-light);
  color: var(--primary-color);
  transform: translateX(5px);
}

.nav-link.active {
  background-color: var(--primary-color);
  color: white;
}

.nav-link i {
  margin-right: 0.75rem;
  font-size: 1.1rem;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 240px;
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.page-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--text-primary);
}

.btn-print {
  background-color: var(--primary-color);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  transition: var(--transition);
  cursor: pointer;
}

.btn-print:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-print i {
  font-size: 1.1rem;
}

.card {
  background-color: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin-bottom: 1.5rem;
  overflow: hidden;
  animation: fadeIn 0.5s ease-out forwards;
}

.card-header {
  padding: 1.25rem 1.5rem;
  background-color: white;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 0;
  color: var(--text-primary);
}

.card-body {
  padding: 1.5rem;
}

/* Table Styling */
.records-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

.records-table th {
  background-color: var(--light-gray);
  color: var(--text-primary);
  font-weight: 600;
  text-align: left;
  padding: 1rem;
  border-bottom: 2px solid var(--border-color);
}

.records-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
  vertical-align: top;
  color: var(--text-secondary);
}

.records-table tr:last-child td {
  border-bottom: none;
}

.records-table tr:hover {
  background-color: var(--primary-light);
}

.no-records {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--text-secondary);
}

.no-records i {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
  color: var(--primary-color);
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 1200px) {
  .main-content {
    padding: 1.5rem;
  }
}

@media (max-width: 992px) {
  .sidebar {
    width: 70px;
    overflow: visible;
  }
  
  .logo {
    padding: 1rem;
  }
  
  .nav-link span {
    display: none;
  }
  
  .nav-link i {
    margin-right: 0;
    font-size: 1.25rem;
  }
  
  .main-content {
    margin-left: 70px;
  }
  
  .nav-item {
    padding: 0.5rem;
    display: flex;
    justify-content: center;
  }
  
  .nav-link {
    padding: 0.75rem;
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .wrapper {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
  }
  
  .main-content {
    margin-left: 0;
    padding: 1rem;
  }
  
  .nav-menu {
    display: flex;
    justify-content: space-around;
    padding: 0.5rem 0;
  }
  
  .nav-item {
    padding: 0;
  }
  
  .nav-link {
    padding: 0.5rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .records-table {
    display: block;
    overflow-x: auto;
  }
}

@media (max-width: 480px) {
  .nav-menu {
    flex-wrap: wrap;
  }
  
  .nav-item {
    flex: 1 0 33.333%;
    text-align: center;
  }
}
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">
                <img src="logo.png" alt="Healthcare Logo" onerror="this.src='data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%232c7be5\'/%3E%3Cpath d=\'M40 30h20v10H40zm0 30h20V50H40z\' fill=\'white\'/%3E%3C/svg%3E'">
            </div>
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="user.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="medical-record.php" class="nav-link active">
                        <i class="fas fa-file-medical-alt"></i>
                        <span>Medical Records</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="patient-info.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>Patient Info</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="contact.php" class="nav-link">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Medical Records</h1>
                <div class="actions">
                    <button class="btn-print" onclick="window.print()" style="background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-print"></i> Print Records
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Your Medical History</h2>
                </div>
                <div class="card-body">
                    <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Appointment</th>
                                    <th>Diagnosis</th>
                                    <th>Prescription</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>Dr. <?php echo htmlspecialchars($row['doctor_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['prescription']); ?></td>
                                    <td><?php 
                                        $date = new DateTime($row['record_date']);
                                        echo $date->format('M d, Y'); 
                                    ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="no-records">
                        <i class="fas fa-file-medical"></i>
                        <p>No medical records found in our system.</p>
                        <p>If you believe this is an error, please contact your healthcare provider.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>