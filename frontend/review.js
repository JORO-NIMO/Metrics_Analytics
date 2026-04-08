// Review slider
(function () {
    const slides = document.querySelectorAll('.review-slide');
    if (!slides.length) return;
    let current = 0;

    function show(index) {
        slides.forEach(s => s.classList.remove('active'));
        slides[index].classList.add('active');
    }

    document.querySelector('.controls .next')?.addEventListener('click', () => {
        current = (current + 1) % slides.length;
        show(current);
    });
    document.querySelector('.controls .prev')?.addEventListener('click', () => {
        current = (current - 1 + slides.length) % slides.length;
        show(current);
    });

    setInterval(() => {
        current = (current + 1) % slides.length;
        show(current);
    }, 6000);
})();
