const img = document.querySelector('.parallax-city');
if (img) {
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        const factor = 0.35; // скорость движения
        img.style.transform = `translate(-50%, -${scrolled * factor}px)`;
    });
}
