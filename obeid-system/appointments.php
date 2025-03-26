<?php
session_start();
include 'config.php';
$error = "";


// Handle appointment status update
if (isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $appointmentId = $_POST['appointment_id'];
    $status = ($_POST['action'] == 'accept') ? 'Confirmed' : 'Cancelled';
    
    $updateQuery = "UPDATE appointments SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $status, $appointmentId);
    
    if ($stmt->execute()) {
        $statusMessage = "Appointment " . strtolower($status) . " successfully.";
    } else {
        $errorMessage = "Error updating appointment: " . $conn->error;
    }
}

// Function to get appointment status class for styling
function getStatusClass($status) {
    switch ($status) {
        case 'Confirmed':
            return 'status-confirmed';
        case 'Cancelled':
            return 'status-cancelled';
        default:
            return 'status-pending';
    }
}

// Pagination setup
$recordsPerPage = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Filter parameters
$dateFilter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$departmentFilter = isset($_GET['department']) ? $_GET['department'] : 'all';

// Build the query with filters - using your existing table structure
$query = "SELECT a.*, u.full_name, u.file_number, u.saudi_id 
          FROM appointments a 
          JOIN users u ON a.user_id = u.id 
          WHERE 1=1";

$params = [];
$types = "";

// Add date filter if specified
if (!empty($dateFilter) && $dateFilter != 'all') {
    $query .= " AND a.appointment_date = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

// Add status filter if specified
if (!empty($statusFilter) && $statusFilter != 'all') {
    $query .= " AND a.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

// Add department filter if specified
if (!empty($departmentFilter) && $departmentFilter != 'all') {
    $query .= " AND a.department = ?";
    $params[] = $departmentFilter;
    $types .= "s";
}

// Count total records for pagination
$countQuery = str_replace("a.*, u.full_name, u.file_number, u.saudi_id", "COUNT(*) as total", $query);
$countStmt = $conn->prepare($countQuery);

if (!empty($types)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$totalResult = $countStmt->get_result();
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Final query with pagination
$query .= " ORDER BY a.appointment_date DESC, a.id DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $recordsPerPage;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result();

// Get available departments for filter
$deptQuery = "SELECT DISTINCT department FROM appointments ORDER BY department";
$deptResult = $conn->query($deptQuery);
$departments = [];
while ($dept = $deptResult->fetch_assoc()) {
    $departments[] = $dept['department'];
}

// Get date range for filter (next 7 days)
$dateOptions = [];
$today = new DateTime();
for ($i = 0; $i < 7; $i++) {
    $date = clone $today;
    $date->modify("+$i day");
    $dateOptions[$date->format('Y-m-d')] = $date->format('D, M j'); // e.g., "Mon, Jan 15"
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments - Obeid Hospital Admin</title>
    <link rel="stylesheet" href="css/appointment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <img src="https://obeidhospital.sa/wp-content/uploads/2024/06/للويب.png" alt="Obeid Hospital Logo" class="logo">
                <h2>Admin Doctor Management System</h2>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="dashboard-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="profile.php" class="profile-btn"><i class="fas fa-user-shield"></i> Admin Profile</a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-user-md"></i> Doctor Appointments Management</h1>
        </div>

        <?php if (isset($statusMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $statusMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="filters-section">
            <h3 class="filters-title"><i class="fas fa-filter"></i> Filter Appointments</h3>
            <form class="filters-form" method="GET" action="">
                <div class="form-group">
                    <label for="date">Date</label>
                    <select name="date" id="date" class="form-control">
                        <option value="all" <?php echo ($dateFilter == 'all') ? 'selected' : ''; ?>>All Dates</option>
                        <?php foreach ($dateOptions as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($dateFilter == $value) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="all" <?php echo ($statusFilter == 'all') ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Pending" <?php echo ($statusFilter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Confirmed" <?php echo ($statusFilter == 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="Cancelled" <?php echo ($statusFilter == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select name="department" id="department" class="form-control">
                        <option value="all">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept; ?>" <?php echo ($departmentFilter == $dept) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="appointments.php" class="reset-btn">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="action-buttons-top">
            <a href="doctor_management.php" class="btn btn-primary">
                <i class="fas fa-user-md"></i> Manage Doctors
            </a>
            <a href="add_doctor.php" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Add New Doctor
            </a>
            <a href="doctor_schedule.php" class="btn btn-info">
                <i class="fas fa-calendar-alt"></i> Doctor Schedules
            </a>
        </div>

        <div class="appointments-container">
            <?php if ($appointments && $appointments->num_rows > 0): ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>File / Saudi ID</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($appointment = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $appointment['id']; ?></td>
                                <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                                <td>
                                    File: <?php echo htmlspecialchars($appointment['file_number']); ?><br>
                                    ID: <?php echo htmlspecialchars($appointment['saudi_id']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['department']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($appointment['status'] == 'Pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-accept">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="action" value="decline">
                                                <button type="submit" class="btn btn-decline">
                                                    <i class="fas fa-times"></i> Decline
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-view" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            <button class="btn btn-reschedule" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-calendar-alt"></i> Reschedule
                                            </button>
                                            <button class="btn btn-assign" onclick="assignDoctor(<?php echo $appointment['id']; ?>)">
                                                <i class="fas fa-user-md"></i> Assign Doctor
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <div class="page-item">
                                <a href="?page=<?php echo ($page - 1); ?>&date=<?php echo $dateFilter; ?>&status=<?php echo $statusFilter; ?>&department=<?php echo $departmentFilter; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <div class="page-item">
                                <a href="?page=<?php echo $i; ?>&date=<?php echo $dateFilter; ?>&status=<?php echo $statusFilter; ?>&department=<?php echo $departmentFilter; ?>" 
                                   class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </div>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <div class="page-item">
                                <a href="?page=<?php echo ($page + 1); ?>&date=<?php echo $dateFilter; ?>&status=<?php echo $statusFilter; ?>&department=<?php echo $departmentFilter; ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data-message">
                    <i class="fas fa-calendar-times fa-3x" style="opacity: 0.4; margin-bottom: 15px;"></i>
                    <p>No appointments found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            © <?php echo date('Y'); ?> Obeid Hospital - All rights reserved
        </footer>
    </div>

    <script>
        // Function to view appointment details
        function viewAppointment(id) {
            window.location.href = "appointment_details.php?id=" + id;
        }
        
        // Function to reschedule appointment
        function rescheduleAppointment(id) {
            window.location.href = "reschedule_appointment.php?id=" + id;
        }
        
        // Function to assign doctor to appointment
        function assignDoctor(id) {
            window.location.href = "assign_doctor.php?appointment_id=" + id;
        }
    </script>
</body>
</html>