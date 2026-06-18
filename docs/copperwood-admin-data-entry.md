# WordPress admin: Copperwood Models & Quick Possession (content guide)

This theme includes two custom post types for home listings:

- **Models** (`model`)
- **Quick Possession** (`quick-possesion`)

Use this as the admin SOP for adding and maintaining content.

---

## 1. One-time setup: taxonomies first

Before adding posts, make sure these taxonomies and terms exist:

- **Home type** / `home-type` (used by Models and Quick Possession)
- **Garage** / `garage` (used by Models and Quick Possession)
- **Possession date** / `possesion-date` (used by Quick Possession only)

### Home type terms to create

| Term | Purpose |
| ---- | ------- |
| Model names (e.g. *The Aspen*, *The Birch*) | Used as the floorplan heading and for filtering |
| **Show Home** (slug: `show-home`) | Marks a move-in ready listing. Floorplan tabs are **not** shown on the front end |

[add screenshot here]

---

## 2. One-time setup: Google Maps API key

The map shortcode reads the API key from Elementor.

1. Go to **WordPress Admin → Elementor → Settings → Integrations**.
2. Add your **Google Maps API key**.
3. **Save**.

Without a key, map shortcodes output nothing for visitors (editors may see an admin notice).

[add screenshot here]

---

## 3. Per item: Models

1. Go to **WordPress Admin → Models → Add Model**.
2. Fill core post fields:
   - **Title** (admin label; floorplan heading prefers the **Home type** name when set)
   - **Featured Image**
3. Assign taxonomies:
   - **Home type** — pick the model name (e.g. *The Aspen*). Do **not** use **Show Home** on standard models.
   - **Garage** (optional)
4. Fill ACF fields in **Models & Quick Possesion details**:
   - **`price`**
   - **`price_rebate`**
   - **`price_saving`**
   - **`price_maintenance`**
   - **`address`**
   - **`bedrooms`**
   - **`bathrooms`**
   - **`square_feet`**
   - **`floorplan_url`** (optional PDF for the download button)
   - **`description`**
   - **`video`**
   - **`feature_image`**
   - **`features_bullet`**
   - **`features_description`**
   - **`gallery`**
5. Fill repeater **`floorplan`** in **Model details [Floorplan]** (one row per floor):
   - **`name`** (e.g. `Main Floor / 660 sq. ft.`)
   - **`image`** (required per row for it to appear on the site)
6. **Publish / Update**.

### Floorplan display rules

- If **no floorplan rows** are added, or **no row has an image**, the floorplan section **does not appear** on the front end — there is no placeholder or fallback text.
- Text before `/` or `-` in **`name`** becomes the tab label (e.g. `MAIN FLOOR`).
- The full **`name`** value is shown as the subtitle under the heading.
- **`floorplan_url`** only shows a **Download Floorplan** button when a file is uploaded.

[add screenshot here]

---

## 4. Per item: Quick Possession

1. Go to **WordPress Admin → Quick Possession → Add Quick Possession**.
2. Fill core post fields:
   - **Title**
   - **Featured Image**
3. Assign taxonomies:
   - **Home type**
   - **Garage** (optional)
   - **Possession date** / `possesion-date` (optional)
4. Fill the same **Models & Quick Possesion details** ACF fields as Models (see section 3).
5. **Publish / Update**.

Quick Possession posts do **not** have the **Model details [Floorplan]** repeater. Floorplan tabs are only for **Models**.

[add screenshot here]

---

## 5. Per item: Show Home listings

Use this for furnished / move-in ready homes that use a different page layout.

1. Add or edit a **Model** or **Quick Possession** post.
2. Assign **Home type → Show Home**.
3. Fill **Models & Quick Possesion details** (address, price, gallery, video, etc.).
4. **Publish / Update**.

While **Show Home** is assigned, the `[copperwood_floorplans_v2]` shortcode outputs **nothing** — even if floorplan repeater rows exist on a Model.

In Elementor, use the **Show Home** single template without the floorplan shortcode (or hide it when `home-type` = Show Home).

[add screenshot here]

---

## 6. Front-end shortcodes

### Floorplan tabs (Models only)

```
[copperwood_floorplans_v2]
```

Also accepts: `[copperwood_floorplans]`

| UI element | Data source |
| ---------- | ----------- |
| Heading (e.g. *The Aspen*) | First **Home type** term that is not Show Home, else post **Title** |
| Subtitle | Repeater **`name`** |
| Tab labels | Part of **`name`** before `/` or `-` |
| Stats line | **`square_feet`**, **`bedrooms`**, **`bathrooms`** |
| Download button | **`floorplan_url`** file field |

Renders only when: post type is **Model**, post is **not** tagged **Show Home**, and at least one repeater row has an **`image`**.

### Map

```
[get_the_map_shortcode lat="53.4880694" lng="-113.6870252"]
```

Also accepts: `[copperwood_map]`

Optional attributes: `address`, `title`, `zoom`, `map_type`, `icon`, `class`.

If `lat`/`lng` and `address` are all empty, the map **does not render**.

[add screenshot here]

---

## 7. Recommended publishing order

1. Create **Home type** terms (model names + **Show Home**).
2. Create **Garage** and **Possession date** terms as needed.
3. Set the **Google Maps API key** in Elementor.
4. Add **Models** with details, taxonomies, and floorplan repeater rows.
5. Add **Quick Possession** posts with details and taxonomies.

[add screenshot here]

---

## 8. Quick checklist

| Step | Action |
| ---- | ------ |
| 1 | Create taxonomy terms: Home type, Garage, Possession date |
| 2 | Set Elementor **Google Maps API key** |
| 3 | Models: fill details + assign Home type and Garage |
| 4 | Models: add floorplan repeater rows with **name** and **image** per floor |
| 5 | Quick Possession: fill details + assign taxonomies |
| 6 | Show Home: assign **Show Home** term; use Show Home template (no floorplan shortcode) |

[add screenshot here]

---

## 9. Troubleshooting

- **Floorplan section missing on a Model:** Add at least one repeater row with a valid **`image`**. Empty or image-less rows are skipped; if none remain, nothing is shown.
- **No “No floorplans available” message:** By design — unfilled floorplans mean the block is hidden entirely.
- **Wrong floorplan heading:** Check **Home type** — the first non–Show Home term is used. Post **Title** is the fallback.
- **Floorplan still shows on Show Home:** Confirm **Show Home** is assigned under **Home type**; the shortcode suppresses output automatically.
- **Download button missing:** Upload a file in **`floorplan_url`** under main details.
- **Map not showing:** Check Elementor **Google Maps API key** and that the shortcode has `lat`/`lng` or `address`.
- **Repeater panel missing on Models:** Sync ACF field groups (**Custom Fields → Sync**).

[add screenshot here]
