# WordPress admin: Granville Models (content guide)

This theme includes one custom post type for content management:

- **Models** (`model`)

Use this as the admin SOP for adding and maintaining content.

---

## 1. One-time setup: taxonomy terms first

Before adding models, make sure this taxonomy and its terms exist:

- **Home types** / `home-type` (used by Models)

| Term                                            | Purpose                                         |
| ----------------------------------------------- | ----------------------------------------------- |
| **Model names** (e.g. _The Aspen_, _The Birch_) | Shown as the floorplan heading on the front end |

**Add a term:** WordPress Admin → **Home type** → **Add New Home type** → enter name → **Add New Home type**

[add screenshot here]

---

## 2. Per item: Model (with floorplans)

1. Go to **WordPress Admin → Models → Add Model**.
2. Fill core post fields:
   - **Title** (admin label; floorplan heading uses **Home type** when set)
   - **Featured Image**
3. Assign taxonomy:
   - **Home type** — pick the model name (e.g. _The Aspen_).
4. Fill ACF fields in **Models details**:
   - **`address`**
   - **`bedrooms`**
   - **`bathrooms`**
   - **`square_feet`**
   - **`floorplan_url`** (optional PDF; can be linked from the Elementor template)
5. Scroll to **Model details [Floorplan]** and add repeater rows:

| Repeater column | What to enter                              | Example                    |
| --------------- | ------------------------------------------ | -------------------------- |
| **`name`**      | Floor label + size (subtitle and tab text) | `Main Floor / 660 sq. ft.` |
| **`image`**     | Floorplan image for that level             | PNG or JPG                 |

6. Click **Add Row** for each floor (Main, Second, Basement, etc.).
7. **Publish / Update**.

### Name field tips

- Text **before** `/` or `-` becomes the **tab label** (e.g. `MAIN FLOOR`).
- Full **`name`** value is shown as the **subtitle** under the heading (uppercase on the front end).
- Each row **must** have both **`name`** and **`image`** filled in, or that floor will not appear on the site.

### If floorplans are not filled in

- If the **Floorplan** repeater is empty, or any row is missing **`name`** or **`image`**, **no floorplan section is shown** on the front end. The page will not display an empty floorplan block or a fallback message.

[add screenshot here]

---

## 3. Front-end shortcode

Floorplan tabs render only when **all** of the following are true:

- Post type is **Model**
- At least one repeater row has both **`name`** and **`image`** filled in

Shortcode for Elementor:

```
[granville_floorplans_v2]
```

Optional: pass a specific post ID — `[granville_floorplans_v2 post_id="123"]`

| UI element                                 | Data source                                        |
| ------------------------------------------ | -------------------------------------------------- |
| Heading (e.g. _The Aspen_)                 | **Home type** term (falls back to post **Title**)  |
| Subtitle (e.g. _MAIN FLOOR / 660 SQ. FT._) | Repeater **`name`**                                |
| Tab labels                                 | Part of **`name`** before `/` or `-`               |
| Stats line                                 | **`square_feet`**, **`bedrooms`**, **`bathrooms`** |

If requirements are not met, **nothing is output** — no floorplan block appears.

[add screenshot here]

---

## 4. Recommended publishing order

1. Create **Home type** terms (model names).
2. Add **Models** with home type, **Models details** fields, and floorplan repeater rows.
3. Place `[granville_floorplans_v2]` on the Model single template in Elementor.

[add screenshot here]

---

## 5. Quick checklist

| Step | Action                                                              |
| ---- | ------------------------------------------------------------------- |
| 1    | Create **Home type** terms (model names)                            |
| 2    | Model: assign home type + fill **Models details**                   |
| 3    | Model: add **Floorplan** repeater rows (`name` + `image` per floor) |
| 4    | Publish and view the single post                                    |

[add screenshot here]

---

## 6. Troubleshooting

- **No floorplan block on a Model:** Add at least one repeater row with both **`name`** and **`image`**. If the repeater is empty or any row is incomplete, the theme outputs nothing — this is expected.
- **Wrong heading:** Check **Home type** is assigned. Post **Title** is used as the fallback.
- **Floorplan repeater panel missing:** Go to **Custom Fields** and sync field groups from the theme if needed.
- **Stats line missing:** Fill **`square_feet`**, **`bedrooms`**, and/or **`bathrooms`** in **Models details**.

[add screenshot here]
