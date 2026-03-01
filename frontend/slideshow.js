document.addEventListener("DOMContentLoaded", () => {
    const slides = document.querySelectorAll(".slide");
    const nextBtn = document.querySelector(".next");
    const prevBtn = document.querySelector(".prev");

    let currentIndex = 0;

    function updateSlides() {
        slides.forEach((slide, index) => {
            slide.classList.remove("active-slide", "left-slide", "right-slide");
            if (index === currentIndex) slide.classList.add("active-slide");
            else if (index === (currentIndex - 1 + slides.length) % slides.length) slide.classList.add("left-slide");
            else if (index === (currentIndex + 1) % slides.length) slide.classList.add("right-slide");
        });
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlides();
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateSlides();
    }

    nextBtn.addEventListener("click", nextSlide);
    prevBtn.addEventListener("click", prevSlide);

    updateSlides();
    setInterval(nextSlide, 4000);
});