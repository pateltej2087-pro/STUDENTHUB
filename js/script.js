// ==========================================
// STUDENTHUB PRACTICAL 4
// JavaScript DOM Manipulation and Interactivity
// ==========================================


// ==========================================
// 1. HAMBURGER MENU
// ==========================================

const menuButton = document.getElementById("menuButton");
const mainNavigation = document.getElementById("mainNavigation");

if (menuButton && mainNavigation) {

    menuButton.addEventListener("click", function () {

        mainNavigation.classList.toggle("show-menu");

    });

}


// ==========================================
// 2. LIGHT / DARK THEME SWITCHER
// ==========================================

const themeButton = document.getElementById("themeButton");

if (themeButton) {

    themeButton.addEventListener("click", function () {

        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {

            localStorage.setItem("theme", "dark");
            themeButton.textContent = "☀️ Light Mode";

        } else {

            localStorage.setItem("theme", "light");
            themeButton.textContent = "🌙 Dark Mode";

        }

    });

}


// Restore saved theme when page loads

const savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {

    document.body.classList.add("dark-mode");

    if (themeButton) {
        themeButton.textContent = "☀️ Light Mode";
    }

}


// ==========================================
// 3. COLLAPSIBLE FAQ
// ==========================================

const faqQuestions = document.querySelectorAll(".faq-question");

faqQuestions.forEach(function (question) {

    question.addEventListener("click", function () {

        const answer = question.nextElementSibling;

        answer.classList.toggle("faq-visible");

        question.classList.toggle("faq-active");

    });

});


// ==========================================
// 4. MODAL POPUP
// ==========================================

const modal = document.getElementById("eventModal");
const modalTitle = document.getElementById("modalTitle");
const modalText = document.getElementById("modalText");
const closeModal = document.getElementById("closeModal");

const eventButtons = document.querySelectorAll(".event-button");

eventButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        const title = button.getAttribute("data-title");
        const description = button.getAttribute("data-description");

        if (modalTitle) {
            modalTitle.textContent = title;
        }

        if (modalText) {
            modalText.textContent = description;
        }

        if (modal) {
            modal.classList.add("modal-visible");
        }

    });

});

if (closeModal) {

    closeModal.addEventListener("click", function () {

        modal.classList.remove("modal-visible");

    });

}


// Close modal when clicking outside

if (modal) {

    modal.addEventListener("click", function (event) {

        if (event.target === modal) {

            modal.classList.remove("modal-visible");

        }

    });

}


// ==========================================
// 5. CONTENT SLIDER
// ==========================================

const slides = document.querySelectorAll(".slide");
const previousButton = document.getElementById("previousSlide");
const nextButton = document.getElementById("nextSlide");

let currentSlide = 0;


function displaySlide(index) {

    if (slides.length === 0) {
        return;
    }

    slides.forEach(function (slide) {

        slide.classList.remove("active-slide");

    });

    slides[index].classList.add("active-slide");

}


if (nextButton) {

    nextButton.addEventListener("click", function () {

        currentSlide++;

        if (currentSlide >= slides.length) {

            currentSlide = 0;

        }

        displaySlide(currentSlide);

    });

}


if (previousButton) {

    previousButton.addEventListener("click", function () {

        currentSlide--;

        if (currentSlide < 0) {

            currentSlide = slides.length - 1;

        }

        displaySlide(currentSlide);

    });

}


displaySlide(currentSlide);


// ==========================================
// 6. NOTIFICATION BANNER
// ==========================================

const notificationBanner = document.getElementById("notificationBanner");
const closeNotification = document.getElementById("closeNotification");

if (closeNotification) {

    closeNotification.addEventListener("click", function () {

        notificationBanner.classList.add("notification-hidden");

    });

}


// ==========================================
// 7. BUTTON CLICK FEEDBACK
// ==========================================

const actionButtons = document.querySelectorAll(".action-button");

actionButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        button.textContent = "Clicked ✓";

        setTimeout(function () {

            button.textContent = "Get Started";

        }, 1500);

    });

});


// ==========================================
// 8. PAGE LOAD MESSAGE
// ==========================================

console.log("StudentHub JavaScript loaded successfully.");
console.log("DOM manipulation and event handling are active.");
// ==========================================
// PRACTICAL 5
// REGISTRATION FORM VALIDATION
// ==========================================


const registrationForm =
    document.getElementById("registrationForm");


if (registrationForm) {


    // ======================================
    // GET FORM ELEMENTS
    // ======================================

    const fullName =
        document.getElementById("fullName");

    const email =
        document.getElementById("email");

    const mobile =
        document.getElementById("mobile");

    const password =
        document.getElementById("password");

    const confirmPassword =
        document.getElementById("confirmPassword");

    const course =
        document.getElementById("course");

    const year =
        document.getElementById("year");

    const terms =
        document.getElementById("terms");


    // ======================================
    // ERROR ELEMENTS
    // ======================================

    const nameError =
        document.getElementById("nameError");

    const emailError =
        document.getElementById("emailError");

    const mobileError =
        document.getElementById("mobileError");

    const passwordError =
        document.getElementById("passwordError");

    const confirmPasswordError =
        document.getElementById("confirmPasswordError");

    const courseError =
        document.getElementById("courseError");

    const yearError =
        document.getElementById("yearError");

    const genderError =
        document.getElementById("genderError");

    const termsError =
        document.getElementById("termsError");

    const successMessage =
        document.getElementById("successMessage");


    // ======================================
    // REGULAR EXPRESSIONS
    // ======================================


    // Name: letters and spaces only

    const nameRegex =
        /^[A-Za-z ]{3,50}$/;


    // Email validation

    const emailRegex =
        /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;


    // Indian mobile number

    const mobileRegex =
        /^[6-9][0-9]{9}$/;


    // Password:
    // minimum 8 characters
    // uppercase
    // lowercase
    // number
    // special character

    const passwordRegex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,72}$/;


    // ======================================
    // HELPER FUNCTIONS
    // ======================================


    function showError(input, errorElement, message) {

        errorElement.textContent = message;

        input.classList.remove("input-success");

        input.classList.add("input-error");

    }


    function showSuccess(input, errorElement) {

        errorElement.textContent = "";

        input.classList.remove("input-error");

        input.classList.add("input-success");

    }


    // ======================================
    // NAME VALIDATION
    // ======================================

    function validateName() {

        const value =
            fullName.value.trim();


        if (value === "") {

            showError(
                fullName,
                nameError,
                "Name is required."
            );

            return false;

        }


        if (!nameRegex.test(value)) {

            showError(
                fullName,
                nameError,
                "Enter a valid name using letters only."
            );

            return false;

        }


        showSuccess(
            fullName,
            nameError
        );

        return true;

    }


    // ======================================
    // EMAIL VALIDATION
    // ======================================

    function validateEmail() {

        const value =
            email.value.trim();


        if (value === "") {

            showError(
                email,
                emailError,
                "Email is required."
            );

            return false;

        }


        if (value.length > 254 || !emailRegex.test(value)) {

            showError(
                email,
                emailError,
                "Enter a valid email address."
            );

            return false;

        }


        showSuccess(
            email,
            emailError
        );

        return true;

    }


    // ======================================
    // MOBILE VALIDATION
    // ======================================

    function validateMobile() {

        const value =
            mobile.value.trim();


        if (value === "") {

            showError(
                mobile,
                mobileError,
                "Mobile number is required."
            );

            return false;

        }


        if (!mobileRegex.test(value)) {

            showError(
                mobile,
                mobileError,
                "Enter a valid 10 digit mobile number."
            );

            return false;

        }


        showSuccess(
            mobile,
            mobileError
        );

        return true;

    }


    // ======================================
    // PASSWORD VALIDATION
    // ======================================

    function validatePassword() {

        const value =
            password.value;


        if (value === "") {

            showError(
                password,
                passwordError,
                "Password is required."
            );

            return false;

        }


        if (!passwordRegex.test(value)) {

            showError(
                password,
                passwordError,
                "Password must contain 8 characters, uppercase, lowercase, number and special character."
            );

            return false;

        }


        showSuccess(
            password,
            passwordError
        );

        return true;

    }


    // ======================================
    // CONFIRM PASSWORD
    // ======================================

    function validateConfirmPassword() {

        const value =
            confirmPassword.value;


        if (value === "") {

            showError(
                confirmPassword,
                confirmPasswordError,
                "Confirm password is required."
            );

            return false;

        }


        if (value !== password.value) {

            showError(
                confirmPassword,
                confirmPasswordError,
                "Passwords do not match."
            );

            return false;

        }


        showSuccess(
            confirmPassword,
            confirmPasswordError
        );

        return true;

    }


    // ======================================
    // COURSE VALIDATION
    // ======================================

    function validateCourse() {

        if (course.value === "") {

            showError(
                course,
                courseError,
                "Please select a course."
            );

            return false;

        }


        showSuccess(
            course,
            courseError
        );

        return true;

    }


    // ======================================
    // YEAR VALIDATION
    // ======================================

    function validateYear() {

        if (year.value === "") {

            showError(
                year,
                yearError,
                "Please select your year."
            );

            return false;

        }


        showSuccess(
            year,
            yearError
        );

        return true;

    }


    // ======================================
    // GENDER VALIDATION
    // ======================================

    function validateGender() {

        const gender =
            document.querySelector(
                'input[name="gender"]:checked'
            );


        if (!gender) {

            genderError.textContent =
                "Please select your gender.";

            return false;

        }


        genderError.textContent = "";

        return true;

    }


    // ======================================
    // TERMS VALIDATION
    // ======================================

    function validateTerms() {

        if (!terms.checked) {

            termsError.textContent =
                "You must accept the terms and conditions.";

            return false;

        }


        termsError.textContent = "";

        return true;

    }


    // ======================================
    // PASSWORD STRENGTH
    // ======================================

    const strengthBar =
        document.getElementById(
            "passwordStrengthBar"
        );

    const strengthText =
        document.getElementById(
            "passwordStrengthText"
        );


    password.addEventListener(
        "input",
        function () {

            const value =
                password.value;


            strengthBar.className =
                "password-strength-bar";


            if (value.length === 0) {

                strengthText.textContent =
                    "Password Strength";

                return;

            }


            let strength = 0;


            if (value.length >= 8) {
                strength++;
            }


            if (
                /[A-Z]/.test(value) &&
                /[a-z]/.test(value)
            ) {
                strength++;
            }


            if (
                /\d/.test(value) &&
                /[@$!%*?&]/.test(value)
            ) {
                strength++;
            }


            if (strength === 1) {

                strengthBar.classList.add(
                    "strength-weak"
                );

                strengthText.textContent =
                    "Weak Password";

            }


            if (strength === 2) {

                strengthBar.classList.add(
                    "strength-medium"
                );

                strengthText.textContent =
                    "Medium Password";

            }


            if (strength === 3) {

                strengthBar.classList.add(
                    "strength-strong"
                );

                strengthText.textContent =
                    "Strong Password";

            }

        }
    );


    // ======================================
    // REAL-TIME VALIDATION
    // ======================================

    fullName.addEventListener(
        "blur",
        validateName
    );

    email.addEventListener(
        "blur",
        validateEmail
    );

    mobile.addEventListener(
        "blur",
        validateMobile
    );

    password.addEventListener(
        "blur",
        validatePassword
    );

    confirmPassword.addEventListener(
        "blur",
        validateConfirmPassword
    );


    // ======================================
    // FORM SUBMISSION
    // ======================================

    registrationForm.addEventListener(
    "submit",
    function (event) {

        const nameValid =
            validateName();

        const emailValid =
            validateEmail();

        const mobileValid =
            validateMobile();

        const passwordValid =
            validatePassword();

        const confirmValid =
            validateConfirmPassword();

        const courseValid =
            validateCourse();

        const yearValid =
            validateYear();

        const genderValid =
            validateGender();

        const termsValid =
            validateTerms();

        if (
            !nameValid ||
            !emailValid ||
            !mobileValid ||
            !passwordValid ||
            !confirmValid ||
            !courseValid ||
            !yearValid ||
            !genderValid ||
            !termsValid
        ) {
            event.preventDefault();
            successMessage.textContent = "";
        }
    }
);

}