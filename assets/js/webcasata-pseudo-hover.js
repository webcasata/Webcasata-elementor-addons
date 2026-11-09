/**
 * Webcasata hover triggers — the "Custom class" option.
 *
 * Used by two features:
 *   - Container Pseudo Elements (:before / :after) -> toggles .wpe-before-hover / .wpe-after-hover
 *   - Style On Parent Hover (any widget/container)  -> toggles .wph-hover
 *   - Webcasata Icon Box hover trigger              -> toggles .wib-hover
 *
 * "This container" / "Direct parent container" are pure CSS. Only "Custom
 * class" needs script, because Elementor cannot put a typed class name into a
 * CSS selector. The element watched is the nearest ancestor that has the
 * class; if there is none, every element on the page that has the class.
 *
 * One handler serves both features (attached to every element) because
 * Elementor tells handlers apart in the editor by their constructor name.
 */
(function ($) {
	"use strict";

	var SAFE_CLASS = /^[A-Za-z_][A-Za-z0-9_-]*$/;
	var WATCHED_SETTING = /^(wpe_(before|after)_(enable|h_trigger|h_class)|wph_(enable|trigger|class)|wib_(trigger|trigger_class))$/;

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

		var HoverTriggers = elementorModules.frontend.handlers.Base.extend({
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

				// Pseudo-elements (containers only: the settings simply don't exist elsewhere).
				["before", "after"].forEach(function (pseudo) {
					var p = "wpe_" + pseudo + "_";
					if (settings[p + "enable"] === "yes" && settings[p + "h_trigger"] === "custom") {
						self.watch("wpe-" + pseudo + "-hover", settings[p + "h_class"]);
					}
				});

				// Style On Parent Hover (any widget / container).
				if (settings.wph_enable === "yes" && settings.wph_trigger === "custom") {
					self.watch("wph-hover", settings.wph_class);
				}

				// Webcasata Icon Box: "Hover Styles Apply When Hovering -> Custom class".
				if (settings.wib_trigger === "custom") {
					self.watch("wib-hover", settings.wib_trigger_class);
				}
			},

			watch: function (hoverClass, rawClass) {
				var cls = String(rawClass || "").replace(/^\./, "").trim();
				if (!SAFE_CLASS.test(cls)) {
					return;
				}

				var el = this.$element[0];
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

		// Elementor identifies handlers by constructor name; make ours unique.
		try {
			Object.defineProperty(HoverTriggers, "name", { value: "WebcasataHoverTriggers" });
		} catch (e) {}

		// `null` skin => the plain "frontend/element_ready/global" hook (every widget and container).
		elementorFrontend.elementsHandler.attachHandler("global", HoverTriggers, null);
	}

	$(window).on("elementor/frontend/init", register);
})(jQuery);
