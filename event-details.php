<?php
if (!isset($_GET['id'])) {
    echo "Event ID not provided.";
    exit;
}

$eventId = intval($_GET['id']);

// DB connection
$db = new mysqli("localhost", "root", "root", "events_db");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$query = "SELECT * FROM events WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Event not found.";
    exit;
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="CSS Files/style.css">
</head>
<body>
    <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
    <p><strong>Time:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['event_location']); ?></p>
    <?php if (!empty($event['flyer_url'])): ?>
        <img src="<?php echo htmlspecialchars($event['flyer_url']); ?>" alt="Flyer" style="max-width:500px;">
    <?php endif; ?>
    <br><br>
    <a href="past-events.php">← Back to Calendar</a>
</body>
</html>
