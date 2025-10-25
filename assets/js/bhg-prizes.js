(function () {
	'use strict';

	function updateDots(dots, index) {
		if (!dots) {
			return;
		}

		dots.forEach(function (dot, dotIndex) {
			dot.classList.toggle('active', dotIndex === index);
		});
	}

	function setSlideDimensions(track, slides, wrapperWidth) {
		slides.forEach(function (slide) {
			slide.style.flex = '0 0 ' + wrapperWidth + 'px';
			slide.style.maxWidth = wrapperWidth + 'px';
		});
		track.style.width = slides.length * wrapperWidth + 'px';
	}

	function initCarousel(container) {
		if (!container || container.dataset.bhgPrizeCarouselInit) {
			return;
		}

		var track = container.querySelector('.bhg-prize-track');
		var wrapper = container.querySelector('.bhg-prize-track-wrapper');
		var slides = track ? Array.prototype.slice.call(track.querySelectorAll('.bhg-prize-card')) : [];

		if (!track || !wrapper || slides.length <= 1) {
			return;
		}

		container.dataset.bhgPrizeCarouselInit = '1';

		var dots = Array.prototype.slice.call(container.querySelectorAll('.bhg-prize-dot'));
		var prev = container.querySelector('.bhg-prize-prev');
		var next = container.querySelector('.bhg-prize-next');
		var index = 0;

		function update() {
			var width = wrapper.clientWidth;
			if (width <= 0) {
				return;
			}

			setSlideDimensions(track, slides, width);
			track.style.transform = 'translateX(' + (index * -width) + 'px)';
			updateDots(dots, index);
		}

		function goTo(newIndex) {
			var total = slides.length;
			if (total <= 0) {
				return;
			}

			index = (newIndex + total) % total;
			update();
		}

		if (prev) {
			prev.addEventListener('click', function () {
				goTo(index - 1);
			});
		}

		if (next) {
			next.addEventListener('click', function () {
				goTo(index + 1);
			});
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function (event) {
				var target = event.currentTarget;
				var dotIndex = parseInt(target.getAttribute('data-index'), 10);
				if (!Number.isNaN(dotIndex)) {
					goTo(dotIndex);
				}
			});
		});

		window.addEventListener('resize', update);
		update();
	}

	function initAll() {
		var carousels = document.querySelectorAll('.bhg-prize-carousel');
		carousels.forEach(function (carousel) {
			initCarousel(carousel);
		});
	}

	if ('loading' !== document.readyState) {
		initAll();
	} else {
		document.addEventListener('DOMContentLoaded', initAll);
	}
})();
