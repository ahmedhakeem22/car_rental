document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-section .hero-slide');
    let currentSlide = 0;
    const slideInterval = 5000; // الوقت بالمللي ثانية (5 ثوانٍ)

    if (slides.length > 0) {
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                }
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        // إظهار الشريحة الأولى فورًا
        showSlide(currentSlide);

        // بدء التبديل التلقائي إذا كان هناك أكثر من شريحة واحدة
        if (slides.length > 1) {
            setInterval(nextSlide, slideInterval);
        }
    }
});