// main.js — Maternal Health Platform
// Updated to apply Measurement Theory (SENG 421 Ch.2) concepts:
//   - Emergency items displayed with BOTH ordinal (Low/Medium/High)
//     and ratio scale (numeric score 1-10) severity values.
//   - Items arrive pre-sorted by severity_score DESC from the API
//     (ratio scale ordering is more precise than ordinal alone).
//   - Measurement validation: form inputs validated before submission.
//   - Direct measurement: page views tracked on load.

// ================================================================
// SERVICES
// ================================================================
if (document.getElementById("services")) {
    fetch("../api/get_services.php")
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("services");
            data.forEach(item => {
                // service_category is a nominal-scale classification label
                container.innerHTML += `
                    <div class="service-card">
                        <div class="service-icon">&#x1F9BA;</div>
                        <div class="service-content">
                            <h3>${item.service_name}</h3>
                            <p>${item.description}</p>
                            <span class="service-category">${item.service_category}</span>
                        </div>
                    </div>
                `;
            });
        })
        .catch(() => {
            const container = document.getElementById("services");
            if (container) container.innerHTML = '<p class="error">Could not load services. Please try again later.</p>';
        });
}

// ================================================================
// EMERGENCY INFO
// Data arrives sorted by severity_score DESC (ratio scale sort).
// Each card shows:
//   - Ordinal label:  Low / Medium / High   (categorical ordering)
//   - Ratio score:    1–10                  (quantitative comparison)
// This dual representation follows the property-oriented
// measurement principle: same entity, two complementary scales.
// ================================================================
if (document.getElementById("emergency")) {
    fetch("../api/get_emergency.php")
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("emergency");

            data.forEach((item, index) => {
                // Map severity_score (ratio scale 1-10) to a visual bar width
                const barWidth = (item.severity_score / 10) * 100;

                container.innerHTML += `
                    <div class="emergency-card" data-index="${index}" data-severity="${item.severity.toLowerCase()}">
                        <div class="emergency-header">
                            <div class="emergency-icon">&#x26A0;&#xFE0F;</div>
                            <div class="emergency-title">
                                <h3>${item.title}</h3>
                                <p>${item.short_description}</p>
                                <div class="severity-display">
                                    <span class="severity ${item.severity.toLowerCase()}">${item.severity}</span>
                                    <span class="severity-score" title="Severity score (ratio scale 1-10)">
                                        Score: ${item.severity_score}/10
                                    </span>
                                </div>
                                <div class="severity-bar-track" title="Severity score visual">
                                    <div class="severity-bar-fill ${item.severity.toLowerCase()}"
                                         style="width: ${barWidth}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="emergency-details">
                            <p><strong>Details:</strong> ${item.detailed_description}</p>
                            <p><strong>Advice:</strong> ${item.advice}</p>
                            <a href="contact.html?emergency=${encodeURIComponent(item.title)}" class="btn">
                                Contact Support
                            </a>
                        </div>
                    </div>
                `;
            });

            // Toggle expanded view on card click
            document.querySelectorAll(".emergency-card").forEach(card => {
                card.addEventListener("click", e => {
                    if (!e.target.classList.contains('btn')) {
                        card.classList.toggle("expanded");
                    }
                });
            });
        })
        .catch(() => {
            const container = document.getElementById("emergency");
            if (container) container.innerHTML = '<p class="error">Could not load emergency information. Please try again later.</p>';
        });
}

// ================================================================
// CONTACT FORM
// Measurement validation: ensures all required attributes have
// non-empty values before submitting (invalid entity detection).
// ================================================================
if (document.getElementById("contactForm")) {
    const form = document.getElementById("contactForm");
    const responseDiv = document.getElementById("formResponse");

    form.addEventListener("submit", e => {
        e.preventDefault();

        // Client-side measurement validation
        const name    = form.querySelector('[name="name"]').value.trim();
        const phone   = form.querySelector('[name="phone"]').value.trim();
        const message = form.querySelector('[name="message"]').value.trim();
        const phonePattern = /^[+0-9\s\-]{7,20}$/;

        const validationErrors = [];
        if (!name)                      validationErrors.push("Name is required.");
        if (!phone)                     validationErrors.push("Phone is required.");
        if (phone && !phonePattern.test(phone)) validationErrors.push("Phone number format is invalid.");
        if (!message)                   validationErrors.push("Message is required.");

        if (validationErrors.length > 0) {
            responseDiv.innerHTML = validationErrors.map(e => `<p>${e}</p>`).join('');
            responseDiv.style.color = "red";
            return;
        }

        const formData = new FormData(form);
        fetch("../api/submit_contact.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    responseDiv.textContent = "Message sent successfully!";
                    responseDiv.style.color = "green";
                    form.reset();
                } else {
                    const errMsg = data.errors ? data.errors.join(", ") : "An error occurred.";
                    responseDiv.textContent = errMsg;
                    responseDiv.style.color = "red";
                }
            })
            .catch(() => {
                responseDiv.textContent = "Network error. Please try again.";
                responseDiv.style.color = "red";
            });
    });
}

// ================================================================
// AUTO-FILL EMERGENCY MESSAGE FROM URL PARAMETER
// ================================================================
const urlParams = new URLSearchParams(window.location.search);
const emergencyTitle = urlParams.get('emergency');
if (emergencyTitle) {
    const messageField = document.querySelector('#contactForm textarea[name="message"]');
    if (messageField) {
        messageField.value = `I need help regarding: ${emergencyTitle}\n\n`;
    }
}

// ================================================================
// SLIDESHOW
// ================================================================
const slides = document.querySelectorAll(".slide");
let currentSlide = 0;

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.remove("active-slide", "left-slide", "right-slide");
        if (i === index) {
            slide.classList.add("active-slide");
        } else if (i === (index - 1 + slides.length) % slides.length) {
            slide.classList.add("left-slide");
        } else if (i === (index + 1) % slides.length) {
            slide.classList.add("right-slide");
        }
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

if (slides.length > 0) {
    showSlide(currentSlide);
    setInterval(nextSlide, 4000);
}