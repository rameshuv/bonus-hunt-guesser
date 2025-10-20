document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bhg-tabs a').forEach(function (tab) {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            document.querySelectorAll('.bhg-tabs li').forEach(function (li) {
                li.classList.remove('active');
            });
            document.querySelectorAll('.bhg-tab-pane').forEach(function (pane) {
                pane.classList.remove('active');
            });
            this.parentElement.classList.add('active');
            var target = document.getElementById(targetId);
            if (target) {
                target.classList.add('active');
            }
        });
    });

    var huntSelect = document.querySelector('.bhg-hunt-select');
    if (huntSelect && huntSelect.form) {
        huntSelect.addEventListener('change', function () {
            var pageInputs = huntSelect.form.querySelectorAll('[name="bhg_hunt_page"]');
            if (pageInputs.length) {
                pageInputs.forEach(function (input) {
                    input.parentNode.removeChild(input);
                });
            }
            huntSelect.form.submit();
        });
    }

    document.querySelectorAll('.bhg-prize-carousel').forEach(function (carousel) {
        var track = carousel.querySelector('.bhg-prize-track');
        if (!track) {
            return;
        }

        var cards = track.querySelectorAll('.bhg-prize-card');
        if (!cards.length) {
            return;
        }

        var dots = carousel.querySelectorAll('.bhg-prize-dot');
        var prevButton = carousel.querySelector('.bhg-prize-prev');
        var nextButton = carousel.querySelector('.bhg-prize-next');
        var currentIndex = 0;

        function updateCarousel(index) {
            if (!track) {
                return;
            }
            currentIndex = index;
            track.style.transform = 'translateX(' + (-100 * currentIndex) + '%)';
            dots.forEach(function (dot, dotIndex) {
                if (dotIndex === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                var newIndex = currentIndex - 1;
                if (newIndex < 0) {
                    newIndex = cards.length - 1;
                }
                updateCarousel(newIndex);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                var newIndex = currentIndex + 1;
                if (newIndex >= cards.length) {
                    newIndex = 0;
                }
                updateCarousel(newIndex);
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var targetIndex = parseInt(dot.getAttribute('data-index'), 10);
                if (!isNaN(targetIndex)) {
                    updateCarousel(targetIndex);
                }
            });
        });

        updateCarousel(0);
    });

    document.querySelectorAll('.bhg-prize-layout-toggle').forEach(function (toggle) {
        var buttons = toggle.querySelectorAll('button[data-layout]');
        if (!buttons.length) {
            return;
        }

        var block = toggle.closest('.bhg-prizes-block');
        if (!block) {
            return;
        }

        var views = block.querySelectorAll('.bhg-prize-layout-view');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var targetLayout = button.getAttribute('data-layout');
                if (!targetLayout) {
                    return;
                }

                buttons.forEach(function (btn) {
                    var isActive = btn === button;
                    btn.classList.toggle('active', isActive);
                    btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                views.forEach(function (view) {
                    var matches = view.getAttribute('data-layout') === targetLayout;
                    if (matches) {
                        view.removeAttribute('hidden');
                        view.classList.add('is-active');
                    } else {
                        view.setAttribute('hidden', 'hidden');
                        view.classList.remove('is-active');
                    }
                });

                block.classList.remove('bhg-prizes-layout-grid', 'bhg-prizes-layout-carousel');
                block.classList.add('bhg-prizes-layout-' + targetLayout);
            });
        });
    });
});
