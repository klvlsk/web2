$(document).ready(function () {
    var $slider = $('.slick-slider');

    $slider.slick({
        adaptiveHeight: true,
        fade: true,
        arrows: false,
        dots: false,
        lazyLoad: 'ondemand',
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1

    });

    function updateSlideCounter(currentIndex, totalSlides) {
        var current = (currentIndex + 1).toString().padStart(2, '0');
        var total = totalSlides.toString().padStart(2, '0');

        var counterHTML = `<span class="slide-num-digit">${current[0]}</span><span class="slide-num-digit">${current[1]}</span> / <span class="slide-num-digit">${total[0]}</span><span class="slide-num-digit">${total[1]}</span>`;
        $('.slide-num').html(counterHTML);
    }

    var totalSlides = $slider.slick('getSlick').slideCount;
    updateSlideCounter(0, totalSlides);

    $slider.on('afterChange', function (event, slick, currentSlide) {
        updateSlideCounter(currentSlide, totalSlides);
    });

    $('.slick-prev').click(() => $slider.slick('slickPrev'));
    $('.slick-next').click(() => $slider.slick('slickNext'));
});