// review-slideshow.js

document.addEventListener('DOMContentLoaded', function() {
    // Get all review slides
    const slides = document.querySelectorAll('.review-slide');
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');
    
    let currentSlide = 0;
    let slideInterval;
    
    // Function to show a specific slide
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });
        
        // Show the selected slide
        slides[index].classList.add('active');
    }
    
    // Function to go to next slide
    function nextSlide() {
        currentSlide++;
        if (currentSlide >= slides.length) {
            currentSlide = 0; // Loop back to first slide
        }
        showSlide(currentSlide);
    }
    
    // Function to go to previous slide
    function prevSlide() {
        currentSlide--;
        if (currentSlide < 0) {
            currentSlide = slides.length - 1; // Loop to last slide
        }
        showSlide(currentSlide);
    }
    
    // Function to start auto-sliding
    function startAutoSlide() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }
    
    // Function to stop auto-sliding
    function stopAutoSlide() {
        clearInterval(slideInterval);
    }
    
    // Add click event listeners to buttons
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            stopAutoSlide();
            nextSlide();
            startAutoSlide(); // Restart auto-slide after manual navigation
        });
    }
    
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            stopAutoSlide();
            prevSlide();
            startAutoSlide(); // Restart auto-slide after manual navigation
        });
    }
    
    // Pause auto-slide when hovering over reviews
    const reviewSection = document.getElementById('review-section');
    if (reviewSection) {
        reviewSection.addEventListener('mouseenter', stopAutoSlide);
        reviewSection.addEventListener('mouseleave', startAutoSlide);
    }
    
    // Start the slideshow
    if (slides.length > 0) {
        showSlide(0); // Show first slide
        startAutoSlide();
    }
});