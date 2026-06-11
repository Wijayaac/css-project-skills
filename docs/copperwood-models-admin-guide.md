# Copperwood admin: Models, Quick Possession & floorplans

Guide for editors adding **Models** and **Quick Possession** homes in WordPress.

---

## 1. One-time setup: taxonomy terms

Create terms before assigning them to posts.

### Home Types (`home-type`)

Used on **Models** and **Quick Possession**. You can assign **more than one** term to a single post.

| Term | Purpose |
| ---- | ------- |
| **Model names** (e.g. *The Aspen*, *The Birch*) | Shown as the floorplan heading and used for filtering |
| **Show Home** (slug: `show-home`) | Marks a move-in ready / show home listing. Uses the **Show Home** Elementor template and **does not** show the floorplan tabs block |

**Add a term:** WordPress Admin → **Home type** → **Add New Home type** → enter name → **Add New Home type**

### Garage (`garage`)

Optional filter term for Models and Quick Possession.

### Possession date (`possesion-date`)

Quick Possession only — used for move-in timing filters.

[add screenshot here]

---

## 2. Per item: Model (with floorplans)

1. Go to **WordPress Admin → Models → Add Model**
2. Fill core fields:
   - **Title** (internal/admin label; floorplan heading uses **Home type** when set)
   - **Featured Image**
   - **Models & Quick Possesion details** (price, bedrooms, bathrooms, square feet, gallery, etc.)
3. Assign taxonomies on the right:
   - **Home type** — pick the model name (e.g. *The Aspen*). Add extra terms if needed (e.g. a marketing tag). Do **not** use **Show Home** on standard models.
   - **Garage** (optional)
4. Scroll to **Model details [Floorplan]** and add repeater rows:

| Repeater column | What to enter | Example |
| --------------- | ------------- | ------- |
| **Name** | Floor label + size (subtitle and tab text) | `Main Floor / 660 sq. ft.` |
| **Image** | Floorplan image for that level | PNG or JPG |

5. Click **Add Row** for each floor (Main, Second, Basement, etc.)
6. Optional: upload a PDF in **Floorplan** (file field under main details) for the download button
7. **Publish / Update**

### Name field tips

- Text **before** `/` or `-` becomes the **tab label** (e.g. `MAIN FLOOR`)
- Full **Name** value is shown as the **subtitle** under the heading (uppercase on the front end)
- Each row **must** have an **Image** or it will not appear on the site

[add screenshot here]

---

## 3. Per item: Show Home (no floorplan block)

Use this for furnished / move-in ready homes that use a different page layout.

1. Go to **Quick Possession** (or **Models** if applicable) → add or edit the post
2. Assign **Home type → Show Home**
3. You may also assign a **model name** term if you want that name available for filters — the floorplan block stays hidden while **Show Home** is assigned
4. Fill **Models & Quick Possesion details** (address, price, gallery, video, etc.)
5. **Do not** rely on the floorplan repeater for Show Home posts — the front end hides `[copperwood_floorplans_v2]` automatically
6. **Publish / Update**

In Elementor, the **Show Home** single template should omit the floorplan shortcode (or use a condition: hide when `home-type` = Show Home).

[add screenshot here]

---

## 4. Per item: Quick Possession (standard)

Same as Models for taxonomies and main ACF fields, plus:

- **Possession date** taxonomy (optional)
- Floorplan repeater is **not** on this post type by default — only **Models** have the **Model details [Floorplan]** field group

[add screenshot here]

---

## 5. Front-end shortcode

Floorplan tabs render only when:

- Post type is **Model**
- Post is **not** tagged **Show Home**
- At least one repeater row has an image

Shortcode for Elementor:

```
[copperwood_floorplans_v2]
```

| UI element | Data source |
| ---------- | ----------- |
| Heading (e.g. *The Aspen*) | First **Home type** term that is not Show Home |
| Subtitle (e.g. *MAIN FLOOR / 660 SQ. FT.*) | Repeater **Name** |
| Tab labels | Part of **Name** before `/` or `-` |
| Stats line | **Square feet**, **Bedrooms**, **Bathrooms** |
| Download button | **Floorplan** file field |

---

## 6. Quick checklist

| Step | Action |
| ---- | ------ |
| 1 | Create **Home type** terms (model names + **Show Home**) |
| 2 | Model: assign home type + fill details |
| 3 | Model: add **Floorplan** repeater rows (name + image per floor) |
| 4 | Show Home: assign **Show Home** term; use Show Home template (no floorplan shortcode) |
| 5 | Publish and view the single post |

---

## 7. Troubleshooting

- **Floorplan block empty on a Model:** Add at least one repeater row with an **Image**. Sync ACF field groups if the repeater panel is missing (Custom Fields → sync).
- **Wrong heading:** Check **Home type** — the first non–Show Home term is used. Post **Title** is the fallback.
- **Floorplan still shows on Show Home:** Remove repeater rows or confirm **Show Home** is checked under **Home type**.
- **Multiple home types:** You can select several; Show Home hides floorplans; the first other term supplies the heading.
