// ==========================================
// PRACTICAL 6
// FETCH API, SEARCH, FILTER, SORT, PAGINATION
// ==========================================


// ==========================================
// EVENTS
// ==========================================

const eventList = document.getElementById("eventList");

if (eventList) {
    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;

    const recordsPerPage = 5;

    const eventSearch = document.getElementById("eventSearch");
    const categoryFilter = document.getElementById("categoryFilter");
    const eventSort = document.getElementById("eventSort");
    const previousPage = document.getElementById("previousPage");
    const nextPage = document.getElementById("nextPage");
    const pageInfo = document.getElementById("pageInfo");

    eventList.setAttribute("aria-live", "polite");

    previousPage.disabled = true;
    nextPage.disabled = true;

    function showEventMessage(text, isError = false) {
        eventList.replaceChildren();

        const paragraph = document.createElement("p");
        paragraph.textContent = text;

        if (isError) {
            paragraph.className = "error-message";
        }

        eventList.appendChild(paragraph);
    }

    function addEventDetail(card, label, value) {
        const paragraph = document.createElement("p");
        const heading = document.createElement("strong");

        heading.textContent = label + ": ";

        paragraph.append(
            heading,
            document.createTextNode(String(value))
        );

        card.appendChild(paragraph);
    }

    function renderEvents() {
        eventList.replaceChildren();

        if (filteredEvents.length === 0) {
            showEventMessage("No events found.");

            pageInfo.textContent = "Page 0 of 0";
            previousPage.disabled = true;
            nextPage.disabled = true;

            return;
        }

        const totalPages = Math.ceil(
            filteredEvents.length / recordsPerPage
        );

        currentPage = Math.max(
            1,
            Math.min(currentPage, totalPages)
        );

        const start = (currentPage - 1) * recordsPerPage;

        const pageEvents = filteredEvents.slice(
            start,
            start + recordsPerPage
        );

        pageEvents.forEach(function (event) {
            const card = document.createElement("article");
            card.className = "data-card";

            const title = document.createElement("h3");
            title.textContent = event.title;

            card.appendChild(title);

            if (
                typeof event.poster_filename === "string" &&
                /^[a-f0-9]{32}\.(jpg|png|webp)$/.test(
                    event.poster_filename
                )
            ) {
                const image = document.createElement("img");

                image.src =
                    "../uploads/posters/" + event.poster_filename;

                image.alt = event.title + " poster";
                image.loading = "lazy";
                image.style.maxWidth = "100%";
                image.style.height = "auto";

                card.appendChild(image);
            }

            addEventDetail(card, "Category", event.category);
            addEventDetail(card, "Date", event.date);

            let statusLabel = "Closed";

            if (event.status === "open") {
                statusLabel = "Open";
            }

            addEventDetail(card, "Status", statusLabel);

            const description = document.createElement("p");
            description.textContent = event.description;

            card.appendChild(description);
            eventList.appendChild(card);
        });

        pageInfo.textContent =
            "Page " + currentPage + " of " + totalPages;

        previousPage.disabled = currentPage === 1;
        nextPage.disabled = currentPage === totalPages;
    }

    function updateEvents() {
        const searchValue = eventSearch.value
            .trim()
            .toLowerCase();

        const categoryValue = categoryFilter.value;

        filteredEvents = allEvents.filter(function (event) {
            const searchableText = [
                event.title,
                event.category,
                event.description
            ].join(" ").toLowerCase();

            const searchMatch =
                searchableText.includes(searchValue);

            const categoryMatch =
                categoryValue === "All" ||
                event.category === categoryValue;

            return searchMatch && categoryMatch;
        });

        if (eventSort.value === "az") {
            filteredEvents.sort(function (a, b) {
                return a.title.localeCompare(b.title);
            });

        } else if (eventSort.value === "za") {
            filteredEvents.sort(function (a, b) {
                return b.title.localeCompare(a.title);
            });

        } else if (eventSort.value === "date") {
            filteredEvents.sort(function (a, b) {
                return a.date.localeCompare(b.date);
            });
        }

        currentPage = 1;
        renderEvents();
    }

    function loadCategoryOptions() {
        const selectedCategory = categoryFilter.value;

        categoryFilter.replaceChildren();

        const allOption = document.createElement("option");
        allOption.value = "All";
        allOption.textContent = "All Categories";

        categoryFilter.appendChild(allOption);

        const categories = [
            ...new Set(allEvents.map(function (event) {
                return event.category;
            }))
        ].sort(function (a, b) {
            return a.localeCompare(b);
        });

        categories.forEach(function (category) {
            const option = document.createElement("option");

            option.value = category;
            option.textContent = category;

            categoryFilter.appendChild(option);
        });

        if (categories.includes(selectedCategory)) {
            categoryFilter.value = selectedCategory;
        }
    }

    eventSearch.addEventListener("input", updateEvents);
    categoryFilter.addEventListener("change", updateEvents);
    eventSort.addEventListener("change", updateEvents);

    previousPage.addEventListener("click", function () {
        if (currentPage > 1) {
            currentPage--;
            renderEvents();
        }
    });

    nextPage.addEventListener("click", function () {
        const totalPages = Math.ceil(
            filteredEvents.length / recordsPerPage
        );

        if (currentPage < totalPages) {
            currentPage++;
            renderEvents();
        }
    });

    async function loadPublicEvents() {
        showEventMessage("Loading events...");

        try {
            const response = await fetch(
                "../php/api/public_events.php",
                { cache: "no-store" }
            );

            const result = await response.json();

            if (
                !response.ok ||
                result.success !== true ||
                !Array.isArray(result.data)
            ) {
                throw new Error(
                    result.message || "Unable to load events."
                );
            }

            allEvents = result.data;

            loadCategoryOptions();
            updateEvents();

        } catch (error) {
            allEvents = [];
            filteredEvents = [];

            showEventMessage(
                "Unable to load events. Please refresh and try again.",
                true
            );

            pageInfo.textContent = "Events unavailable";
            previousPage.disabled = true;
            nextPage.disabled = true;

            console.error("Public events error:", error);
        }
    }

    loadPublicEvents();
}
// ==========================================
// STUDENTS
// ==========================================

const studentList =
    document.getElementById("studentList");


if (studentList) {

    let allStudents = [];


    const studentSearch =
        document.getElementById(
            "studentSearch"
        );


    const courseFilter =
        document.getElementById(
            "courseFilter"
        );


    const studentSort =
        document.getElementById(
            "studentSort"
        );


    // SHOW LOADING

    studentList.innerHTML =
        "<p class='data-message'>Loading students...</p>";


    // FETCH STUDENT JSON

    fetch("../data/students.json")

        .then(function (response) {

            if (!response.ok) {

                throw new Error(
                    "Could not load students."
                );

            }

            return response.json();

        })


        .then(function (data) {

            allStudents = data;

            console.log(
                "Students loaded:",
                allStudents
            );

            renderStudents(allStudents);

        })


        .catch(function (error) {

            studentList.innerHTML =
                "<p class='error-message'>Error loading students.</p>";

            console.error(
                "Student JSON Error:",
                error
            );

        });


    // ======================================
    // RENDER STUDENTS
    // ======================================

    function renderStudents(students) {

        studentList.innerHTML = "";


        if (students.length === 0) {

            studentList.innerHTML =
                "<p class='data-message'>No students found.</p>";

            return;

        }


        studentList.innerHTML =
            students.map(
                function (student) {

                    return `
                        <article class="student-card">

                            <h3>
                                ${student.name}
                            </h3>

                            <p>
                                <strong>
                                    Course:
                                </strong>

                                ${student.course}
                            </p>

                            <p>
                                <strong>
                                    Year:
                                </strong>

                                ${student.year}
                            </p>

                        </article>
                    `;

                }
            ).join("");

    }


    // ======================================
    // SEARCH + FILTER + SORT
    // ======================================

    function updateStudents() {

        const searchValue =
            studentSearch.value
                .toLowerCase()
                .trim();


        const courseValue =
            courseFilter.value;


        // SEARCH AND FILTER

        let result =
            allStudents.filter(
                function (student) {

                    const nameMatch =
                        student.name
                            .toLowerCase()
                            .includes(
                                searchValue
                            );


                    const courseMatch =
                        courseValue === "All" ||
                        student.course ===
                        courseValue;


                    return (
                        nameMatch &&
                        courseMatch
                    );

                }
            );


        // NAME A-Z

        if (studentSort.value === "az") {

            result.sort(
                function (a, b) {

                    return a.name.localeCompare(
                        b.name
                    );

                }
            );

        }


        // NAME Z-A

        if (studentSort.value === "za") {

            result.sort(
                function (a, b) {

                    return b.name.localeCompare(
                        a.name
                    );

                }
            );

        }


        // YEAR LOW-HIGH

        if (
            studentSort.value ===
            "yearLow"
        ) {

            result.sort(
                function (a, b) {

                    return a.year - b.year;

                }
            );

        }


        // YEAR HIGH-LOW

        if (
            studentSort.value ===
            "yearHigh"
        ) {

            result.sort(
                function (a, b) {

                    return b.year - a.year;

                }
            );

        }


        renderStudents(result);

    }


    // SEARCH EVENT

    studentSearch.addEventListener(
        "input",
        updateStudents
    );


    // FILTER EVENT

    courseFilter.addEventListener(
        "change",
        updateStudents
    );


    // SORT EVENT

    studentSort.addEventListener(
        "change",
        updateStudents
    );

}


// ==========================================
// FAQs
// ==========================================

const dynamicFaqList =
    document.getElementById(
        "dynamicFaqList"
    );


if (dynamicFaqList) {

    let allFaqs = [];


    const faqSearch =
        document.getElementById(
            "faqSearch"
        );


    dynamicFaqList.innerHTML =
        "<p class='data-message'>Loading FAQs...</p>";


    // FETCH FAQ JSON

    fetch("../data/faqs.json")

        .then(function (response) {

            if (!response.ok) {

                throw new Error(
                    "Could not load FAQs."
                );

            }

            return response.json();

        })


        .then(function (data) {

            allFaqs = data;

            renderFaqs(allFaqs);

        })


        .catch(function (error) {

            dynamicFaqList.innerHTML =
                "<p class='error-message'>Error loading FAQs.</p>";

            console.error(error);

        });


    // RENDER FAQ

    function renderFaqs(faqs) {

        if (faqs.length === 0) {

            dynamicFaqList.innerHTML =
                "<p>No FAQs found.</p>";

            return;

        }


        dynamicFaqList.innerHTML =
            faqs.map(
                function (faq) {

                    return `
                        <div class="dynamic-faq">

                            <button
                                class="dynamic-faq-question"
                                type="button">

                                ${faq.question}

                            </button>

                            <div
                                class="dynamic-faq-answer">

                                <p>
                                    ${faq.answer}
                                </p>

                            </div>

                        </div>
                    `;

                }
            ).join("");


        // FAQ CLICK

        const questions =
            document.querySelectorAll(
                ".dynamic-faq-question"
            );


        questions.forEach(
            function (question) {

                question.addEventListener(
                    "click",
                    function () {

                        const answer =
                            question
                                .nextElementSibling;


                        answer.classList.toggle(
                            "show-answer"
                        );

                    }
                );

            }
        );

    }


    // FAQ SEARCH

    faqSearch.addEventListener(
        "input",
        function () {

            const searchValue =
                faqSearch.value
                    .toLowerCase()
                    .trim();


            const result =
                allFaqs.filter(
                    function (faq) {

                        return (
                            faq.question
                                .toLowerCase()
                                .includes(
                                    searchValue
                                ) ||

                            faq.answer
                                .toLowerCase()
                                .includes(
                                    searchValue
                                )
                        );

                    }
                );


            renderFaqs(result);

        }
    );

}