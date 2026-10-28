(() => {
    "use strict";

    const form = document.getElementById("ajaxEventForm");
    const fields = document.getElementById("eventFields");
    const formTitle = document.getElementById("formTitle");
    const message = document.getElementById("apiMessage");
    const listStatus = document.getElementById("listStatus");
    const tableBody = document.getElementById("eventTableBody");
    const search = document.getElementById("eventSearch");
    const refreshButton = document.getElementById("refreshEventsButton");

    const eventId = document.getElementById("eventId");
    const eventName = document.getElementById("eventName");
    const eventDate = document.getElementById("eventDate");
    const eventVenue = document.getElementById("eventVenue");
    const eventCategory = document.getElementById("eventCategory");
    const eventStatus = document.getElementById("eventStatus");

    const csrfToken = document.querySelector(
        'meta[name="event-csrf"]'
    ).content;

    let eventsById = new Map();
    let busy = false;
    let searchTimer;
    let listController;
    let listRequestNumber = 0;

    async function apiRequest(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            credentials: "same-origin"
        });

        let result;

        try {
            result = await response.json();
        } catch (error) {
            throw new Error(
                "The server did not return valid JSON. Check the Network tab."
            );
        }

        if (!response.ok || result.success !== true) {
            let errorMessage = result.message || "Request failed.";

            if (
                result.data &&
                Array.isArray(result.data.errors)
            ) {
                errorMessage += " " + result.data.errors.join(" ");
            }

            throw new Error(errorMessage);
        }

        return result;
    }

    function setBusy(value) {
        busy = value;
        fields.disabled = value;
        search.disabled = value;
        refreshButton.disabled = value;

        tableBody.querySelectorAll("button").forEach((button) => {
            button.disabled = value;
        });
    }

    function clearForm() {
        form.reset();
        eventId.value = "0";
        formTitle.textContent = "Add Event";
    }

    function addCell(row, value) {
        const cell = document.createElement("td");
        cell.textContent = String(value);
        row.appendChild(cell);
    }

    function renderEvents(events) {
        tableBody.replaceChildren();
        eventsById = new Map();

        events.forEach((event) => {
            eventsById.set(Number(event.event_id), event);

            const row = document.createElement("tr");

            addCell(row, event.event_id);
            addCell(row, event.event_name);
            addCell(row, event.event_date);
            addCell(row, event.venue);
            addCell(row, event.category);
            addCell(row, event.status);

            const actions = document.createElement("td");

            const editButton = document.createElement("button");
            editButton.type = "button";
            editButton.textContent = "Edit";
            editButton.dataset.action = "edit";
            editButton.dataset.id = event.event_id;
            editButton.disabled = busy;

            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.textContent = "Delete";
            deleteButton.dataset.action = "delete";
            deleteButton.dataset.id = event.event_id;
            deleteButton.disabled = busy;

            actions.append(
                editButton,
                document.createTextNode(" "),
                deleteButton
            );

            row.appendChild(actions);
            tableBody.appendChild(row);
        });
    }

    async function loadEvents() {
        if (listController) {
            listController.abort();
        }

        listController = new AbortController();

        const requestNumber = ++listRequestNumber;

        listStatus.textContent = "Loading events...";

        try {
            const query = new URLSearchParams({
                search: search.value.trim()
            });

            const result = await apiRequest(
                "../php/api/events_list.php?" + query,
                { signal: listController.signal }
            );

            if (requestNumber !== listRequestNumber) {
                return;
            }

            renderEvents(result.data);

            if (result.data.length === 0) {
                listStatus.textContent = "No events found.";
            } else {
                listStatus.textContent =
                    "Matching events: " + result.data.length;
            }

        } catch (error) {
            if (
                error.name === "AbortError" ||
                requestNumber !== listRequestNumber
            ) {
                return;
            }

            tableBody.replaceChildren();
            eventsById.clear();
            listStatus.textContent = error.message;
        }
    }

    function postOptions(data) {
        return {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken
            },
            body: JSON.stringify(data)
        };
    }

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (busy || !form.reportValidity()) {
            return;
        }

        const data = {
            event_id: Number(eventId.value),
            event_name: eventName.value.trim(),
            event_date: eventDate.value,
            venue: eventVenue.value.trim(),
            category: eventCategory.value.trim(),
            status: eventStatus.value
        };

        clearTimeout(searchTimer);
        setBusy(true);
        message.textContent = "Saving event...";

        try {
            const result = await apiRequest(
                "../php/api/events_save.php",
                postOptions(data)
            );

            clearForm();
            message.textContent = result.message;

            await loadEvents();

        } catch (error) {
            message.textContent = error.message;

        } finally {
            setBusy(false);
        }
    });

    tableBody.addEventListener("click", async (event) => {
        const button = event.target.closest("button[data-action]");

        if (!button || busy) {
            return;
        }

        const id = Number(button.dataset.id);
        const selectedEvent = eventsById.get(id);

        if (!selectedEvent) {
            return;
        }

        if (button.dataset.action === "edit") {
            eventId.value = selectedEvent.event_id;
            eventName.value = selectedEvent.event_name;
            eventDate.value = selectedEvent.event_date;
            eventVenue.value = selectedEvent.venue;
            eventCategory.value = selectedEvent.category;
            eventStatus.value = selectedEvent.status;

            formTitle.textContent = "Edit Event";
            message.textContent = "Edit the details and click Save Event.";

            eventName.focus();
            return;
        }

        const confirmed = window.confirm(
            'Permanently delete "' +
            selectedEvent.event_name +
            '" and its poster?'
        );

        if (!confirmed) {
            return;
        }

        clearTimeout(searchTimer);
        setBusy(true);
        message.textContent = "Deleting event...";

        try {
            const result = await apiRequest(
                "../php/api/events_delete.php",
                postOptions({
                    event_id: id,
                    confirmed: true
                })
            );

            if (Number(eventId.value) === id) {
                clearForm();
            }

            message.textContent = result.message;

            await loadEvents();

        } catch (error) {
            message.textContent = error.message;

        } finally {
            setBusy(false);
        }
    });

    document.getElementById("cancelEventButton")
        .addEventListener("click", () => {
            clearForm();
            message.textContent = "";
        });

    search.addEventListener("input", () => {
        clearTimeout(searchTimer);

        // Wait until typing pauses before requesting results.
        searchTimer = setTimeout(loadEvents, 350);
    });

    refreshButton.addEventListener("click", () => {
        clearTimeout(searchTimer);
        loadEvents();
    });

    loadEvents();
})();