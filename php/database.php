<?php
// Database connection
$host = 'localhost';
$dbname = 'event_db';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate user information
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone']); // Allow only numbers
    $address = htmlspecialchars(trim($_POST['address']));
    $age = intval($_POST['age']);
    $gender = htmlspecialchars(trim($_POST['gender']));
    $institute_name = htmlspecialchars(trim($_POST['institute_name']));
    $course = htmlspecialchars(trim($_POST['course']));
    $graduating_year = intval($_POST['graduating_year']);

    // Validate mandatory fields
    if (!$name || !$email || !$phone || !$age || !$gender || !$institute_name || !$course || !$graduating_year) {
        die("All fields are required.");
    }

    // Insert user data into the database
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, phone, address, age, gender, institute_name, course, graduating_year)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "ssssisssi",
        $name,
        $email,
        $phone,
        $address,
        $age,
        $gender,
        $institute_name,
        $course,
        $graduating_year
    );

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; // Get the inserted user ID

        // Process events if provided
        if (!empty($_POST['events'])) {
            $event_stmt = $conn->prepare(
                "INSERT INTO event_registrations (user_id, event_name, event_slot, registration_type, special_requirements)
                VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($_POST['events'] as $event) {
                $event_name = htmlspecialchars(trim($event['event']));
                $event_slot = htmlspecialchars(trim($event['event_slot']));
                $registration_type = htmlspecialchars(trim($event['registration_type']));
                $special_requirements = htmlspecialchars(trim($event['special_requirements']));

                // Bind and execute for each event
                $event_stmt->bind_param(
                    "issss",
                    $user_id,
                    $event_name,
                    $event_slot,
                    $registration_type,
                    $special_requirements
                );
                $event_stmt->execute();
            }
            $event_stmt->close();
        }

        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
