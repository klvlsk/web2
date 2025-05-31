document.addEventListener('DOMContentLoaded', function() {
    const tariffWrappers = document.querySelectorAll('.tariff-wrapper');
    const centerIndex = Math.floor(tariffWrappers.length / 2);

    function resetEnlarged() {
        tariffWrappers.forEach(wrapper => wrapper.classList.remove('enlarged'));
    }

    function enlargeCenter() {
        if (window.innerWidth >= 768) {
            resetEnlarged();
            tariffWrappers[centerIndex].classList.add('enlarged');
        }
    }

    function handleHover(wrapper, isEntering) {
        if (window.innerWidth >= 768) {
            if (isEntering) {
                resetEnlarged();
                wrapper.classList.add('enlarged');
            }
        }
    }

    tariffWrappers.forEach((wrapper) => {
        wrapper.addEventListener('mouseenter', function() {
            handleHover(this, true);
        });
        wrapper.addEventListener('mouseleave', function() {
            handleHover(this, false);
        });
    });

    document.querySelector('.tariffs-row').addEventListener('mouseleave', function() {
        if (window.innerWidth >= 768) {
            enlargeCenter();
        }
    });

    function handleResize() {
        if (window.innerWidth >= 768) {
            enlargeCenter();
        } else {
            resetEnlarged();
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);
});