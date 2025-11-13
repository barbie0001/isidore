document.addEventListener('DOMContentLoaded', function() {
    const carouselContainer = document.querySelector('.carousel-container');
    const slides = document.querySelectorAll('.carousel-slide');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const indicators = document.querySelectorAll('.indicator');
    
    let currentSlide = 0;
    const totalSlides = slides.length;

    // Функция для загрузки фоновых изображений
    function loadBackgroundImages() {
        slides.forEach((slide, index) => {
            const bgImage = slide.getAttribute('data-bg');
            if (bgImage) {
                // Создаем новое изображение для предзагрузки
                const img = new Image();
                img.src = bgImage;
                img.onload = function() {
                    // Устанавливаем фон только когда изображение загружено
                    slide.style.backgroundImage = `url('${bgImage}')`;
                    
                    // Добавляем класс для плавного появления
                    slide.classList.add('bg-loaded');
                };
                img.onerror = function() {
                    console.warn(`Не удалось загрузить изображение: ${bgImage}`);
                    // Устанавливаем цвет фона по умолчанию если изображение не загружено
                    slide.style.backgroundColor = index === 0 ? '#8B7355' : '#6B8E23';
                };
            }
        });
    }

    function updateCarousel() {
        carouselContainer.style.transform = `translateX(-${currentSlide * 50}%)`;
        
        // Обновление индикаторов
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSlide);
        });
        
        // Обновление слайдов
        slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === currentSlide);
        });
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateCarousel();
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateCarousel();
    }
    
    // Инициализация
    loadBackgroundImages();
    
    // События для стрелок
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    // События для индикаторов
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentSlide = index;
            updateCarousel();
        });
    });
    
    // Автопереключение (опционально)
    let autoSlideInterval = setInterval(nextSlide, 5000);
    
    // Остановка автопереключения при наведении
    carouselContainer.addEventListener('mouseenter', () => {
        clearInterval(autoSlideInterval);
    });
    
    carouselContainer.addEventListener('mouseleave', () => {
        autoSlideInterval = setInterval(nextSlide, 5000);
    });
    
    // Поддержка свайпов на мобильных устройствах
    let startX = 0;
    let endX = 0;
    
    carouselContainer.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
    });
    
    carouselContainer.addEventListener('touchend', (e) => {
        endX = e.changedTouches[0].clientX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = startX - endX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                nextSlide(); // Свайп влево
            } else {
                prevSlide(); // Свайп вправо
            }
        }
    }
});