<?php
$server = "localhost";
$username = "root";
$password = "root";
$dbname = "events_db";
$socket = "/Applications/MAMP/tmp/mysql/mysql.sock"; // Correct socket path

// Connect using the socket
$db = new mysqli($server, $username, $password, $dbname, null, $socket);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Set the timezone to Seattle (America/Los_Angeles)
date_default_timezone_set('America/Los_Angeles'); 

$current_date = date('Y-m-d');
$current_time = date('H:i:s');
$current_datetime = $current_date . ' ' . $current_time; // Full current datetime

// Prepare and execute queries for each category update

// Update to "past" where the event has already finished
$query_past = $db->prepare("UPDATE events SET timeline_category = 'past'
                            WHERE event_date < ? 
                            OR (event_date = ? AND event_end_time < ?)");
$query_past->bind_param("sss", $current_date, $current_date, $current_time);
$query_past->execute();

// Update to "now" where the event is currently happening
$query_now = $db->prepare("UPDATE events SET timeline_category = 'now'
                           WHERE event_date = ? 
                           AND event_time <= ? 
                           AND event_end_time > ?");
$query_now->bind_param("sss", $current_date, $current_time, $current_time);
$query_now->execute();

// Update to "upcoming" where the event is happening later today but hasn't started yet
$query_upcoming_today = $db->prepare("UPDATE events SET timeline_category = 'upcoming'
                                      WHERE event_date = ? 
                                      AND event_time > ?");
$query_upcoming_today->bind_param("ss", $current_date, $current_time);
$query_upcoming_today->execute();

// Update to "upcoming" where the event is in the future (date is later than today)
$query_upcoming_future = $db->prepare("UPDATE events SET timeline_category = 'upcoming'
                                       WHERE event_date > ?");
$query_upcoming_future->bind_param("s", $current_date);
$query_upcoming_future->execute();

// Close database connection
$db->close();
?>
