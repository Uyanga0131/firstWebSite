<?php
// Database connection
$server = "localhost";
$username = "root"; // MAMP default
$password = "root"; // MAMP default
$dbname = "events_db";

$db = new mysqli($server, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $event_name = isset($_POST['event-name']) ? trim($_POST['event-name']) : '';
    $event_date = isset($_POST['event-date']) ? trim($_POST['event-date']) : '';
    $event_time = isset($_POST['event-time']) ? trim($_POST['event-time']) : '';
    $event_end_time = isset($_POST['event-end-time']) ? trim($_POST['event-end-time']) : '';
    $event_location = isset($_POST['event-location']) ? trim($_POST['event-location']) : '';
    $event_category = isset($_POST['event-category']) ? trim($_POST['event-category']) : '';

    // Basic validation for empty fields
    $required_fields = ['event-name', 'event-date', 'event-time', 'event-end-time', 'event-location', 'event-category'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error_message = "Please fill in all required fields.";
            break;
        }
    }

    if (empty($error_message) && isset($_FILES['flyer']) && $_FILES['flyer']['error'] === 0) {
        // File upload validation
        $file_tmp_name = $_FILES['flyer']['tmp_name'];
        $file_name = basename($_FILES['flyer']['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $error_message = "Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.";
        } elseif ($_FILES['flyer']['size'] > 5000000) { // 5MB limit
            $error_message = "File size exceeds the 5MB limit.";
        } else {
            // Determine timeline category based on current time and event timing
            $current_datetime = new DateTime(); // now
            $event_start = new DateTime("$event_date $event_time");
            $event_end = new DateTime("$event_date $event_end_time");

            if ($current_datetime < $event_start) {
                $timeline_category = "upcoming";
            } elseif ($current_datetime >= $event_start && $current_datetime <= $event_end) {
                $timeline_category = "now";
            } else {
                $timeline_category = "past";
            }

            // Build folder path
            $upload_dir = __DIR__ . "/uploads/$timeline_category/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Move the uploaded file
            $file_path = $upload_dir . $file_name;
            $relative_path = "uploads/$timeline_category/" . $file_name;

            if (move_uploaded_file($file_tmp_name, $file_path)) {
                // Insert into database
                $stmt = $db->prepare("INSERT INTO events (event_name, event_date, event_time, event_end_time, event_location, event_category, flyer_url, timeline_category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $event_name, $event_date, $event_time, $event_end_time, $event_location, $event_category, $relative_path, $timeline_category);

                if ($stmt->execute()) {
                    $success_message = "Event successfully added as '$timeline_category'.";
                } else {
                    $error_message = "Database error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error_message = "Failed to upload the flyer.";
            }
        }
    } else {
        $error_message = "Please upload a valid flyer.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Event</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 1.1em;
            margin-bottom: 10px;
            display: block;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1em;
        }

        input[type="file"] {
            padding: 10px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
            padding: 10px 20px;
            border-radius: 5px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

    <h1>Submit an Event</h1>

    <div class="form-container">
        <!-- Display success or error messages -->
        <?php if (!empty($success_message)): ?>
            <div class="message success"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="message error"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- Event Submission Form -->
        <form action="submit_event.php" method="POST" enctype="multipart/form-data">
            <label for="event-name">Event Name:</label>
            <input type="text" id="event-name" name="event-name" required>

            <label for="event-date">Event Date:</label>
            <input type="date" id="event-date" name="event-date" required>

            <label for="event-time">Start Time:</label>
            <input type="time" id="event-time" name="event-time" required>

            <label for="event-end-time">End Time:</label>
            <input type="time" id="event-end-time" name="event-end-time" required>

            <label for="event-location">Event Location:</label>
            <input type="text" id="event-location" name="event-location" required>

            <label for="event-category">Event Category:</label>
            <select id="event-category" name="event-category" required>
                <option value="Social & Fun">Social & Fun</option>
                <option value="Career">Career</option>
                <option value="Education">Education</option>
                <option value="Networking">Networking</option>
            </select>

            <label for="flyer">Event Flyer:</label>
            <input type="file" id="flyer" name="flyer" required>

            <input type="submit" value="Submit Event">
        </form>

        <!-- Navigation -->
        <div class="bottom-nav" style="text-align: center; margin-top: 20px;">
            <a href="index.html" style="text-decoration: none; color: #007BFF;">Home</a>
        </div>
    </div>

</body>
</html>

<?php
// Close the database connection
$db->close();
?>
