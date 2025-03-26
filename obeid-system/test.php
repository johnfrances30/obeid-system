<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$message = '';
$error = '';
$patient_data = null;
$medical_records = [];
$search_term = '';
$record_id = '';
$diagnosis = '';
$prescription = '';
$search_by = 'file_number';

// Handle search functionality
if (isset($_GET['search']) && !empty($_GET['search_term'])) {
    $search_term = $_GET['search_term'];
    $search_by = $_GET['search_by'];
    
    // Sanitize input
    $search_term = mysqli_real_escape_string($conn, $search_term);
    
    // Build query based on search type
    if ($search_by == 'file_number') {
        $sql = "SELECT * FROM users WHERE file_number = '$search_term'";
    } elseif ($search_by == 'saudi_id') {
        $sql = "SELECT * FROM users WHERE saudi_id = '$search_term'";
    } elseif ($search_by == 'name') {
        $sql = "SELECT * FROM users WHERE full_name LIKE '%$search_term%'";
    }
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $patient_data = $result->fetch_assoc();
        
        // Fetch medical records for this patient
        $patient_id = $patient_data['id'];
        $records_sql = "SELECT * FROM medical_records WHERE user_id = '$patient_id' ORDER BY record_date DESC";
        $records_result = $conn->query($records_sql);
        
        if ($records_result && $records_result->num_rows > 0) {
            while ($row = $records_result->fetch_assoc()) {
                $medical_records[] = $row;
            }
        }
    } else {
        $error = "No patient found with the given information.";
    }
}

// Handle form submission for adding/updating medical record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    // Get form data
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $prescription = mysqli_real_escape_string($conn, $_POST['prescription']);
    
    // Add additional fields from patient record notes
    $past_medical_history = isset($_POST['past_medical_history']) ? mysqli_real_escape_string($conn, $_POST['past_medical_history']) : '';
    $family_medical_history = isset($_POST['family_medical_history']) ? mysqli_real_escape_string($conn, $_POST['family_medical_history']) : '';
    $current_medications = isset($_POST['current_medications']) ? mysqli_real_escape_string($conn, $_POST['current_medications']) : '';
    $lab_results = isset($_POST['lab_results']) ? mysqli_real_escape_string($conn, $_POST['lab_results']) : '';
    $physical_exam = isset($_POST['physical_exam']) ? mysqli_real_escape_string($conn, $_POST['physical_exam']) : '';
    $allergies = isset($_POST['allergies']) ? mysqli_real_escape_string($conn, $_POST['allergies']) : '';
    $vaccinations = isset($_POST['vaccinations']) ? mysqli_real_escape_string($conn, $_POST['vaccinations']) : '';
    $lifestyle_factors = isset($_POST['lifestyle_factors']) ? mysqli_real_escape_string($conn, $_POST['lifestyle_factors']) : '';
    
    // Check if it's an update or new record
    if ($_POST['action'] == 'update' && !empty($_POST['record_id'])) {
        $record_id = mysqli_real_escape_string($conn, $_POST['record_id']);
        
        // Update the existing record
        $sql = "UPDATE medical_records SET 
                diagnosis = '$diagnosis', 
                prescription = '$prescription',
                past_medical_history = '$past_medical_history',
                family_medical_history = '$family_medical_history',
                current_medications = '$current_medications',
                lab_results = '$lab_results',
                physical_exam = '$physical_exam',
                allergies = '$allergies',
                vaccinations = '$vaccinations',
                lifestyle_factors = '$lifestyle_factors',
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = '$record_id'";
                
        if ($conn->query($sql) === TRUE) {
            $message = "Medical record updated successfully!";
        } else {
            $error = "Error updating record: " . $conn->error;
        }
    } else {
        // Create a new record
        $sql = "INSERT INTO medical_records (user_id, diagnosis, prescription, past_medical_history, family_medical_history, 
                current_medications, lab_results, physical_exam, allergies, vaccinations, lifestyle_factors) 
                VALUES ('$user_id', '$diagnosis', '$prescription', '$past_medical_history', '$family_medical_history', 
                '$current_medications', '$lab_results', '$physical_exam', '$allergies', '$vaccinations', '$lifestyle_factors')";
                
        if ($conn->query($sql) === TRUE) {
            $message = "Medical record added successfully!";
        } else {
            $error = "Error adding record: " . $conn->error;
        }
    }
    
    // Refresh the page to show updated data
    header("Location: medical_records.php?search=true&search_by=$search_by&search_term=$search_term");
    exit();
}

// Handle record edit request
if (isset($_GET['edit']) && !empty($_GET['record_id'])) {
    $record_id = $_GET['record_id'];
    $sql = "SELECT * FROM medical_records WHERE id = '$record_id'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $record_data = $result->fetch_assoc();
        $diagnosis = $record_data['diagnosis'];
        $prescription = $record_data['prescription'];
        // Load other fields here
        $past_medical_history = $record_data['past_medical_history'] ?? '';
        $family_medical_history = $record_data['family_medical_history'] ?? '';
        $current_medications = $record_data['current_medications'] ?? '';
        $lab_results = $record_data['lab_results'] ?? '';
        $physical_exam = $record_data['physical_exam'] ?? '';
        $allergies = $record_data['allergies'] ?? '';
        $vaccinations = $record_data['vaccinations'] ?? '';
        $lifestyle_factors = $record_data['lifestyle_factors'] ?? '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Obeid Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            padding: 18px 32px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            margin-bottom: 30px;
            border-radius: 0 0 var(--radius) var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        .page-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .search-container {
            background: white;
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn {
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .patient-info {
            background: white;
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 500;
            font-size: 1.1rem;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .records-list {
            margin-bottom: 30px;
        }

        .record-item {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .record-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transform: translateY(-3px);
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .record-date {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .record-actions {
            display: flex;
            gap: 10px;
        }

        .record-actions a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .record-actions a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .record-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .record-section {
            margin-bottom: 15px;
        }

        .record-section-title {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-secondary);
        }

        .record-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }

        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 500;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
        
        .full-width {
            grid-column: 1 / span 2;
        }
        
        .form-actions {
            grid-column: 1 / span 2;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background-color: #f1f3f4;
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background-color: #e8eaed;
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .record-content {
                grid-template-columns: 1fr;
            }
            
            .record-form {
                grid-template-columns: 1fr;
            }
            
            .full-width, .form-actions {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <img src="https://obeidhospital.sa/wp-content/uploads/2024/06/للويب.png" alt="Obeid Hospital Logo" class="logo">
                <h2>Medical Records Management</h2>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-home"></i> Back to Dashboard</a>
            </div>
        </header>

        <div class="search-container">
            <h2 class="card-title">Search Patient</h2>
            <form class="search-form" method="GET" action="">
                <div class="form-group">
                    <label for="search_by">Search By</label>
                    <select name="search_by" id="search_by">
                        <option value="file_number" <?php echo ($search_by == 'file_number') ? 'selected' : ''; ?>>File Number</option>
                        <option value="saudi_id" <?php echo ($search_by == 'saudi_id') ? 'selected' : ''; ?>>Saudi ID/Residence Permit</option>
                        <option value="name" <?php echo ($search_by == 'name') ? 'selected' : ''; ?>>Patient Name</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="search_term">Search Term</label>
                    <input type="text" name="search_term" id="search_term" value="<?php echo $search_term; ?>" placeholder="Enter search term">
                </div>
                <button type="submit" name="search" value="true" class="btn"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($patient_data): ?>
            <div class="patient-info">
                <h2 class="card-title">Patient Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">File Number</div>
                        <div class="info-value"><?php echo $patient_data['file_number']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Saudi ID/Residence Permit</div>
                        <div class="info-value"><?php echo $patient_data['saudi_id']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo $patient_data['full_name']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo $patient_data['email']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo $patient_data['phone_number']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Birthday</div>
                        <div class="info-value"><?php echo date('F d, Y', strtotime($patient_data['birthday'])); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="tabs">
                    <div class="tab <?php echo empty($record_id) ? 'active' : ''; ?>" data-tab="records">Medical Records</div>
                    <div class="tab <?php echo !empty($record_id) ? 'active' : ''; ?>" data-tab="add-record">
                        <?php echo !empty($record_id) ? 'Edit Record' : 'Add New Record'; ?>
                    </div>
                </div>

                <div class="tab-content <?php echo empty($record_id) ? 'active' : ''; ?>" id="records">
                    <h2 class="card-title">Medical Records History</h2>
                    <div class="records-list">
                        <?php if (count($medical_records) > 0): ?>
                            <?php foreach ($medical_records as $record): ?>
                                <div class="record-item">
                                    <div class="record-header">
                                        <div class="record-date">
                                            <strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($record['record_date'])); ?>
                                        </div>
                                        <div class="record-actions">
                                            <a href="?search=true&search_by=<?php echo $search_by; ?>&search_term=<?php echo $search_term; ?>&edit=true&record_id=<?php echo $record['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="print_record.php?record_id=<?php echo $record['id']; ?>" target="_blank">
                                                <i class="fas fa-print"></i> Print
                                            </a>
                                        </div>
                                    </div>
                                    <div class="record-content">
                                        <div class="record-section">
                                            <div class="record-section-title">Diagnosis</div>
                                            <div><?php echo nl2br($record['diagnosis']); ?></div>
                                        </div>
                                        <div class="record-section">
                                            <div class="record-section-title">Prescription</div>
                                            <div><?php echo nl2br($record['prescription']); ?></div>
                                        </div>

                                        <?php if (!empty($record['past_medical_history'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Past Medical History</div>
                                            <div><?php echo nl2br($record['past_medical_history']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['family_medical_history'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Family Medical History</div>
                                            <div><?php echo nl2br($record['family_medical_history']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['current_medications'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Current Medications</div>
                                            <div><?php echo nl2br($record['current_medications']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['lab_results'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Lab Test Results</div>
                                            <div><?php echo nl2br($record['lab_results']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['physical_exam'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Physical Examination</div>
                                            <div><?php echo nl2br($record['physical_exam']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['allergies'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Allergies</div>
                                            <div><?php echo nl2br($record['allergies']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['vaccinations'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Vaccinations</div>
                                            <div><?php echo nl2br($record['vaccinations']); ?></div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['lifestyle_factors'])): ?>
                                        <div class="record-section">
                                            <div class="record-section-title">Lifestyle Factors</div>
                                            <div><?php echo nl2br($record['lifestyle_factors']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No medical records found for this patient.</p>
                        <?php endif; ?>
                    </div>
                    <button class="btn" id="add-new-record"><i class="fas fa-plus"></i> Add New Record</button>
                </div>

                <div class="tab-content <?php echo !empty($record_id) ? 'active' : ''; ?>" id="add-record">
                    <h2 class="card-title"><?php echo !empty($record_id) ? 'Edit Medical Record' : 'Add New Medical Record'; ?></h2>
                    <form method="POST" action="" class="record-form">
                        <input type="hidden" name="user_id" value="<?php echo $patient_data['id']; ?>">
                        <input type="hidden" name="action" value="<?php echo !empty($record_id) ? 'update' : 'add'; ?>">
                        <?php if (!empty($record_id)): ?>
                            <input type="hidden" name="record_id" value="<?php echo $record_id; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="diagnosis">Diagnosis</label>
                            <textarea name="diagnosis" id="diagnosis" class="form-control" required><?php echo $diagnosis; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="prescription">Prescription</label>
                            <textarea name="prescription" id="prescription" class="form-control" required><?php echo $prescription; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="past_medical_history">Past Medical History</label>
                            <textarea name="past_medical_history" id="past_medical_history" class="form-control"><?php echo $past_medical_history ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="family_medical_history">Family Medical History</label>
                            <textarea name="family_medical_history" id="family_medical_history" class="form-control"><?php echo $family_medical_history ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_medications">Current Medications</label>
                            <textarea name="current_medications" id="current_medications" class="form-control"><?php echo $current_medications ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="lab_results">Lab Test Results</label>
                            <textarea name="lab_results" id="lab_results" class="form-control"><?php echo $lab_results ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="physical_exam">Physical Examination</label>
                            <textarea name="physical_exam" id="physical_exam" class="form-control"><?php echo $physical_exam ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="allergies">Allergies</label>
                            <textarea name="allergies" id="allergies" class="form-control"><?php echo $allergies ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="vaccinations">Vaccinations</label>
                            <textarea name="vaccinations" id="vaccinations" class="form-control"><?php echo $vaccinations ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="lifestyle_factors">Lifestyle Factors</label>
                            <textarea name="lifestyle_factors" id="lifestyle_factors" class="form-control"><?php echo $lifestyle_factors ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="?search=true&search_by=<?php echo $search_by; ?>&search_term=<?php echo $search_term; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> 
                                <?php echo !empty($record_id) ? 'Update Record' : 'Save Record'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif (isset($_GET['search'])): ?>
            <div class="alert alert-danger">
                No patient found. Please try a different search term or check the patient is registered.
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs