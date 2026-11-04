/**
 * Webcasata Pseudo Elements — "Custom class" hover trigger.
 *
 * "This container" and "Direct parent container" are pure CSS. Only the
 * "Custom class" trigger needs script, because Elementor cannot put a typed
 * class name into a CSS selector. For each container that uses it, this
 * watches the chosen element and toggles .wpe-before-hover / .wpe-after-hover
 * on the container while the pointer is over it; the generated CSS does the rest.
 *
 * Which element is watched: the nearest ancestor of the container that has the
 * class; if there is none, every element on the page that has the class.
 */
(function ($) {
	"use strict";

	var PSEUDOS = ["before", "after"];
	var SAFE_CLASS = /^[A-Za-z_][A-Za-z0-9_-]*$/;
	var WATCHED_SETTING = /^wpe_(before|after)_(enable|h_trigger|h_class)$/;

	function register() {
		if (
			typeof elementorModules === "undefined" ||
			!elementorModules.frontend ||
			!elementorModules.frontend.handlers ||
			!elementorModules.frontend.handlers.Base ||
			typeof elementorFrontend === "undefined" ||
			!elementorFrontend.elementsHandler
		) {
			return;
		}

		var PseudoHover = elementorModules.frontend.handlers.Base.extend({
			listeners: null,

			// Elementor calls bindEvents() on init and unbindEvents() on destroy.
			bindEvents: function () {
				this.refresh();
			},

			unbindEvents: function () {
				this.release();
			},

			// Editor only: re-read the settings when one of ours changes.
			onElementChange: function (name) {
				if (WATCHED_SETTING.test(name)) {
					this.refresh();
				}
			},

			refresh: function () {
				var self = this;
				var settings = this.getElementSettings() || {};

				this.release();
				this.listeners = [];

				PSEUDOS.forEach(function (pseudo) {
					if (settings["wpe_" + pseudo + "_enable"] !== "yes") {
						return;
					}
					if (settings["wpe_" + pseudo + "_h_trigger"] !== "custom") {
						return;
					}

					var cls = String(settings["wpe_" + pseudo + "_h_class"] || "")
						.replace(/^\./, "")
						.trim();
					if (!SAFE_CLASS.test(cls)) {
						return;
					}

					self.watch(pseudo, cls);
				});
			},

			watch: function (pseudo, cls) {
				var el = this.$element[0];
				var hoverClass = "wpe-" + pseudo + "-hover";
				var ancestor = el.parentElement ? el.parentElement.closest("." + cls) : null;
				var targets = ancestor
					? [ancestor]
					: Array.prototype.slice.call(document.querySelectorAll("." + cls));

				var on = function () {
					el.classList.add(hoverClass);
				};
				var off = function () {
					el.classList.remove(hoverClass);
				};

				var listeners = this.listeners;
				targets.forEach(function (target) {
					target.addEventListener("mouseenter", on);
					target.addEventListener("mouseleave", off);
					listeners.push({ target: target, on: on, off: off });
				});
				listeners.push({ cleanup: off });
			},

			release: function () {
				if (!this.listeners) {
					return;
				}
				this.listeners.forEach(function (l) {
					if (l.target) {
						l.target.removeEventListener("mouseenter", l.on);
						l.target.removeEventListener("mouseleave", l.off);
					}
					if (l.cleanup) {
						l.cleanup();
					}
				});
				this.listeners = null;
			},
		});

		// `null` skin => the plain "frontend/element_ready/container" hook.
		elementorFrontend.elementsHandler.attachHandler("container", PseudoHover, null);
	}

	$(window).on("elementor/frontend/init", register);
})(jQuery);
