/**
 * Webcasata editor diagnostics. Only active when the editor URL contains
 * &wib_debug=1. It does not change any behaviour: it wraps Marionette's render
 * and triggerMethod so that, if a control or panel view throws, the NAME of the
 * failing control and the error are written to the console and shown in a red
 * box at the bottom of the editor.
 */
(function () {
	"use strict";

	if (!/[?&]wib_debug=1/.test(window.location.search)) {
		return;
	}

	var M = window.Marionette;
	if (!M) {
		return;
	}

	var box = null;

	function show(text) {
		try {
			if (!box) {
				box = document.createElement("pre");
				box.id = "wib-debug-box";
				box.style.cssText = "position:fixed;left:0;right:0;bottom:0;max-height:45vh;overflow:auto;margin:0;padding:10px 14px;background:#7a0019;color:#fff;font:12px/1.4 monospace;z-index:2147483647;white-space:pre-wrap";
				document.body.appendChild(box);
			}
			box.textContent += text + "\n\n";
		} catch (e) {}
	}

	function report(view, where, error) {
		if (!error || error.__wibLogged) {
			return;
		}
		try {
			error.__wibLogged = true;
		} catch (e) {}

		var name = "?", type = "?", parent = "?";
		try {
			name = view.model && view.model.get ? view.model.get("name") || view.model.get("widgetType") || view.model.get("elType") || view.model.cid : "(no model)";
			type = view.model && view.model.get ? view.model.get("type") : "";
			parent = view.options && view.options.model ? "" : "";
		} catch (e) {}

		var stack = String(error && error.stack ? error.stack : error).split("\n").slice(0, 6).join("\n");
		var text = "WIB DEBUG  [" + where + "]  control/view: " + name + (type ? "  (type: " + type + ")" : "") + "\n" + (error && error.message ? error.message : error) + "\n" + stack;

		if (window.console && console.error) {
			console.error(text);
		}
		show(text);
	}

	function wrap(proto, method, label) {
		if (!proto || typeof proto[method] !== "function") {
			return;
		}
		var original = proto[method];
		proto[method] = function () {
			try {
				return original.apply(this, arguments);
			} catch (error) {
				report(this, label + "." + method + (arguments[0] && typeof arguments[0] === "string" ? "(" + arguments[0] + ")" : ""), error);
				throw error;
			}
		};
	}

	wrap(M.View && M.View.prototype, "triggerMethod", "View");
	wrap(M.ItemView && M.ItemView.prototype, "render", "ItemView");
	wrap(M.CollectionView && M.CollectionView.prototype, "render", "CollectionView");
	wrap(M.CompositeView && M.CompositeView.prototype, "render", "CompositeView");
	wrap(M.CollectionView && M.CollectionView.prototype, "addChild", "CollectionView");

	window.addEventListener("error", function (e) {
		show("WIB DEBUG  [window error]  " + e.message + (e.filename ? "  @ " + e.filename + ":" + e.lineno : ""));
	});
	window.addEventListener("unhandledrejection", function (e) {
		var r = e && e.reason;
		show("WIB DEBUG  [unhandled rejection]  " + (r && r.message ? r.message : r) + (r && r.stack ? "\n" + String(r.stack).split("\n").slice(0, 5).join("\n") : ""));
	});

	if (window.console && console.info) {
		console.info("WIB DEBUG active: errors raised while rendering panel controls will be listed here.");
	}
})();
