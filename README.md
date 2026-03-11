## Casino Finder – Quick Guide

### What this plugin does

Casino Finder adds a short “wizard” that asks visitors a few questions and then recommends casinos based on their answers.  
It uses:

- A `casino` **Custom Post Type** for casino entries.
- Several **custom taxonomies** (type, game, banking, payout speed) as filters.
- A `[casino_finder]` **shortcode** to render the wizard.
- A small **REST endpoint** to load matching casinos as server‑rendered cards.

---

### How to install and configure

1. **Install and activate**
   - Copy the `casino-finder` folder into `wp-content/plugins/`.
   - If working from source, run `composer install` to pull in dependencies.
   - Activate **Casino Finder** from the Plugins screen.

2. **Create casinos**
   - Use the **Casinos** menu (the `casino` post type).
   - For each casino:
     - Add a title and description.
     - Fill in the custom fields (rating, bonus, bonus code, affiliate URL, etc.).
     - Assign taxonomy terms (see next step).

3. **Set up filter options**
   - Manage terms for these taxonomies:
     - `casino_type` – type of casino.
     - `casino_game` – game category.
     - `casino_banking` – banking/payment method.
     - `casino_payout_speed` – payout speed.
   - Optionally, add an image to each term (used as icons in the wizard).

4. **Place the wizard on a page**
   - Add this shortcode to any page or post:

     ```text
     [casino_finder per_page="10" load_more="yes"]
     ```

   - `per_page`: casinos per request (1–50, default 10).  
   - `load_more`: whether to show “Load more results” (`yes` / `no`).

Once this is done, visitors will see the multi‑step wizard, and results will be loaded via the REST endpoint and displayed as cards.

---

### Files to review

- `casino-finder.php` – Boots the plugin and registers activation/deactivation hooks.
- `src/Plugin.php` – Registers the `casino` CPT, taxonomies, meta boxes, shortcode, and REST controller.
- `src/Shortcodes/CasinoFinderShortcode.php` – Implements `[casino_finder]` and renders the wizard via a PHP template.
- `src/Rest/CasinoFinderController.php` – Handles `/wp-json/casino-finder/v1/search` and renders casino cards via a PHP template.
- `src/Support/TemplateRenderer.php` – Helper that safely loads templates from `resources/templates/`.
- `resources/templates/` – Contains `wizard.php` (wizard layout) and `casino-card.php` (single casino card).

---

### Custom fields library

Custom fields library, [`ivanyankov/meta-fields-builder`](https://github.com/ivanyankov/meta-fields-builder) to define and manage all meta boxes and custom fields.

- Provides a clean, declarative API for meta fields (text, number, URL, checkbox, image, taxonomy image, etc.).
- Centralizes rendering, sanitization, and saving of meta values.
- Keeps this plugin’s classes focused on casino logic and filtering, instead of low‑level meta box plumbing.

In practice, the library powers:

- All structured meta on the `casino` CPT (rating, URLs, bonus details, flags).
- Term‑level images used as icons for the taxonomies shown in the wizard.