<?php
function fetchPastEventsFromDatabase() {
    $server = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "events_db";

    $db = new mysqli($server, $username, $password, $dbname);

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $current_date = date('Y-m-d');
    $query = "SELECT * FROM events WHERE timeline_category = 'past' AND event_date < ? ORDER BY event_date DESC";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $past_events = [];
    while ($row = $result->fetch_assoc()) {
        $past_events[] = $row;
    }

    $stmt->close();
    $db->close();

    return $past_events;
}

$past_events = fetchPastEventsFromDatabase();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Past Events</title>
  <link rel="stylesheet" href="CSS Files/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet" />

  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    h1 {
      text-align: center;
      margin-top: 30px;
    }

    #calendar {
      max-width: 1000px;
      margin: 40px auto 0;
      padding: 20px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .event-cards {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin: 40px auto;
      max-width: 1300px;
      gap: 20px;
    }

    .event-card {
      flex: 1 1 calc(20% - 20px);
      background-color: #ffffff;
      border-radius: 10px;
      padding: 12px;
      box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
      max-width: 250px;
      box-sizing: border-box;
    }

    .event-card h3 {
      font-size: 1.1em;
      margin: 10px 0 5px;
      color: #333;
    }

    .event-card p {
      font-size: 0.95em;
      color: #555;
      margin: 4px 0;
    }

    .event-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .event-card img:hover {
      transform: scale(1.03);
    }

    .home-button {
      text-align: center;
      margin-bottom: 40px;
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

    /* Lightbox Styling */
    #lightbox {
      display: none;
      position: fixed;
      z-index: 9999;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
      cursor: zoom-out;
    }

    #lightbox img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    }

    @media (max-width: 768px) {
      .event-card {
        flex: 1 1 calc(50% - 20px);
      }
    }

    @media (max-width: 480px) {
      .event-card {
        flex: 1 1 100%;
      }
    }
  </style>
</head>
<body>

  <h1>Past Events</h1>

  <!-- Calendar Container -->
  <div id="calendar"></div>

  <!-- Event Cards -->
  <?php if (empty($past_events)) : ?>
    <p style="text-align: center;">No past events found.</p>
  <?php else : ?>
    <section class="event-cards">
      <?php foreach ($past_events as $event) : ?>
        <div class="event-card">
          <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
          <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
          <p><strong>Time:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
          <?php if ($event['flyer_url']) : ?>
            <img src="<?php echo htmlspecialchars($event['flyer_url']); ?>" alt="Event Flyer" onclick="openLightbox('<?php echo htmlspecialchars($event['flyer_url']); ?>')">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <!-- Home Button -->
  <div class="home-button">
    <button onclick="window.location.href='index.html'">Home Page</button>
  </div>

  <!-- Lightbox Modal -->
  <div id="lightbox" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="Full Flyer">
  </div>

  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
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
            'id' => $event['id'],
            'title' => $event['event_name'],
            'start' => $event['event_date'] . 'T' . $event['event_time'],
            'extendedProps' => [
              'location' => $event['event_location'],
              'flyer' => $event['flyer_url']
            ]
          ];
        }, $past_events)); ?>,
        eventClick: function(info) {
          window.location.href = 'event-details.php?id=' + info.event.id;
        }
      });

      calendar.render();
    });

    function openLightbox(url) {
      document.getElementById('lightbox-img').src = url;
      document.getElementById('lightbox').style.display = 'flex';
    }

    function closeLightbox() {
      document.getElementById('lightbox').style.display = 'none';
    }
  </script>
</body>
</html>
