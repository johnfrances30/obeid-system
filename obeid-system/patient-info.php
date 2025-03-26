<?php session_start(); require 'config.php'; // Include database connection 

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user's medical records
$stmt = $conn->prepare("SELECT * FROM medical_records WHERE user_id = ? ORDER BY record_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
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
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-text: #777;
            --border-color: #e9ecef;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: var(--text-color);
            line-height: 1.6;
            padding-top: 70px; /* Space for fixed navbar */
        }
        
        /* Navbar */
        .navbar {
            background-color: var(--primary-color);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .navbar-brand {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 0.5rem;
        }
        
        .navbar-menu {
            display: flex;
            list-style: none;
        }
        
        .navbar-item {
            margin-left: 1.5rem;
        }
        
        .navbar-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
        }
        
        .navbar-link i {
            margin-right: 0.5rem;
        }
        
        .navbar-link:hover {
            opacity: 0.85;
        }
        
        .navbar-link.active {
            border-bottom: 2px solid white;
        }
        
        /* Container */
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header h2 {
            color: var(--text-color);
            font-weight: 600;
            margin: 0;
        }
        
        .header-actions {
            display: flex;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        /* Table */
        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.02);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .records-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 1rem;
            white-space: nowrap;
        }
        
        .records-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        
        .records-table tr:last-child td {
            border-bottom: none;
        }
        
        .records-table tr:nth-child(even) {
            background-color: var(--secondary-color);
        }
        
        .records-table tr:hover {
            background-color: #f1f1f1;
        }
        
        /* No Records Message */
        .no-records {
            text-align: center;
            padding: 2rem;
            background-color: var(--secondary-color);
            border-radius: 5px;
            color: var(--light-text);
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        
        .no-records i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar-link span {
                display: none;
            }
            
            .navbar-link i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .navbar-item {
                margin-left: 1rem;
            }
            
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .records-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-heartbeat"></i>
            <span>MyHealth</span>
        </a>
        <ul class="navbar-menu">
            <li class="navbar-item">
                <a href="dashboard.php" class="navbar-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="medical-record.php" class="navbar-link active">
                    <i class="fas fa-file-medical"></i>
                    <span>Records</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="appointments.php" class="navbar-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="profile.php" class="navbar-link">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="logout.php" class="navbar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Your Medical Records</h2>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="records-table">
                <thead>
                    <tr>
                        <th>Doctor ID</th>
                        <th>Appointment ID</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Record Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['doctor_id']); ?></td>
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
            <i class="fas fa-folder-open"></i>
            <p>No medical records found in our system.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>