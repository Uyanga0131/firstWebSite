<?php
function fetchUpcomingEventsFromDatabase($category = null) {
    $server = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "events_db";

    $db = new mysqli($server, $username, $password, $dbname);

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $current_date = date('Y-m-d');
    // Adjust query to filter events by category if it's provided
    $query = "SELECT * FROM events WHERE event_date >= ? " . ($category ? "AND event_category = ?" : "") . " ORDER BY event_date ASC";
    $stmt = $db->prepare($query);

    if ($category) {
        $stmt->bind_param("ss", $current_date, $category);
    } else {
        $stmt->bind_param("s", $current_date);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $upcoming_events = [];
    while ($row = $result->fetch_assoc()) {
        $upcoming_events[] = $row;
    }

    $stmt->close();
    $db->close();

    return $upcoming_events;
}

$category = isset($_GET['category']) ? $_GET['category'] : null;
$upcoming_events = fetchUpcomingEventsFromDatabase($category);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Upcoming Events</title>
  <link rel="stylesheet" href="CSS Files/style.css" />

  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet" />

  <!-- Simple Lightbox CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplelightbox@2.1.0/dist/simple-lightbox.min.css">

  <style>
    #calendar {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .event-cards {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 30px;
    }

    .event-card {
      width: 18%;
      margin: 15px;
      padding: 15px;
      border-radius: 10px;
      background-color: #f9f9f9;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .event-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
    }

    .event-card h3 {
      font-size: 1.2em;
      color: #333;
    }

    .event-card p {
      font-size: 1em;
      color: #555;
      margin: 8px 0;
    }

    .home-button {
      text-align: center;
      margin-top: 40px;
    }

    .home-button button {
      padding: 10px 20px;
      font-size: 1.1em;
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .home-button button:hover {
      background-color: #0056b3;
    }

    .category-selector {
      text-align: center;
      margin: 20px;
    }

    .category-selector select {
      padding: 10px;
      font-size: 1.1em;
      border-radius: 5px;
    }

    /* Responsive Design: 5 events per row */
    @media (max-width: 1200px) {
      .event-card {
        width: 19%;
      }
    }

    @media (max-width: 900px) {
      .event-card {
        width: 22%;
      }
    }

    @media (max-width: 768px) {
      .event-card {
        width: 45%;
      }
    }

    @media (max-width: 480px) {
      .event-card {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <h1 style="text-align: center;">Upcoming Events</h1>

  <!-- Category Selector -->
  <div class="category-selector">
    <h2>Select a Category:</h2>
    <form method="GET" action="">
      <select name="category" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <option value="Social & Fun" <?php echo $category == 'Social & Fun' ? 'selected' : ''; ?>>Social & Fun</option>
        <option value="Career" <?php echo $category == 'Career' ? 'selected' : ''; ?>>Career</option>
        <option value="Education" <?php echo $category == 'Education' ? 'selected' : ''; ?>>Education</option>
        <option value="Networking" <?php echo $category == 'Networking' ? 'selected' : ''; ?>>Networking</option>
      </select>
    </form>
  </div>

  <!-- Calendar Container -->
  <div id="calendar"></div>

  <!-- Event Cards -->
  <?php if (empty($upcoming_events)) : ?>
    <p style="text-align: center;">No upcoming events found.</p>
  <?php else : ?>
    <section class="event-cards">
      <?php foreach ($upcoming_events as $event) : ?>
        <div class="event-card">
          <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
          <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
          <p><strong>Time:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
          <p><strong>Location:</strong> <?php echo htmlspecialchars($event['event_location']); ?></p>
          <?php if ($event['flyer_url']) : ?>
            <a href="<?php echo htmlspecialchars($event['flyer_url']); ?>" data-lightbox="event-<?php echo $event['id']; ?>"><img src="<?php echo htmlspecialchars($event['flyer_url']); ?>" alt="Event Flyer"></a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <!-- Home Button -->
  <div class="home-button">
    <button onclick="window.location.href='index.html'">Home Page</button>
  </div>

  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
  
  <!-- Simple Lightbox JS -->
  <script src="https://cdn.jsdelivr.net/npm/simplelightbox@2.1.0/dist/simple-lightbox.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,listMonth'
        },
        events: <?php echo json_encode(array_map(function($event) {
          return [
            'id' => $event['id'],  // The event's unique ID for redirection
            'title' => $event['event_name'],
            'start' => $event['event_date'] . 'T' . $event['event_time'],
            'extendedProps' => [
              'location' => $event['event_location'],
              'flyer' => $event['flyer_url']
            ]
          ];
        }, $upcoming_events)); ?>,
        eventClick: function(info) {
          // Redirect to the event's detail page
          window.location.href = 'event-details.php?id=' + info.event.id;
        }
      });

      calendar.render();

      // Initialize SimpleLightbox for event images
      var lightbox = new SimpleLightbox('[data-lightbox]');
    });
  </script>
</body>
</html>
