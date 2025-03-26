<?php
session_start();
include 'config.php';
$error = "";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get doctor's ID from session
$doctor_id = $_SESSION['user_id'];

// Count today's appointments - add error handling
$today = date("Y-m-d");
$today_appointments = 0;
try {
    $sql_today_appointments = "SELECT COUNT(*) as count FROM appointments 
                              WHERE user_id = ? AND appointment_date = ?";
    $stmt = $conn->prepare($sql_today_appointments);
    if ($stmt) {
        $stmt->bind_param("is", $doctor_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $today_appointments = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    $today_appointments = 0;
    // Log error if needed
}

// Count pending medical records
$pending_records = 0;
try {
    // Count pending medical records
    $sql_pending_records = "SELECT COUNT(*) as count FROM appointments 
                          WHERE user_id = ? AND status = 'Confirmed' 
                          AND appointment_date <= ?";
    $stmt = $conn->prepare($sql_pending_records);
    if ($stmt) {
        $stmt->bind_param("is", $doctor_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $pending_records = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    $pending_records = 0;
    // Log error if needed
}

// Count test results to review (placeholder query - adjust as needed)
$test_results_to_review = 5; // Static for now, update with actual query when test results table is available

// Count total patients this week
$weekly_patients = 0;
try {
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime('sunday this week'));
    $sql_weekly_patients = "SELECT COUNT(DISTINCT user_id) as count FROM appointments 
                         WHERE user_id = ? AND appointment_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql_weekly_patients);
    if ($stmt) {
        $stmt->bind_param("iss", $doctor_id, $week_start, $week_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $weekly_patients = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    $weekly_patients = 0;
    // Log error if needed
}

// First, let's try to update the database schema if needed
function tableColumnExists($conn, $table, $column) {
    $query = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Add missing columns to appointments table if needed
if (!tableColumnExists($conn, 'appointments', 'appointment_time')) {
    $alterQuery = "ALTER TABLE appointments ADD COLUMN appointment_time TIME AFTER appointment_date";
    mysqli_query($conn, $alterQuery);
}

if (!tableColumnExists($conn, 'appointments', 'appointment_type')) {
    $alterQuery = "ALTER TABLE appointments ADD COLUMN appointment_type VARCHAR(100) AFTER appointment_time";
    mysqli_query($conn, $alterQuery);
}

// Add missing doctor_id column to appointments if needed
if (!tableColumnExists($conn, 'appointments', 'doctor_id')) {
    $alterQuery = "ALTER TABLE appointments ADD COLUMN doctor_id INT AFTER user_id";
    mysqli_query($conn, $alterQuery);
}

// Add missing columns to medical_records table if needed
if (!tableColumnExists($conn, 'medical_records', 'doctor_id')) {
    $alterQuery = "ALTER TABLE medical_records ADD COLUMN doctor_id INT AFTER user_id";
    mysqli_query($conn, $alterQuery);
}

if (!tableColumnExists($conn, 'medical_records', 'appointment_id')) {
    $alterQuery = "ALTER TABLE medical_records ADD COLUMN appointment_id INT AFTER doctor_id";
    mysqli_query($conn, $alterQuery);
}

// Get upcoming appointments - UPDATED QUERY
$upcoming_appointments = null;
try {
    // First check if doctor_id column exists in appointments
    if (tableColumnExists($conn, 'appointments', 'doctor_id')) {
        // If doctor_id exists, get appointments assigned to this doctor
        $sql_upcoming = "SELECT a.*, u.full_name, u.file_number 
                      FROM appointments a 
                      JOIN users u ON a.user_id = u.id 
                      WHERE a.doctor_id = ? AND a.appointment_date = ? 
                      AND a.status = 'Confirmed'";
        
        if (tableColumnExists($conn, 'appointments', 'appointment_time')) {
            $sql_upcoming .= " ORDER BY a.appointment_time ASC";
        }
        
        $sql_upcoming .= " LIMIT 4";
        $stmt = $conn->prepare($sql_upcoming);
        $stmt->bind_param("is", $doctor_id, $today);
    } else {
        // If doctor_id doesn't exist, use user_id (temporary assumption that user_id is both patient and doctor)
        $sql_upcoming = "SELECT a.*, u.full_name, u.file_number 
                      FROM appointments a 
                      JOIN users u ON a.user_id = u.id 
                      WHERE a.appointment_date = ? 
                      AND a.status = 'Confirmed'";
        
        if (tableColumnExists($conn, 'appointments', 'appointment_time')) {
            $sql_upcoming .= " ORDER BY a.appointment_time ASC";
        }
        
        $sql_upcoming .= " LIMIT 4";
        $stmt = $conn->prepare($sql_upcoming);
        $stmt->bind_param("s", $today);
    }
    
    if ($stmt) {
        $stmt->execute();
        $upcoming_appointments = $stmt->get_result();
    }
} catch (Exception $e) {
    $upcoming_appointments = null;
    // Log error if needed
}

// Get recent medical records - UPDATED QUERY
$recent_records = null;
try {
    // First check if doctor_id column exists in medical_records
    if (tableColumnExists($conn, 'medical_records', 'doctor_id')) {
        // If doctor_id exists, get records assigned to this doctor
        $sql_records = "SELECT mr.*, u.full_name, u.file_number 
                      FROM medical_records mr 
                      JOIN users u ON mr.user_id = u.id 
                      WHERE mr.doctor_id = ? 
                      ORDER BY mr.record_date DESC 
                      LIMIT 4";
        $stmt = $conn->prepare($sql_records);
        $stmt->bind_param("i", $doctor_id);
    } else {
        // If doctor_id doesn't exist, get all recent records
        $sql_records = "SELECT mr.*, u.full_name, u.file_number 
                      FROM medical_records mr 
                      JOIN users u ON mr.user_id = u.id 
                      ORDER BY mr.record_date DESC 
                      LIMIT 4";
        $stmt = $conn->prepare($sql_records);
    }
    
    if ($stmt) {
        $stmt->execute();
        $recent_records = $stmt->get_result();
    }
} catch (Exception $e) {
    $recent_records = null;
    // Log error if needed
}
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    if ($seconds < 60) {
        return "Just now";
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . " minutes ago";
    } elseif ($seconds < 86400) {
        return floor($seconds / 3600) . " hours ago";
    } elseif ($seconds < 604800) {
        return floor($seconds / 86400) . " days ago";
    } elseif ($seconds < 2419200) {
        return floor($seconds / 604800) . " weeks ago";
    } else {
        return date("F j, Y", $time_ago);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Obeid Hospital - Doctor Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="dashboard-container">
    <header class="header">
      <div class="header-left">
        <img src="https://obeidhospital.sa/wp-content/uploads/2024/06/للويب.png" alt="Obeid Hospital Logo" class="logo">
        <h2>Doctor Management System</h2>
      </div>
      <div class="header-right">
        <span class="user-department"><i class="fas fa-building"></i> Department: <?php echo isset($_SESSION['department']) ? $_SESSION['department'] : 'General'; ?></span>
        <a href="profile.php" class="profile-btn"><i class="fas fa-user-md"></i> My Profile</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </header>

    <div class="welcome-section">
      <div class="welcome-message">
        <h1>Welcome, Dr. <?php echo $_SESSION['full_name']; ?>!</h1>
        <p>Manage your patients and appointments efficiently</p>
      </div>
      <div class="date-time">
        <div id="current-date"></div>
        <div id="current-time"></div>
      </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-value"><?php echo $today_appointments; ?></div>
        <div class="stat-label">Today's Appointments</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $pending_records; ?></div>
        <div class="stat-label">Pending Medical Records</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $test_results_to_review; ?></div>
        <div class="stat-label">Test Results to Review</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $weekly_patients; ?></div>
        <div class="stat-label">Total Patients This Week</div>
      </div>
    </div>

    <!-- Features from the flowchart -->
    <div class="features-grid">
      <div class="feature-card" onclick="location.href='appointments.php'">
        <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="feature-title">Appointments</div>
        <div class="feature-description">View and manage your appointment schedule</div>
      </div>
      
      <div class="feature-card" onclick="location.href='medical-record.php'">
        <div class="feature-icon"><i class="fas fa-file-medical"></i></div>
        <div class="feature-title">Medical Records</div>
        <div class="feature-description">Update and view patient medical records</div>
      </div>
      
      <div class="feature-card" onclick="location.href='medical_result.php'">
        <div class="feature-icon"><i class="fas fa-microscope"></i></div>
        <div class="feature-title">Medical Results</div>
        <div class="feature-description">Review and manage patient test results</div>
      </div>
      
      <div class="feature-card" onclick="location.href='doctor_perception.php'">
        <div class="feature-icon"><i class="fas fa-prescription"></i></div>
        <div class="feature-title">Doctor Perception</div>
        <div class="feature-description">Issue and manage patient prescriptions</div>
      </div>
      
      <div class="feature-card" onclick="location.href='additional_features.php'">
        <div class="feature-icon"><i class="fas fa-tools"></i></div>
        <div class="feature-title">Additional Features</div>
        <div class="feature-description">Access schedules, calendar, and search tools</div>
      </div>
    </div>

    <div class="upcoming-section">
      <div class="upcoming-appointments">
        <div class="section-header">
          <h3><i class="fas fa-calendar-day"></i> Upcoming Appointments</h3>
          <a href="appointments.php" class="view-all">View All</a>
        </div>
        <div class="appointment-list">
          <?php if ($upcoming_appointments && $upcoming_appointments->num_rows > 0): ?>
            <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
              <div class="appointment-item">
                <strong><?php echo isset($appointment['appointment_time']) ? date('h:i A', strtotime($appointment['appointment_time'])) : 'Time not set'; ?></strong> - 
                <?php echo htmlspecialchars($appointment['full_name']); ?> 
                (File #<?php echo htmlspecialchars($appointment['file_number']); ?>) - 
                <?php echo isset($appointment['appointment_type']) ? htmlspecialchars($appointment['appointment_type']) : 'Consultation'; ?>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-data-message">No appointments scheduled for today.</div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="medical-records-list">
        <div class="section-header">
          <h3><i class="fas fa-clipboard-list"></i> Recent Medical Records</h3>
          <a href="medical-record.php" class="view-all">View All</a>
        </div>
        <div class="records-list">
          <?php if ($recent_records && $recent_records->num_rows > 0): ?>
            <?php while ($record = $recent_records->fetch_assoc()): ?>
              <div class="record-item">
                <strong>File #<?php echo htmlspecialchars($record['file_number']); ?></strong> - 
                <?php echo htmlspecialchars($record['full_name']); ?> - 
                Updated <?php echo timeAgo($record['record_date']); ?>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-data-message">No recent medical records found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <footer class="footer">
      © <?php echo date('Y'); ?> Obeid Hospital - All rights reserved
    </footer>
  </div>

  <script>
    // Display current date and time
    function updateDateTime() {
      const now = new Date();
      const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
      
      document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
      document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
    }
    
    updateDateTime();
    setInterval(updateDateTime, 1000); // Update every second for a more dynamic feel
  </script>
</body>
</html>