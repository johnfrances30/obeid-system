<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <style>
        /* Reset default margins and paddings */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body and background setup */
        body {
            font-family: Arial, sans-serif;
            background-color: #eaf6fd; /* Light blue background */
            margin: 0;
            transition: background-color 0.3s ease;
            color: #333;
        }

        /* Main container setup */
        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Header styles */
        header {
            background-color: #66b3ff; /* Light blue header */
            color: white;
            padding: 15px 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        header h1 {
            font-size: 24px;
            transition: font-size 0.3s ease;
        }

        header:hover {
            background-color: #5da9e6; /* Darker shade on hover */
        }

        /* Profile information section */
        .profile-info {
            font-size: 18px;
            transition: opacity 0.3s ease;
        }

        .profile-info button {
            padding: 8px 15px;
            background-color: #ff5722;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .profile-info button:hover {
            background-color: #e64a19;
            transform: scale(1.1); /* Slightly enlarge the button on hover */
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #4c9fd1; /* Blue sidebar */
            width: 250px;
            padding: 20px;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar li {
            margin: 15px 0;
            transition: transform 0.3s ease;
        }

        .sidebar li:hover {
            transform: translateX(10px); /* Add a little hover effect for the sidebar items */
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            display: block;
            padding: 8px 0;
            transition: background-color 0.3s, padding-left 0.3s;
        }

        .sidebar a:hover {
            text-decoration: underline;
            background-color: #3f88b5;
            padding-left: 20px; /* Smoothly slide in when hovered */
        }

        /* Main content area */
        main {
            margin-left: 270px; /* Adjusted margin to accommodate sidebar */
            padding: 20px;
            width: calc(100% - 270px);
            min-height: 100vh;
            background-color: #eaf6fd; /* Light blue background for main content */
            transition: margin-left 0.3s ease, background-color 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            main {
                margin-left: 0;
                width: 100%;
            }
        }

        h2 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #333;
            transition: font-size 0.3s ease;
        }

        /* Section styles */
        .section {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }

        /* Keyframe for fade-in effect */
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        form {
            display: flex;
            flex-direction: column;
        }

        form label {
            font-size: 16px;
            margin-bottom: 5px;
            color: #555;
        }

        form input,
        form select,
        form textarea {
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            border-color: #66b3ff; /* Blue focus effect */
            box-shadow: 0 0 5px rgba(102, 179, 255, 0.5); /* Light blue focus */
        }

        form button {
            padding: 10px;
            background-color: #66b3ff; /* Blue button */
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        form button:hover {
            background-color: #5da9e6;
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }

        button {
            background-color: #66b3ff; /* Blue button */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        button:hover {
            background-color: #5da9e6;
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }

        /* Modal for confirmation (Logout) */
        #logoutModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            animation: fadeInModal 0.3s ease;
        }

        @keyframes fadeInModal {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
            transform: translateY(-20px);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            0% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0);
            }
        }

        .modal-content button {
            margin: 10px;
            background-color: #ff5722;
            transition: background-color 0.3s;
        }

        .modal-content button:hover {
            background-color: #e64a19;
        }

        .modal-content .cancel-btn {
            background-color: #ccc;
            transition: background-color 0.3s;
        }

        .modal-content .cancel-btn:hover {
            background-color: #aaa;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            main {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header>
            <h1>Patient Dashboard</h1>
            <div class="profile-info">
                <p>Welcome, <span id="patientName"></span></p> 
                <button id="logoutBtn">Logout</button>
            </div>
        </header>

        <!-- Sidebar Section -->
        <nav class="sidebar">
            <ul>
                <li><a href="#profile">Profile</a></li>
                <li><a href="#health-history">Health History</a></li>
                <li><a href="#treatment-details">Treatment Details</a></li>
                <li><a href="#diagnostic-info">Diagnostic Information</a></li>
                <li><a href="#appointments">Appointments</a></li>
                <li><a href="#prescriptions">Prescriptions</a></li>
                <li><a href="#allergies">Allergies</a></li>
                <li><a href="#immunization">Immunization Records</a></li>
                <li><a href="#lifestyle">Lifestyle & Social History</a></li>
                <li><a href="#communication">Patient-Provider Communication</a></li>
                <li><a href="#care-plans">Follow-Up and Care Plans</a></li>
                <li><a href="#patient-data">Patient-Generated Data</a></li>
                <li><a href="#insurance">Insurance & Billing</a></li>
            </ul>
        </nav>

        <!-- Main Content Section -->
        <main>
            <section id="profile" class="section">
                <h2>Profile Information</h2>
                <form id="profileForm">
                    <label for="residentNumber">Resident Number:</label>
                    <input type="text" id="residentNumber" name="residentNumber" value="" required>

                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="" required>

                    <label for="phoneNumber">Phone Number:</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" value="" required>

                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday" value="" required>

                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>

                    <label for="address">Address:</label>
                    <textarea id="address" name="address" rows="3" required></textarea>

                    <button type="button" id="saveProfileBtn">Save Changes</button>
                </form>
            </section>

            <section id="health-history" class="section">
                <h2>Health History</h2>
                <div>
                    <h3>Past Medical History</h3>
                    <textarea id="pastMedicalHistory" rows="4" placeholder="Records of previous illnesses, surgeries, and treatments"></textarea>
                </div>
                <div>
                    <h3>Family Medical History</h3>
                    <textarea id="familyMedicalHistory" rows="4" placeholder="Information about hereditary conditions in the family"></textarea>
                </div>
            </section>

            <section id="treatment-details" class="section">
                <h2>Treatment Details</h2>
                <div>
                    <h3>Current Medications</h3>
                    <ul id="medicationsList">
                        <!-- Dynamic content -->
                    </ul>
                    <button id="addMedicationBtn">Add New Medication</button>
                </div>
                <div>
                    <h3>Treatment Plans</h3>
                    <textarea id="treatmentPlans" rows="4" placeholder="Details of ongoing treatments and therapies"></textarea>
                </div>
            </section>

            <section id="diagnostic-info" class="section">
                <h2>Diagnostic Information</h2>
                <div>
                    <h3>Lab Test Results</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Date</th>
                                <th>Result</th>
                                <th>Reference Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>Imaging Studies</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>Physical Examination Notes</h3>
                    <textarea id="physicalExam" rows="4" placeholder="Findings from physical assessments"></textarea>
                </div>
            </section>

            <section id="appointments" class="section">
                <h2>Appointments</h2>
                <div>
                    <h3>Appointment History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Provider</th>
                                <th>Notes</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>Upcoming Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Provider</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                    <button id="scheduleAppointmentBtn">Schedule New Appointment</button>
                </div>
                <div>
                    <h3>Appointment Feedback</h3>
                    <textarea id="appointmentFeedback" rows="4" placeholder="Patient feedback regarding their experience"></textarea>
                </div>
            </section>

            <section id="prescriptions" class="section">
                <h2>Prescriptions</h2>
                <div>
                    <h3>Prescription History</h3>
                    <ul id="prescriptionsList">
                        <!-- Dynamic content -->
                    </ul>
                </div>
                <div>
                    <h3>Dosage Information</h3>
                    <textarea id="dosageInfo" rows="4" placeholder="Details on dosages, frequency, and duration of medications"></textarea>
                </div>
            </section>

            <section id="allergies" class="section">
                <h2>Allergies and Adverse Reactions</h2>
                <div>
                    <h3>Known Allergies</h3>
                    <ul id="allergiesList">
                        <!-- Dynamic content -->
                    </ul>
                </div>
                <div>
                    <h3>Adverse Reactions</h3>
                    <textarea id="adverseReactions" rows="4" placeholder="Records of any adverse reactions to treatments"></textarea>
                </div>
            </section>

            <section id="immunization" class="section">
                <h2>Immunization Records</h2>
                <div>
                    <h3>Vaccination History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Vaccine</th>
                                <th>Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="lifestyle" class="section">
                <h2>Lifestyle and Social History</h2>
                <div>
                    <h3>Lifestyle Factors</h3>
                    <textarea id="lifestyleFactors" rows="4" placeholder="Information on smoking, alcohol use, diet, and exercise"></textarea>
                </div>
                <div>
                    <h3>Social Determinants of Health</h3>
                    <textarea id="socialDeterminants" rows="4" placeholder="Factors that may impact health, such as living conditions and employment"></textarea>
                </div>
            </section>

            <section id="communication" class="section">
                <h2>Patient-Provider Communication</h2>
                <div>
                    <h3>Communication Logs</h3>
                    <textarea id="communicationLogs" rows="4" placeholder="Records of messages and communications between the patient and healthcare providers"></textarea>
                </div>
            </section>

            <section id="care-plans" class="section">
                <h2>Follow-Up and Care Plans</h2>
                <div>
                    <h3>Care Plans</h3>
                    <textarea id="carePlans" rows="4" placeholder="Plans for ongoing care, including goals and follow-up appointments"></textarea>
                </div>
            </section>

            <section id="patient-data" class="section">
                <h2>Patient-Generated Data</h2>
                <div>
                    <h3>Symptom Tracking</h3>
                    <textarea id="symptomTracking" rows="4" placeholder="Information entered by patients regarding their symptoms"></textarea>
                </div>
                <div>
                    <h3>Health Metrics</h3>
                    <textarea id="healthMetrics" rows="4" placeholder="Data from wearable devices or health apps (e.g., blood pressure, glucose levels)"></textarea>
                </div>
            </section>

            <section id="insurance" class="section">
                <h2>Insurance and Billing Information</h2>
                <div>
                    <h3>Insurance Details</h3>
                    <textarea id="insuranceDetails" rows="4" placeholder="Information about the patient's insurance provider and coverage"></textarea>
                </div>
                <div>
                    <h3>Billing History</h3>
                    <textarea id="billingHistory" rows="4" placeholder="Records of billing and payment history"></textarea>
                </div>
            </section>
        </main>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to log out?</p>
            <button id="confirmLogoutBtn">Yes, Log Out</button>
            <button class="cancel-btn" id="cancelLogoutBtn">Cancel</button>
        </div>
    </div>

    <script>
        // Handle save profile
document.getElementById('saveProfileBtn').addEventListener('click', function() {
    const profileData = {
        residentNumber: document.getElementById('residentNumber').value,
        fullName: document.getElementById('fullName').value,
        phoneNumber: document.getElementById('phoneNumber').value,
        birthday: document.getElementById('birthday').value,
        gender: document.getElementById('gender').value,
        address: document.getElementById('address').value
    };

    // Simulate saving profile
    console.log('Profile saved:', profileData);
    alert('Profile updated successfully!');
});

// Logout confirmation modal
document.getElementById('logoutBtn').addEventListener('click', function() {
    document.getElementById('logoutModal').style.display = 'flex';
});

// Cancel logout
document.getElementById('cancelLogoutBtn').addEventListener('click', function() {
    document.getElementById('logoutModal').style.display = 'none';
});

// Confirm logout (Redirect to logout.php)
document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
    window.location.href = "dashboard.php"; // Redirects to logout page
});

    </script>
</body>
</html> 
