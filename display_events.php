<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = 'root';
$db = 'events_db';

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all events
$sql = "SELECT * FROM events ORDER BY event_date, event_time";
$result = $conn->query($sql);

// Categorize events
$upcoming = [];
$happening = [];
$past = [];

date_default_timezone_set('America/Los_Angeles');
$now = new DateTime();

while ($row = $result->fetch_assoc()) {
    $event_datetime = new DateTime($row['event_date'] . ' ' . $row['event_time']);
    $diff = $now->diff($event_datetime);

    if ($event_datetime > $now) {
        $upcoming[] = $row;
    } elseif ($diff->days === 0 && $event_datetime->format('Y-m-d') === $now->format('Y-m-d')) {
        $happening[] = $row;
    } else {
        $past[] = $row;
    }
}

// Function to display events
function displayEvents($events, $category) {
    echo "<h2>$category</h2><section class='event-container'>";
    if (empty($events)) {
        echo "<p>No events in this category.</p>";
    } else {
        foreach ($events as $event) {
            echo "<div class='event-card'>";
            echo "<h3>" . htmlspecialchars($event['event_name']) . "</h3>";
            echo "<p><strong>Date:</strong> " . htmlspecialchars($event['event_date']) . "</p>";
            echo "<p><strong>Time:</strong> " . htmlspecialchars($event['event_time']) . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($event['event_location']) . "</p>";
            echo "<p><strong>Category:</strong> " . htmlspecialchars($event['event_category']) . "</p>";

            $flyerPath = $event['flyer_url'];
            $fileExtension = strtolower(pathinfo($flyerPath, PATHINFO_EXTENSION));

            echo "<p><strong>Flyer:</strong><br>";
            if (!empty($flyerPath)) {
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    echo "<img src='" . htmlspecialchars($flyerPath) . "' alt='Event Flyer' class='event-flyer' onclick='openLightbox(this)'>";
                } elseif ($fileExtension === 'pdf') {
                    echo "<a href='" . htmlspecialchars($flyerPath) . "' target='_blank'>View PDF Flyer</a>";
                } else {
                    echo "Unsupported flyer type.";
                }
            } else {
                echo "No flyer uploaded.";
            }
            echo "</p><hr>";
            echo "</div>";
        }
    }
    echo "</section>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .event-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
        }
        .event-flyer {
            max-width: 300px;
            height: auto;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
            background-color: #f9f9f9;
            width: 100%;
            max-width: 300px;
        }
        .bottom-nav {
            margin-top: 30px;
        }
        .bottom-nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #3498db;
        }

        /* Search Bar Styling */
        #search-bar {
            margin-bottom: 30px;
            padding: 10px;
            width: 100%;
            font-size: 1.1em;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        /* Lightbox Styling */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
        }

        /* Close Button for Lightbox */
        .lightbox-close {
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
            font-size: 2em;
            cursor: pointer;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <h1>Event Listings</h1>

    <!-- Search Bar -->
    <input type="text" id="search-bar" placeholder="Search events..." onkeyup="searchEvents()">

    <?php
        displayEvents($upcoming, 'Upcoming Events');
        displayEvents($happening, 'Happening Right Now');
        displayEvents($past, 'Past Events');
    ?>

    <div class="bottom-nav">
        <a href="submit.html">Submit Event</a>
        <a href="index.html">Home</a>
    </div>

    <!-- Lightbox for image viewing -->
    <div class="lightbox" id="lightbox">
        <img src="" alt="Enlarged Flyer">
        <span class="lightbox-close" onclick="closeLightbox()">×</span>
    </div>

    <script>
        // Lightbox functionality
        function openLightbox(imgElement) {
            var lightbox = document.getElementById('lightbox');
            var lightboxImage = lightbox.querySelector('img');
            lightboxImage.src = imgElement.src;
            lightbox.style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
        }

        // Search functionality
        function searchEvents() {
            var input = document.getElementById('search-bar');
            var filter = input.value.toLowerCase();
            var eventCards = document.querySelectorAll('.event-card');

            eventCards.forEach(function(card) {
                var eventName = card.querySelector('h3').innerText.toLowerCase();
                if (eventName.indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>
