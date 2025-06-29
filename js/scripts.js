// scripts.js
document.addEventListener('DOMContentLoaded', function () {
    // Animasi ketikan di hero section
    const heroH1 = document.querySelector('.hero h1:not(#ht1)');
    const heroHt1 = document.querySelector('.hero #ht1');
    if (heroH1 && heroHt1) {
        const lengthH1 = heroH1.textContent.length;
        const lengthHt1 = heroHt1.textContent.length;
        const timePerChar = 0.08;
        const durationH1 = lengthH1 * timePerChar;
        const durationHt1 = lengthHt1 * timePerChar;
        const pauseDuration = 1;
        const totalDuration = durationH1 + durationHt1 + pauseDuration;

        heroH1.style.animation = `typing ${durationH1}s steps(${lengthH1}, end) forwards, cursor-first ${totalDuration}s infinite`;
        heroHt1.style.animation = `typing ${durationHt1}s steps(${lengthHt1}, end) ${durationH1}s forwards, cursor-second ${totalDuration}s infinite`;

        const resetAnimation = () => {
            heroH1.style.width = '0';
            heroHt1.style.width = '0';
            heroH1.style.animation = 'none';
            heroHt1.style.animation = 'none';
            void heroH1.offsetWidth;
            void heroHt1.offsetWidth;
            heroH1.style.animation = `typing ${durationH1}s steps(${lengthH1}, end) forwards, cursor-first ${totalDuration}s infinite`;
            heroHt1.style.animation = `typing ${durationHt1}s steps(${lengthHt1}, end) ${durationH1}s forwards, cursor-second ${totalDuration}s infinite`;
        };

        const styleSheet = document.createElement('style');
        const cursorH1EndPercent = (durationH1 / totalDuration) * 100;
        styleSheet.innerHTML = `
            @keyframes cursor-first {
                0% { border-right: 2px solid #fff; }
                ${cursorH1EndPercent}% { border-right: 2px solid #fff; }
                ${cursorH1EndPercent + 0.1}% { border-right: 2px solid transparent; }
                100% { border-right: 2px solid transparent; }
            }
            @keyframes cursor-second {
                0% { border-right: 2px solid transparent; }
                ${cursorH1EndPercent}% { border-right: 2px solid transparent; }
                ${cursorH1EndPercent + 0.1}% { border-right: 2px solid #fff; }
                100% { border-right: 2px solid #fff; }
            }
        `;
        document.head.appendChild(styleSheet);

        setInterval(resetAnimation, totalDuration * 1000);
    }
    // Pesantren Carousel
    const imgSlider = document.querySelector('.img-slider');
    const imgItems = document.querySelectorAll('.img-item');
    const infoItems = document.querySelectorAll('.info-item');
    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');

    let index = 0;

    const slider = () => {
        if (imgSlider && imgItems.length > 0) {
            imgSlider.style.transform = `translateX(-${index * 33.33}%)`;

            document.querySelector('.img-item.active')?.classList.remove('active');
            imgItems[index].classList.add('active');

            document.querySelector('.info-item.active')?.classList.remove('active');
            infoItems[index].classList.add('active');
        }
    };

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            index++;
            if (index > imgItems.length - 1) {
                index = 0;
            }
            slider();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            index--;
            if (index < 0) {
                index = imgItems.length - 1;
            }
            slider();
        });
    }

    // Inisialisasi slider
    slider();
});