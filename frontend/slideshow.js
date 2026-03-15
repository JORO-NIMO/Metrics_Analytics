// Slideshow
(function () {
    const slides = document.querySelectorAll('.slide');
    if (!slides.length) return;
    let current = 0;

    function showSlide(index) {
        slides.forEach(s => s.className = 'slide');
        const total = slides.length;
        slides[index].classList.add('active-slide');
        slides[(index - 1 + total) % total].classList.add('left-slide');
        slides[(index + 1) % total].classList.add('right-slide');
    }

    showSlide(current);

    document.querySelector('.slide-btn.next')?.addEventListener('click', () => {
        current = (current + 1) % slides.length;
        showSlide(current);
    });
    document.querySelector('.slide-btn.prev')?.addEventListener('click', () => {
        current = (current - 1 + slides.length) % slides.length;
        showSlide(current);
    });

    setInterval(() => {
        current = (current + 1) % slides.length;
        showSlide(current);
    }, 5000);
})();
