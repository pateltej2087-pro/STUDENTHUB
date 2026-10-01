// ==========================================
// PRACTICAL 6
// FETCH API, SEARCH, FILTER, SORT, PAGINATION
// ==========================================


// ==========================================
// EVENTS
// ==========================================

const eventList =
    document.getElementById("eventList");


if (eventList) {

    let allEvents = [];
    let filteredEvents = [];

    let currentPage = 1;

    const recordsPerPage = 5;


    const eventSearch =
        document.getElementById("eventSearch");

    const categoryFilter =
        document.getElementById("categoryFilter");

    const eventSort =
        document.getElementById("eventSort");

    const previousPage =
        document.getElementById("previousPage");

    const nextPage =
        document.getElementById("nextPage");

    const pageInfo =
        document.getElementById("pageInfo");


    // FETCH JSON

    fetch("../data/events.json")

        .then(function (response) {

            if (!response.ok) {

                throw new Error(
                    "Could not load events."
                );

            }

            return response.json();

        })

        .then(function (data) {

            allEvents = data;

            filteredEvents = [...data];

            renderEvents();

        })

        .catch(function (error) {

            eventList.innerHTML =
                "<p class='error-message'>Error loading events.</p>";

            console.error(error);

        });


    // RENDER EVENTS

    function renderEvents() {

        eventList.innerHTML = "";


        if (filteredEvents.length === 0) {

            eventList.innerHTML =
                "<p>No events found.</p>";

            pageInfo.textContent =
                "Page 0 of 0";

            previousPage.disabled = true;

            nextPage.disabled = true;

            return;

        }


        const totalPages =
            Math.ceil(
                filteredEvents.length /
                recordsPerPage
            );


        if (currentPage > totalPages) {

            currentPage = totalPages;

        }


        const start =
            (currentPage - 1) *
            recordsPerPage;


        const end =
            start + recordsPerPage;


        // PAGINATION

        const pageEvents =
            filteredEvents.slice(
                start,
                end
            );


        // MAP AND RENDER

        eventList.innerHTML =
            pageEvents.map(
                function (event) {

                    return `
                        <article class="data-card">

                            <h3>
                                ${event.title}
                            </h3>

                            <p>
                                <strong>
                                    Category:
                                </strong>

                                ${event.category}
                            </p>

                            <p>
                                <strong>
                                    Date:
                                </strong>

                                ${event.date}
                            </p>

                            <p>
                                ${event.description}
                            </p>

                        </article>
                    `;

                }
            ).join("");


        pageInfo.textContent =
            "Page " +
            currentPage +
            " of " +
            totalPages;


        previousPage.disabled =
            currentPage === 1;


        nextPage.disabled =
            currentPage === totalPages;

    }


    // SEARCH + FILTER + SORT

    function updateEvents() {

        const searchValue =
            eventSearch.value
                .toLowerCase()
                .trim();


        const categoryValue =
            categoryFilter.value;


        filteredEvents =
            allEvents.filter(
                function (event) {

                    const searchMatch =
                        event.title
                            .toLowerCase()
                            .includes(
                                searchValue
                            );


                    const categoryMatch =
                        categoryValue === "All" ||
                        event.category ===
                        categoryValue;


                    return (
                        searchMatch &&
                        categoryMatch
                    );

                }
            );


        // A-Z

        if (eventSort.value === "az") {

            filteredEvents.sort(
                function (a, b) {

                    return a.title.localeCompare(
                        b.title
                    );

                }
            );

        }


        // Z-A

        if (eventSort.value === "za") {

            filteredEvents.sort(
                function (a, b) {

                    return b.title.localeCompare(
                        a.title
                    );

                }
            );

        }


        // DATE

        if (eventSort.value === "date") {

            filteredEvents.sort(
                function (a, b) {

                    return (
                        new Date(a.date) -
                        new Date(b.date)
                    );

                }
            );

        }


        currentPage = 1;

        renderEvents();

    }


    eventSearch.addEventListener(
        "input",
        updateEvents
    );


    categoryFilter.addEventListener(
        "change",
        updateEvents
    );


    eventSort.addEventListener(
        "change",
        updateEvents
    );


    // PREVIOUS PAGE

    previousPage.addEventListener(
        "click",
        function () {

            if (currentPage > 1) {

                currentPage--;

                renderEvents();

            }

        }
    );


    // NEXT PAGE

    nextPage.addEventListener(
        "click",
        function () {

            const totalPages =
                Math.ceil(
                    filteredEvents.length /
                    recordsPerPage
                );


            if (currentPage < totalPages) {

                currentPage++;

                renderEvents();

            }

        }
    );

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