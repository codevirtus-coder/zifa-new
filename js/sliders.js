document.addEventListener('DOMContentLoaded', function () {
    const swiperHomeElement = document.querySelector('.swiperHome');   
    if (swiperHomeElement) {
        const swiperHome = new Swiper('.swiperHome', {
            // Optional parameters
            direction: 'horizontal',
            loop: true,

            // If we need pagination
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
        });
    }



    const zifaPartnersElement = document.querySelector('.zifaPartners');   
    if (zifaPartnersElement) {
        const zifaPartners = new Swiper('.zifaPartners', {
            // Optional parameters
            direction: 'horizontal',
            loop: true,

            // Number of Slides
            slidesPerView: 3,
            spaceBetween: 20,
            breakpoints: {
                // when window width is >= 768px
                768: {
                    slidesPerView: 5,
                    // spaceBetween: 40
                }
            },
            freeMode: true, // enables momenttum-based free scrolling
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },
            speed: 3000,
            freeModeMomentum: false, // disables momentum to keep contant speed
        });
    }
});