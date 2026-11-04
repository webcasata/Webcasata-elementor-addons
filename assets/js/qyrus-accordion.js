(function ($) {
	"use strict";

	var NS = ".qyrusAccordion";

	function initQyrusAccordion($scope) {
		var $widget = $scope.find(".qyrus-accordion-widget");
		if (!$widget.length) {
			return;
		}

		$widget.each(function () {
			var $accordion = $(this);
			var $items = $accordion.find(".qyrus-accordion-item");
			var $images = $accordion.find(".qyrus-accordion-image");
			var $titles = $items.find(".qyrus-accordion-title");

			var trigger = $accordion.attr("data-trigger") === "hover" ? "hover" : "click";
			var hoverDelay = parseInt($accordion.attr("data-hover-delay"), 10) || 0;
			var hoverTimer = null;

			// Opens one item, closes the rest, and swaps the side image.
			function activate($item) {
				if ($item.hasClass("active")) {
					// Already open — keep it open (one item always stays expanded).
					return;
				}

				// Close every other item.
				$items.not($item)
					.removeClass("active")
					.find(".qyrus-accordion-content")
					.stop(true)
					.slideUp(200);
				$items.not($item).find(".qyrus-accordion-title").attr("aria-expanded", "false");

				// Open this item.
				$item.addClass("active");
				$item.find(".qyrus-accordion-content").stop(true).slideDown(200);
				$item.find(".qyrus-accordion-title").attr("aria-expanded", "true");

				// Swap the visible image. Match by item position first (safe even
				// when two items share the same image); fall back to the URL.
				var index = $item.attr("data-index");
				var $next = $images.filter(function () {
					return $(this).attr("data-index") === index;
				});
				if (!$next.length) {
					var imageUrl = $item.attr("data-image");
					$next = $images.filter(function () {
						return imageUrl && $(this).attr("src") === imageUrl;
					});
				}
				if ($next.length) {
					// From now on the chosen animation may play. Holding it back
					// until the first switch means the initial image on page load
					// just appears, with no hidden-then-animated flash.
					$accordion.addClass("qyrus-switched");
					$images.removeClass("active");
					$next.first().addClass("active");
				}
			}

			// Drop any handlers from a previous init of this same element.
			$titles.off(NS);

			// Click + keyboard always work, in both modes (touch has no hover).
			$titles.on("click" + NS + " keydown" + NS, function (e) {
				if (e.type === "keydown" && e.which !== 13 && e.which !== 32) {
					return;
				}
				e.preventDefault();
				clearTimeout(hoverTimer);
				activate($(this).closest(".qyrus-accordion-item"));
			});

			// Hover mode: open once the pointer has rested on a heading.
			if (trigger === "hover") {
				$titles.on("mouseenter" + NS, function () {
					var $item = $(this).closest(".qyrus-accordion-item");
					clearTimeout(hoverTimer);
					if (hoverDelay > 0) {
						hoverTimer = setTimeout(function () {
							activate($item);
						}, hoverDelay);
					} else {
						activate($item);
					}
				});

				// Leaving a heading before the delay ends cancels the open.
				$titles.on("mouseleave" + NS, function () {
					clearTimeout(hoverTimer);
				});
			}
		});
	}

	// Elementor frontend hook (works live + inside the editor preview).
	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/qyrus-home-accordion.default",
			initQyrusAccordion
		);
	});

	// Fallback for plain page load outside the Elementor hook (e.g. cached pages).
	$(function () {
		if (typeof elementorFrontend === "undefined") {
			initQyrusAccordion($(document));
		}
	});
})(jQuery);
