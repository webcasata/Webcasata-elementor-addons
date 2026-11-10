=== Webcasata Elementor Addons ===
Contributors: webcasata
Tags: elementor, widgets, accordion, video, icon box
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.11.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extra Elementor widgets in their own "Webcasata" category. Switch each widget on or off; only active widgets load CSS or JavaScript.

== Description ==

Webcasata Elementor Addons adds a small set of carefully built widgets to Elementor, grouped under their own **Webcasata** category in the panel.

**Widgets**

* **Accordion** – left / right accordion where the image swaps per item, with its own mobile inline-image layout.
* **Feature Grid** – icon, title and description cards in a responsive grid.
* **Icon Scroll** – infinite vertical or horizontal auto-scrolling icon wall (pure CSS).
* **Icon Box** – icon with a styleable background, focused title, text fill / stroke / shadow, title separator and :before / :after pseudo-elements.
* **Video** – YouTube, Vimeo or self-hosted video that is not loaded until it is played: in a popup or inline. Videos set to autoplay load when they scroll into view.
* **Content Filter** – drops a saved filter from the separate "Webcasata Content Filter" plugin onto the page (only shown when that plugin is active).

**Extensions**

* **Container :before / :after** – pseudo-element controls (Normal + Hover) for Elementor Containers.
* **Style On Parent Hover** – a section in the Advanced tab of every widget and container.

**Full control**

Under **Settings > Webcasata Addons** you can switch the whole plugin, every widget and every extension on or off. A switched-off widget is not registered with Elementor and loads no CSS or JavaScript. Widgets that are on only load their files on pages where the widget is actually used.

Requires the free Elementor plugin.

== Installation ==

1. Make sure Elementor is installed and active.
2. Upload the `webcasata-elementor-addons` folder to `/wp-content/plugins/`, or install the zip from Plugins > Add New > Upload Plugin.
3. Activate the plugin.
4. Open Settings > Webcasata Addons to choose which widgets to enable (all are on by default).
5. Edit a page with Elementor and find the widgets under the **Webcasata** category.

== Frequently Asked Questions ==

= Does this work without Elementor? =

No. Elementor (free) must be active, version 3.18 or newer.

= What happens to a page that uses a widget I switch off? =

The widget is hidden from the panel and renders nothing on the page. No content is deleted; turn the widget back on and it returns exactly as it was.

= Do disabled widgets slow my site down? =

No. Disabled widgets are never loaded, and enabled widgets only load their CSS / JavaScript on pages where they are used.

= Does the Video widget load YouTube or Vimeo when the page loads? =

No. The page contains only a poster image and a play button. The video player is created when a visitor clicks play (or, for autoplay videos, when the video scrolls into view).

= Where is my data stored? =

The plugin stores one option (`webcasata_addons_settings`) and a few short-lived cached Vimeo thumbnail addresses. Both are removed when you delete the plugin. It collects no personal data and sets no cookies itself.

== External services ==

The Video widget can connect to third-party services. Nothing is requested until a widget that uses the service is placed on a page.

**YouTube (Google LLC)**
Used to play YouTube videos and, optionally, to show the video's thumbnail. When a visitor plays a video, the browser loads the player from `https://www.youtube.com/embed/` (or `https://www.youtube-nocookie.com/embed/` when the "Privacy mode" option is on). The poster image may be loaded from `https://i.ytimg.com/`. Data sent: the visitor's IP address and browser details, as for any embedded video.
Terms: https://www.youtube.com/t/terms – Privacy: https://policies.google.com/privacy

**Vimeo (Vimeo.com, Inc.)**
Used to play Vimeo videos. When a visitor plays a video, the browser loads the player from `https://player.vimeo.com/video/`. To show a thumbnail, your web server asks `https://vimeo.com/api/oembed.json` for the video's thumbnail address (sent: the Vimeo video URL; the result is cached for up to a week).
Terms: https://vimeo.com/terms – Privacy: https://vimeo.com/privacy

Self-hosted videos use no external service.

== Changelog ==

= 1.11.1 =
* Fix: "translation loading was triggered too early" notice (WordPress 6.7+). Labels are now translated only on the settings screen.
* Updated "Tested up to" to WordPress 7.1.

= 1.11.0 =
* New: Settings > Webcasata Addons – master switch plus an on / off switch for every widget and extension.
* New: disabled widgets are not registered and load no CSS or JavaScript; active widgets load theirs only on pages that use them.
* New: readme.txt, uninstall cleanup, minimum Elementor version check.
* Hardening: Content Filter widget hide-selectors can no longer break out of their CSS rule; escaping notes added where Elementor already escapes attribute values.

= 1.10.0 =
* New: Video widget (YouTube / Vimeo / self-hosted, loaded only on play, popup or inline, autoplay on scroll).

= 1.9.3 =
* Fix: Icon Box panel failed to open in some Elementor versions.

= 1.9.0 =
* New: Icon Box widget.

== Upgrade Notice ==

= 1.11.1 =
Fixes a translation-loading notice shown in the admin on WordPress 6.7+.

= 1.11.0 =
Adds the Settings > Webcasata Addons screen. All widgets stay enabled after updating.
