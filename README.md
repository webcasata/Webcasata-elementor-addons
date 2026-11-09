# Webcasata Elementor Addons

Custom Elementor widgets built for the Qyrus site, plus an extension that adds `:before` / `:after` controls to Elementor's own Container. The plugin is structured so more can be added the same way.

**Plugin Name:** Webcasata Elementor Addons
**Author:** Webcasata
**Author URI:** https://webcasata.com *(placeholder — update in the plugin header if this isn't your real site)*
**Text Domain:** `webcasata-elementor-addons`

## Finding the widgets in Elementor

All the widgets are registered under their own category in the widget panel — look for the **"Webcasata"** group (it sorts alphabetically among the other categories like Layout, Basic, General, WordPress, etc.). Inside it you'll find:

- **Webcasata Accordion**
- **Webcasata Feature Grid**
- **Webcasata Icon Scroll**
- **Webcasata Icon Box**
- **Webcasata Content Filter** *(only appears if the separate Webcasata Content Filter plugin is active)*

You can also just type "webcasata" (or the widget's function, e.g. "accordion", "marquee", "scroll") into the widget search box at the top of the panel.

If you don't see the category after installing/updating:
1. Confirm the plugin is **activated** under Plugins (a previous version installed under a different folder name won't auto-upgrade — see the note below).
2. Reload the Elementor editor (hard refresh, Ctrl/Cmd+Shift+R) — Elementor caches its widget list per editor session.
3. If it's still missing, go to **Elementor → Tools → Regenerate CSS & Data** to clear Elementor's internal cache.

## Widgets included

### 1. Webcasata Accordion (Accordion + Image Switcher)

Reproduces the "accordion on the left, image switches on the right" section (see the reference screenshot).

- **Desktop/tablet:** Left column is a vertical accordion (only one item open at a time). Right column is a single shared image panel; the image swaps automatically to match whichever item is open.
- **Trigger — Content → Settings → Open Accordion On:** **Click** (default) or **Hover**. In Hover mode, resting the pointer on a heading opens that item and swaps the image. **Hover Delay (ms)** (default 120) is how long the pointer must rest on a heading before it opens, so items don't flicker open while the mouse travels down the list. Click, tap and Enter/Space keep working in Hover mode, so touch and keyboard users are never stuck.
- **Image switch animation — Style → Image Panel → Image Switch Animation:** **Animation** (None, Fade, Slide In From Bottom / Top / Left / Right, Zoom In, Zoom Out, Blur In), **Animation Duration (ms)** and **Easing**. The animation plays on the incoming image each time the active item changes. The very first image on page load is not animated, and animations are switched off for visitors who have "reduce motion" enabled in their OS.
- **Mobile (≤767px):** The shared side panel is hidden. Instead, each accordion item shows its **own image inside its own container**, directly above or below its description — set in **Content → Settings → Mobile Layout → Image Position** (Before Content / After Content).
- Each accordion item has: **Title**, **Description**, **Image**, **Read More text**, **Read More link**.
- Full **Style tab** controls for Title, Description, Read More button and the Image panel — the Image Panel controls style both the desktop panel and the mobile inline image together.

### 2. Webcasata Feature Grid

A responsive grid of icon + title + description cards (feature highlights, service tiles, etc.).

- Each card has: **Icon** (Elementor's icon library, Font Awesome or custom SVG), **Title**, **Description**, and an optional **Link** — if a link is set, the whole card becomes clickable.
- **Content → Layout**: number of columns (responsive: separate values for desktop/tablet/mobile), gap, content alignment (left/center), title HTML tag (h2–h6/div/span).
- **Style tab** controls for Card (background, border, radius, padding, box shadow, hover background/border/lift), Icon (size, box size, color, background color, shape, spacing), Title (typography, color, hover color for linked cards, spacing) and Description (typography, color).

### 3. Webcasata Icon Scroll

An infinite auto-scrolling wall of icons/logos (integration badges, client logos, tech stack, etc.), similar to the reference "Connect ... To Your Ecosystem" section. Pure CSS animation — no external JS library.

- Each item has: **Icon** (image upload), **Icon Link** (optional — makes the item clickable), **Icon Name** (used as alt text, and optionally shown as a caption).
- **Content → Settings**:
  - **Slide Direction** — Vertical or Horizontal.
  - **Columns** (vertical mode) / **Rows** (horizontal mode) — icons are distributed round-robin across lanes, each scrolling independently, exactly like the reference image.
  - **Marquee Height** (vertical mode).
  - **Speed (Seconds Per Loop)** — lower is faster.
  - **Alternate Column/Row Direction** — odd lanes scroll the opposite way for a flowing effect (on by default, matches the reference).
  - **Reverse Direction** — flips the overall scroll direction.
  - **Pause On Hover** — pauses the whole lane the cursor is over.
  - **Show Icon Name** — optional caption under each icon.
  - **Open Links In New Tab** / **Add "nofollow" To Links**.
- **Style tab**:
  - **Layout** — gap between columns/rows, gap between icons.
  - **Icon Box** — **Logo Width**, **Logo Height**, padding, background color, border, **Border Radius**, box shadow, and a **Grayscale Until Hover** toggle (icons are muted grayscale until hovered, then show full color).
  - **Caption** — typography, color, spacing (only relevant when "Show Icon Name" is on).

### 4. Webcasata Icon Box

Elementor's Icon Box with the parts it lacks: a real **background around the icon** (with its own hover state), hover styling that can be driven by the widget, its parent container or any custom class, a **focused title**, text fill / stroke / shadow effects, a **title separator**, and `:before` / `:after` pseudo-elements.

- **Content → Icon Box:** Icon, Title, Description, Link, Title HTML Tag. Put words between `{{` and `}}` in the Title to style them separately, e.g. `We build {{lasting}} things`. (On-canvas inline editing of the title is not available because of the `{{ }}` syntax — edit it in the panel.)
- **Content → Title Separator:** Show Separator, Position (above / below the title), Style (line, dashed, dotted, double, or line with icon) and the separator icon.
- **Content → Pseudo Elements:** the same `:before` / `:after` switches as the Container, applied to the whole icon box. Their styling blocks appear in the Style tab (Normal / Hover, Hover Trigger, Icon Font, Center Content, transforms, etc. — see the Container section below).
- **Style → Hover Trigger:** choose what has to be hovered for *every* Hover tab in this widget: **This widget**, **Direct parent container**, or **Custom class**. Plus transition duration and easing.
- **Style → Box:** Icon Position (left / top / right / bottom, responsive), Vertical Alignment, Alignment (start / center / end, responsive), Icon Spacing.
- **Style → Icon:** Size, Color (Normal / Hover), Hover Animation (grow, shrink, float, sink, rotate, tilt), Rotate.
- **Style → Icon Background** *(the main addition)*: **Shape** quick-select (circle / rounded / square), **Size**, **Padding**, then Normal and Hover tabs each with **Background** (solid or gradient), **Background Opacity** (affects the shape only, never the icon), **Border**, **Border Radius** and **Box Shadow**.
- **Style → Title** and **Style → Focused Title** (same toolkit; anything left empty in Focused Title is inherited from the Title): Typography, **Text Fill** (Solid with Normal/Hover colour, **Gradient** with two colours and an angle, or **Image** with size / position / repeat), **Text Stroke** (width, colour, *Outline Only*, *Stroke Behind Fill*) and **Text Shadow** (colour, X, Y, blur). Focused Title also has *Display* (inline, or on its own line).
- **Style → Title Separator:** Width, Thickness, Line Color and Icon Color (Normal / Hover), Width on Hover, Icon Size, spacing above and below.
- **Style → Description:** Typography, Text Shadow, Color (Normal / Hover).

Notes: with a gradient or image fill the shadow is drawn with a `drop-shadow` filter (a normal `text-shadow` would show through the transparent letters); the result looks the same to the eye. Gradient / image text needs a modern browser (all current ones).

### 5. Webcasata Content Filter

A thin wrapper around the separate **Webcasata Content Filter** plugin. It doesn't reimplement any rendering — it looks up the same `wbcf_filter` posts (the "filters" you create under **Content Filters** in the WP admin menu) and calls that plugin's own `WBCF_Render::render_filter()`, the exact method its `[wbcf_filter id="X"]` shortcode and Gutenberg block both use. So the output here is always identical to the shortcode/block for the same filter, and any template or feature added to that plugin later works here automatically with no changes to this widget.

- **Content → Content Filter**:
  - **Select Filter** — a dropdown of every filter you've created in the Webcasata Content Filter plugin (by title).
  - **Posts Per Page** (optional override) — leave at 0 to use the number set on the filter itself.
  - **Template** (optional override) — leave on "Don't Override" to use the template set on the filter itself; otherwise choose from every registered template, including any the Pro add-on has registered.
- **Content → Display** — seven toggles to hide parts of every card in this placement only:
  - **Hide Title**, **Hide Meta Tags** (date + category), **Hide Author**, **Hide Comments**, **Hide Read More**, **Hide Featured Image**, **Hide Excerpt**.
  - The Content Filter plugin has no per-placement option for this (these are baked into its template files and shared by every shortcode/block/widget use of a filter), so each toggle works by injecting CSS scoped to just this one widget instance — it never touches the plugin's templates or affects the same filter used elsewhere.
  - Author/Comments/Read More are separate toggles rather than folded into "Meta" because the Classic template puts them in a different markup block (`card__footer`) than the date/category "meta" block (`card__meta`) — one combined selector can't match both.
  - Turning a toggle on reveals a **"... Selector (Advanced)"** field, pre-filled with a best-guess CSS selector matching the class names used by this plugin's built-in templates (Classic, Cards, Magazine, Minimal, News). If your filter uses a different template — including one of the five Pro templates — and a toggle doesn't visibly hide anything, open that card in your browser's inspector, find the real class name wrapping that part, and paste it into the selector field.
  - **To match more than one class in the same field, separate them with commas** — e.g. `[class*="card__author"], [class*="card__comments"]` — each one is hidden independently. Space-separating them instead (`[class*="card__meta"] [class*="card__author"]`) means "nested inside," which won't match unless one is literally inside the other in the markup.
- **Requires the "Webcasata Content Filter" plugin to be active.** If it isn't:
  - The widget won't appear registered with a broken picker — instead the Content tab shows a note that the plugin needs to be installed/activated, and an admin notice appears in wp-admin.
  - If you activate Webcasata Content Filter *after* installing this plugin, just reload the Elementor editor — the widget registers itself automatically once the class it depends on (`WBCF_Render`) is available.
- No Style tab beyond Display: the rendered grid's appearance (colors, layout, template) is otherwise controlled entirely by that filter's own settings in the Webcasata Content Filter admin, exactly as it is for the shortcode and block — adding broader style controls here would just conflict with those.

## Container extension: Pseudo Elements (`:before` / `:after`)

This is not a widget: it adds controls to Elementor's own **Container**, so decoration that normally needs hand-written CSS (underlines, overlays, gradient borders, ribbons, corner shapes, hover-on-parent effects) can be built in the panel.

**Turn it on** — select a Container → **Layout** tab → **Pseudo Elements** → switch on **:before** and/or **:after**.

**Style it** — **Style** tab → a **Pseudo :before** / **Pseudo :after** section appears (scroll below Shape Divider), each with **Normal** and **Hover** tabs:

- **Normal:** Content (text or an icon-font code like `\f101`), **Icon Font** (Font Awesome Solid/Regular/Brands or eicons — loaded automatically; without it an icon code shows as an empty box), **Center Content** (centres the text/icon inside the Width/Height), Typography, Position, Fill Container, Width/Height, Offset (Top/Right/Bottom/Left), Background (classic or gradient), Text/Icon Color, Opacity, Border, Border Radius, Box Shadow, CSS Filters, Backdrop Blur, Transform (Translate X/Y, Rotate, Scale), Layering (Send Behind Container Content, Z-Index), Blend Mode, Pointer Events. Width, Height, Offset, Translate and Border Radius are responsive.
- **Hover:** a **Hover Trigger** dropdown, then the same animatable options (size, offset, background, opacity, border, shadow, filters, transform), then **Transition** (duration and easing). Only what you change in Hover is applied on hover.

**Hover Trigger options**

| Option | The styles apply while the pointer is over… |
|---|---|
| This container | the container itself |
| Direct parent container | the container that directly wraps this one (works for boxed and full-width parents) |
| Custom class | the nearest ancestor that has the class you type (no dot); if there is none, any element on the page with that class |

"This container" and "Direct parent container" are pure CSS. "Custom class" uses a small script (`assets/js/webcasata-pseudo-hover.js`) that toggles a class while the pointer is over the chosen element. Hover does not exist on touch screens.

**Things to know**

- **`:before` is also Elementor's Background Overlay layer.** Elementor draws its own overlay with `::before` on every container, so by default the `:before` here already covers the whole container (and copies its rounded corners). Do not use it together with a Background Overlay on the same container — use `:after` for that. `:after` has no default size: it is invisible until you set Width/Height or switch on Fill Container.
- Both start as `position: absolute` with `pointer-events: none`, so they never block clicks. An absolute pseudo-element is painted above the container's content unless you use **Send Behind Container Content** (this sets `z-index: -1` and `isolation: isolate` on the container so it stays inside it).
- Disabling a pseudo-element switch removes all of its CSS, even if values are still set in the panel.
- Pseudo-elements are for Containers only (not legacy Sections/Columns). 

## Style On Parent Hover (any widget or container)

Lets a child change while its **parent** is hovered — e.g. a card whose heading and paragraph change colour when the card is hovered — without custom CSS.

**Turn it on** — select any widget (or a nested Container) → **Advanced** tab → **Style On Parent Hover** → switch on **Style On Parent Hover**.

- **Hover Trigger:** *Direct parent container* (default, pure CSS), or *Custom class* — type a class name (no dot) and the styles apply while the pointer is over the nearest ancestor with that class (or, failing that, any element on the page with it). Use this when the element that should trigger the hover is higher up than the direct parent.
- **Color:** **Text Color** recolours the element *and everything inside it* (headings, paragraphs, links, font icons); **Icon (SVG) Fill Color** does the same for SVG icons.
- **Background**, **Opacity**, **Border**, **Border Radius**, **Box Shadow**, **CSS Filters**, **Transform** (Translate X/Y, Rotate, Scale), and **Transition** (duration, easing).
- Only the options you set are applied on hover. These controls style the widget's outer wrapper (and, for Text Color / SVG Fill, what is inside it). To restyle one specific inner part of a widget (say a button's border), set it on that widget's own Style tab hover state instead.
- Hovering the child itself also counts, because the child is inside its parent. Hover does not exist on touch screens.
- With the switch off, nothing is generated, even if values were left in the panel.

## Installation

1. Zip the plugin folder if it isn't already zipped (this project already includes `webcasata-elementor-addons.zip`).
2. In WordPress admin: **Plugins → Add New → Upload Plugin**, choose the zip, click **Install Now**, then **Activate**. If a previous version of this plugin is already installed under a different name/slug (e.g. "Qyrus Accordion Widget" or "Qyrus Elementor Widgets"), deactivate and delete that old one first — this rebranded version uses a new folder/file name so it will install alongside rather than overwrite it.
3. Requires **Elementor** (free or Pro) to be installed and active.

## Using the widgets

1. Edit a page with Elementor.
2. Open the **Webcasata** category in the widget panel (or search "webcasata") and drag **Webcasata Accordion**, **Webcasata Feature Grid**, or **Webcasata Icon Scroll** onto the page.
3. For the accordion widget, open **Content → Accordion Items**:
   - Click **+ Add Item** to add a new accordion entry.
   - Fill in **Title**, **Description**, upload an **Image**, and set **Read More Text** / **Read More Link** for each item.
   - Use **Default Open Item** to choose which item (and image) is shown first on page load.
   - In **Content → Settings → Mobile Layout**, choose whether each item's inline image on mobile appears **Before Content** or **After Content**.
4. For the feature grid widget, open **Content → Features**:
   - Click **+ Add Item**, pick an icon, and fill in **Title** / **Description** / optional **Link**.
   - Adjust **Content → Layout** for columns, gap and alignment.
5. For the icon marquee widget, open **Content → Icons**:
   - Click **+ Add Item**, upload an icon image, and optionally set a name and link.
   - Choose **Slide Direction**, columns/rows, speed and the other settings described above.
6. For the content filter widget, open **Content → Content Filter**:
   - Pick a filter from **Select Filter** (created beforehand under **Content Filters** in the WP admin menu).
   - Optionally set **Posts Per Page** and/or **Template** overrides.
7. Open each of the first three widgets' **Style** tab to fully customize colors, typography, spacing, borders and shadows.

## File structure

```
webcasata-elementor-addons/
├── webcasata-elementor-addons.php                # Plugin bootstrap – registers all widgets
├── includes/
│   ├── class-webcasata-pseudo-elements.php        # Container extension: :before / :after controls
│   └── class-webcasata-parent-hover.php           # "Style On Parent Hover" for every widget/container
├── widgets/
│   ├── class-qyrus-home-accordion-widget.php      # Webcasata Accordion: controls + render()
│   ├── class-qyrus-feature-grid-widget.php        # Feature Grid: controls + render()
│   ├── class-qyrus-icon-marquee-widget.php        # Webcasata Icon Scroll: controls + render()
│   ├── class-webcasata-icon-box-widget.php        # Webcasata Icon Box: controls + render()
│   └── class-qyrus-wcf-filter-widget.php          # Webcasata Content Filter: wraps WBCF_Render::render_filter()
├── assets/
│   ├── css/
│   │   ├── qyrus-accordion.css                    # Accordion styles + mobile image swap
│   │   ├── qyrus-feature-grid.css                 # Feature grid styles
│   │   ├── qyrus-icon-marquee.css                 # Marquee keyframes + layout
│   │   └── webcasata-icon-box.css                 # Icon Box structure, separator, transitions
│   └── js/
│       ├── qyrus-accordion.js                     # Accordion toggle + desktop image-swap behavior
│       └── webcasata-pseudo-hover.js              # "Custom class" hover trigger (pseudo-elements + parent hover)
└── README.md
```

To add another widget later: drop a new `class-qyrus-*.php` file in `widgets/`, register it (and any of its own CSS/JS) in `webcasata-elementor-addons.php` the same way the existing widgets are registered.

## Notes on this rebrand

- Only the **plugin's public identity** was changed for this package: the Plugin Name, Description, Author, Author URI, Text Domain, the plugin folder/main file name, and the admin notice text. Internal PHP class names (`Qyrus_*`), CSS classes (`.qyrus-*`) and script/style handles were intentionally left as-is — renaming those touches nearly every line across all files and carries real risk of breaking something, and they're invisible to anyone using the plugin. Say the word if you'd like a full internal rebrand too (classes, CSS prefixes, asset filenames).
- **Author URI is a placeholder** (`https://webcasata.com`) — update the header comment in the main plugin file if that isn't correct.
- The version was bumped to **1.3.0** to reflect the new Webcasata widget category and renamed widgets, to **1.3.1** to fix a seamless-loop bug in the Icon Scroll widget, to **1.4.0** to add the Webcasata Content Filter widget, to **1.4.1** to fix that widget's dependency check, to **1.5.0** to add the Content Filter widget's Display toggles, to **1.5.1** to split "Meta" into three separate toggles, to **1.6.0** to add the accordion's hover trigger and image switch animations, to **1.7.0** to add the Container pseudo-element extension, to **1.8.0** to add Center Content / Icon Font for pseudo-elements and Style On Parent Hover, to **1.9.0** to add the Webcasata Icon Box widget, and to **1.9.1** to fix its settings panel opening blank.

## Changelog

- **1.9.3** — Fixed the Webcasata Icon Box settings panel opening blank for real. Two help notes in the panel contained the characters `{{ }}` (used to describe the focused-title syntax); Elementor compiles panel notes as templates, so they threw an error and stopped the whole panel drawing. They now use HTML entities. The editor diagnostic from 1.9.2 (`&wib_debug=1`) is kept.
- **1.9.2** — Diagnostics only: opening the editor with `&wib_debug=1` in the URL lists, in a red box and in the browser console, the name of any panel control that throws while rendering. No behaviour change otherwise.
- **1.9.1** — Fixed the Webcasata Icon Box settings panel opening blank. The widget's "Size" control for the icon background used the same internal id (`bg_size`) as the Background group's own "Size" field; Elementor rejects duplicate control ids and the resulting notice broke the panel. The control id is now unique.
- **1.9.0** — Added the **Webcasata Icon Box** widget: styleable icon background (shape, size, colour/gradient, opacity, border, radius, shadow; Normal + Hover), a Hover Trigger (this widget / direct parent / custom class) that drives every Hover tab, a `{{focused title}}`, text fill (solid / gradient / image), text stroke, text shadow, a title separator, and `:before` / `:after` pseudo-elements. The Container pseudo-element feature was generalised internally so the widget reuses it; Containers behave exactly as before.
- **1.8.0** — (1) Pseudo-elements: added **Center Content**, **Icon Font** (with automatic loading of the matching Elementor icon stylesheet — fixes icon codes rendering as an empty box), **Typography** and **Text/Icon Color** (Normal and Hover). (2) New **Style On Parent Hover** section in the Advanced tab of every widget and container (trigger: direct parent container or custom class). The hover script now serves both features from one handler attached to every element, and only does work for elements that use "Custom class".
- **1.7.0** — Added the **Pseudo Elements** extension to Elementor's Container (Phase 1): `:before` / `:after` switches in the Layout tab, a styling block for each in the Style tab with Normal and Hover tabs, and a Hover Trigger (this container / direct parent container / custom class). No existing widget changed. See "Container extension" above for the full option list and the note about Elementor's Background Overlay sharing `:before`.
- **1.6.0** — Webcasata Accordion: added **Open Accordion On → Click / Hover** (plus a **Hover Delay**), and an **Image Switch Animation** group (animation type, duration, easing). The old "Fade Transition (ms)" control is now "Animation Duration (ms)" and keeps its saved value; it previously had no visible effect because the image toggled between `display:none` and `display:block`, which CSS transitions can't animate — the new animations are keyframe-based, so they do play. Existing accordions default to **Fade**; set Animation to **None** to restore the old instant swap. Also: the side image is now matched to its accordion item by position instead of by file URL, so two items that use the same image no longer light up two images at once.
- **1.5.1** — Split the Content Filter widget's Display toggles from four to seven: **Hide Author**, **Hide Comments** and **Hide Read More** are now their own toggles instead of being lumped under "Hide Meta Tags." In the Classic template these live in a different markup block (`card__footer`) than the date/category "meta" block (`card__meta`), so a single combined selector could never match both — this was reported as the Meta selector not fully working when trying to hide author/comments alongside it. The Display section's help text also now explicitly documents comma- vs. space-separating selectors, since space-separating them (which reads as "nested inside" in CSS, not "any of these") was the other cause of the reported issue.
- **1.5.0** — Added a **Display** section to the Webcasata Content Filter widget with four toggles: **Hide Title**, **Hide Meta Tags**, **Hide Featured Image**, **Hide Excerpt**. Since the Content Filter plugin itself has no per-placement option for this, each toggle injects CSS scoped to that one widget instance (via Elementor's own `.elementor-element-{id}` wrapper class) rather than modifying the plugin's shared templates. Each toggle reveals an editable "Selector (Advanced)" field pre-filled with a best-guess selector for the plugin's built-in templates, in case a different template needs a different class name.
- **1.4.1** — Fixed the Webcasata Content Filter widget not appearing even with the Content Filter plugin installed and active. That plugin's internal class/constant prefix is `WBCF_` (e.g. `WBCF_Render`, `WBCF_Assets`, `WBCF_POST_TYPE`) — changed on their end at some point to avoid colliding with WooCommerce's `WC_` namespace — but this widget's dependency check and lookups were still written against the older `WCF_` names, so `class_exists( 'WCF_Render' )` always came back false and the widget silently skipped registering itself (with no PHP error, since checking for a nonexistent class isn't a fatal). All checks and calls now use the `WBCF_` names the plugin actually ships.
- **1.4.0** — Added the **Webcasata Content Filter** widget, a wrapper around the separate Webcasata Content Filter plugin's saved filters. Registers only if that plugin is active (soft dependency, same pattern Elementor itself uses).
- **1.3.1** — Fixed a bug in Webcasata Icon Scroll where the loop point showed a visible gap and a jump/jerk each time the icons repeated. The spacing between icons was using flexbox `gap`, which isn't accounted for by the `translate(-50%)` seamless-loop trick — the animation now uses per-icon margin instead, so the loop restarts at an exact, gapless match. The animation also now uses `translate3d()` for smoother, GPU-accelerated scrolling.

## Other notes

- The accordion always keeps exactly one item open (clicking the already-open item does nothing). If you'd rather allow fully collapsing all items, that's a one-line change in `assets/js/qyrus-accordion.js`.
- Image swapping in the accordion matches images by URL, so make sure each accordion item has its own image uploaded.
- The mobile layout switch in the accordion happens at `max-width: 767px` (in `assets/css/qyrus-accordion.css`). If your theme's Elementor "Mobile" breakpoint is set to a different value, update that number to match.
- The Feature Grid widget needs no JavaScript — column counts and hover effects are handled entirely by Elementor's generated CSS.
- The Webcasata Icon Scroll widget needs no JavaScript either — the infinite loop is a pure CSS animation (each lane renders its icon set twice; animating a 50% translate loops seamlessly).
