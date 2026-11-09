/**
 * Webcasata Video.
 *
 * The page only contains a poster and a play button. The video is created here,
 * on demand:
 *   - Popup style:  click -> modal with the video; closing the modal removes it.
 *   - Inline style: click -> the video replaces the poster; with Auto Play on, it
 *                   is created when the widget scrolls into view instead.
 * Nothing from YouTube / Vimeo is requested before that.
 */
(function () {
	"use strict";

	var MODAL_VARS = ["--wv-modal-bg", "--wv-modal-w", "--wv-close-c", "--wv-ratio", "--wv-rn", "--wv-modal-r"];
	var ALLOW = "autoplay; fullscreen; picture-in-picture; encrypted-media";
	var activeModal = null;

	function parseConfig(wrap) {
		try {
			var cfg = JSON.parse(wrap.getAttribute("data-wv") || "null");
			return cfg && cfg.src ? cfg : null;
		} catch (e) {
			return null;
		}
	}

	/** Same URL with autoplay=1 (the #t= fragment of Vimeo / hosted files is kept). */
	function withAutoplay(src) {
		try {
			var u = new URL(src, window.location.href);
			u.searchParams.set("autoplay", "1");
			return u.toString();
		} catch (e) {
			return src + (src.indexOf("?") > -1 ? "&" : "?") + "autoplay=1";
		}
	}

	/** The iframe / <video> for this video. Called only when the video is actually going to play. */
	function createMedia(cfg) {
		var media;

		if (cfg.type === "hosted") {
			media = document.createElement("video");
			media.src = cfg.src;
			media.setAttribute("playsinline", "");
			media.setAttribute("preload", "auto");
			media.autoplay = true;
			media.controls = !!cfg.controls;
			media.loop = !!cfg.loop;
			if (cfg.mute) {
				media.muted = true;
				media.setAttribute("muted", "");
			}
			if (cfg.poster) {
				media.poster = cfg.poster;
			}
			media.setAttribute("aria-label", cfg.title || "Video");
			var p = media.play && media.play();
			if (p && p.catch) {
				p.catch(function () {});
			}
			return media;
		}

		media = document.createElement("iframe");
		media.src = withAutoplay(cfg.src);
		media.setAttribute("allow", ALLOW);
		media.setAttribute("allowfullscreen", "");
		media.setAttribute("referrerpolicy", "strict-origin-when-cross-origin");
		media.setAttribute("title", cfg.title || "Video");
		return media;
	}

	/* ---- inline --------------------------------------------------------- */

	function playInline(wrap, cfg, focus) {
		var frame = wrap.querySelector(".wv-frame");
		if (!frame || frame.classList.contains("is-playing")) {
			return;
		}

		var media = createMedia(cfg);
		frame.classList.add("is-playing");

		// Poster, overlay and button are no longer needed.
		Array.prototype.slice.call(frame.children).forEach(function (child) {
			frame.removeChild(child);
		});
		frame.appendChild(media);
		wrap.setAttribute("data-wv-state", "playing");

		if (focus && media.focus) {
			media.focus();
		}
	}

	/* ---- popup ---------------------------------------------------------- */

	function closeModal() {
		var m = activeModal;
		if (!m) {
			return;
		}
		activeModal = null;

		document.removeEventListener("keydown", m.onKey, true);
		var video = m.el.querySelector("video");
		if (video) {
			try {
				video.pause();
			} catch (e) {}
		}
		// Removing the node destroys the iframe / video: playback stops and nothing stays in the page.
		if (m.el.parentNode) {
			m.el.parentNode.removeChild(m.el);
		}
		document.documentElement.classList.remove("wv-modal-open");

		if (m.opener && m.opener.focus) {
			m.opener.focus();
		}
	}

	function openModal(wrap, cfg, opener) {
		closeModal();

		var el = document.createElement("div");
		el.className = "wv-modal";
		el.setAttribute("role", "dialog");
		el.setAttribute("aria-modal", "true");
		el.setAttribute("aria-label", cfg.title || "Video");

		// The widget's Style settings live on the wrapper as CSS variables; hand them to the modal.
		var cs = window.getComputedStyle(wrap);
		MODAL_VARS.forEach(function (name) {
			var v = cs.getPropertyValue(name).trim();
			if (v) {
				el.style.setProperty(name, v);
			}
		});

		var box = document.createElement("div");
		box.className = "wv-modal__box";

		var close = document.createElement("button");
		close.type = "button";
		close.className = "wv-modal__close";
		close.setAttribute("aria-label", "Close");
		close.innerHTML = "&times;";

		var frame = document.createElement("div");
		frame.className = "wv-modal__frame";
		frame.appendChild(createMedia(cfg));

		box.appendChild(close);
		box.appendChild(frame);
		el.appendChild(box);

		// Click on the dark area (not the video) closes.
		el.addEventListener("mousedown", function (e) {
			if (e.target === el) {
				closeModal();
			}
		});
		close.addEventListener("click", closeModal);

		var onKey = function (e) {
			if (e.key === "Escape" || e.key === "Esc") {
				e.stopPropagation();
				closeModal();
			} else if (e.key === "Tab") {
				// Keep keyboard focus inside the dialog: close button <-> video.
				var focusables = [close].concat(Array.prototype.slice.call(el.querySelectorAll("iframe, video[controls]")));
				var i = focusables.indexOf(document.activeElement);
				if (e.shiftKey && i <= 0) {
					e.preventDefault();
					focusables[focusables.length - 1].focus();
				} else if (!e.shiftKey && i === focusables.length - 1) {
					e.preventDefault();
					focusables[0].focus();
				}
			}
		};
		document.addEventListener("keydown", onKey, true);

		document.body.appendChild(el);
		document.documentElement.classList.add("wv-modal-open");
		activeModal = { el: el, onKey: onKey, opener: opener };

		// Next frame, so the fade-in transition runs.
		window.requestAnimationFrame(function () {
			el.classList.add("is-open");
		});
		close.focus();
	}

	/* ---- poster ---------------------------------------------------------- */

	function setupPoster(wrap) {
		var img = wrap.querySelector(".wv-poster[data-fallback]");
		if (!img) {
			return;
		}
		var swap = function () {
			var fb = img.getAttribute("data-fallback");
			if (fb && img.src !== fb) {
				img.removeAttribute("data-fallback");
				img.src = fb;
			}
		};
		img.addEventListener("error", swap, { once: true });
		// maxresdefault.jpg does not exist for every video; YouTube answers with a tiny placeholder instead.
		img.addEventListener(
			"load",
			function () {
				if (img.naturalWidth && img.naturalWidth <= 120) {
					swap();
				}
			},
			{ once: true }
		);
		if (img.complete && (img.naturalWidth === 0 || img.naturalWidth <= 120) && img.getAttribute("src")) {
			swap();
		}
	}

	/* ---- init ------------------------------------------------------------ */

	function init(wrap, options) {
		if (!wrap || wrap.__wvReady) {
			return;
		}
		var cfg = parseConfig(wrap);
		if (!cfg) {
			return;
		}
		wrap.__wvReady = true;
		options = options || {};

		setupPoster(wrap);

		var trigger = wrap.querySelector(".wv-trigger");
		if (trigger) {
			trigger.addEventListener("click", function (e) {
				e.preventDefault();
				if (cfg.style === "inline") {
					playInline(wrap, cfg, true);
				} else {
					openModal(wrap, cfg, trigger);
				}
			});
		}

		// Auto Play (inline): create the video the first time the widget is visible.
		// Not in the Elementor editor, where it would start playing while you edit.
		if (cfg.style === "inline" && cfg.autoplay && !options.editMode) {
			var frame = wrap.querySelector(".wv-frame");
			if (!frame) {
				return;
			}
			if ("IntersectionObserver" in window) {
				var observer = new IntersectionObserver(
					function (entries) {
						entries.forEach(function (entry) {
							if (entry.isIntersecting) {
								observer.disconnect();
								playInline(wrap, cfg, false);
							}
						});
					},
					{ threshold: 0.25 }
				);
				observer.observe(frame);
				wrap.__wvObserver = observer;
			} else {
				playInline(wrap, cfg, false);
			}
		}
	}

	window.WebcasataVideo = { init: init, close: closeModal };

	// Elementor: run for every Webcasata Video widget (front end and editor preview).
	var hooked = false;
	function hookElementor() {
		if (hooked || typeof elementorFrontend === "undefined" || !elementorFrontend.hooks) {
			return;
		}
		hooked = true;
		elementorFrontend.hooks.addAction("frontend/element_ready/webcasata-video.default", function ($scope) {
			var wrap = $scope[0] && $scope[0].querySelector(".wv-wrap");
			init(wrap, { editMode: elementorFrontend.isEditMode() });
		});
	}
	window.addEventListener("elementor/frontend/init", hookElementor);
	if (window.jQuery) {
		window.jQuery(window).on("elementor/frontend/init", hookElementor);
	}
})();
