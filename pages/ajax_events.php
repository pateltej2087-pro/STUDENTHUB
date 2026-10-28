<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

if (!isset($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta
        name="event-csrf"
        content="<?= htmlspecialchars(
            $_SESSION['event_csrf'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <title>StudentHub - AJAX Events</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <nav aria-label="Admin Navigation">
        <a href="admin.php">Admin Dashboard</a>
        <a href="manage_events.php">Events with Posters</a>
        <a href="login.html">Login Page</a>
    </nav>
</header>

<main>
    <section>
        <h2>AJAX Event Management</h2>

        <p id="apiMessage" role="status" aria-live="polite"></p>

        <h3 id="formTitle">Add Event</h3>

        <form id="ajaxEventForm">
            <fieldset id="eventFields">
                <legend>Event Details</legend>

                <input type="hidden" id="eventId" value="0">

                <div class="form-group">
                    <label for="eventName">Event Name</label>

                    <input
                        type="text"
                        id="eventName"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="eventDate">Event Date</label>

                    <input
                        type="date"
                        id="eventDate"
                        min="1000-01-01"
                        max="9999-12-31"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="eventVenue">Venue</label>

                    <input
                        type="text"
                        id="eventVenue"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="eventCategory">Category</label>

                    <input
                        type="text"
                        id="eventCategory"
                        maxlength="50"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="eventStatus">Status</label>

                    <select id="eventStatus" required>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <button type="submit" id="saveEventButton">
                    Save Event
                </button>

                <button type="button" id="cancelEventButton">
                    Clear / Cancel Edit
                </button>
            </fieldset>
        </form>

        <p>
            Poster uploads remain available through
            <a href="manage_events.php">Events with Posters</a>.
        </p>
    </section>

    <section>
        <h2>Event List</h2>

        <div class="form-group">
            <label for="eventSearch">
                Search by event name, venue or category
            </label>

            <input
                type="search"
                id="eventSearch"
                maxlength="150"
                placeholder="Type to search"
            >
        </div>

        <button type="button" id="refreshEventsButton">
            Refresh List
        </button>

        <p id="listStatus" role="status" aria-live="polite"></p>

        <div style="overflow-x: auto;">
            <table>
                <caption>Events from the JSON API</caption>

                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Date</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Category</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody id="eventTableBody"></tbody>
            </table>
        </div>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>
<script src="../js/ajax_events.js"></script>

</body>
</html>