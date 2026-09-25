# WordPress admin: Floorplans (content guide)

This theme uses one custom post type for home / floorplan pages:

- **Floorplans** (`floorplans`)

Content lives in the **Home details** fields on each floorplan. The site layout (Elementor) is already set up — you only add or edit floorplans in WordPress admin.

**Plugins needed (already installed on a normal handoff):** Advanced Custom Fields (ACF) and Elementor. Field groups sync from the child theme `acf-json` folder.

Use this as the admin SOP for adding and maintaining floorplans.

---

## 1. One-time setup: Floorplan Category terms first

Before adding floorplans, create the categories you will use to group them:

- **Floorplan Category** / `floorplan-categories` (used by Floorplans only)

| Example terms | Why |
| ------------- | --- |
| Single family, Townhome, Condo, etc. | Helps organize the Floorplans list in admin (and any front-end filters wired to this taxonomy) |

1. Go to **WordPress Admin → Floorplans → Floorplan Category**.
2. Add each category name.
3. **Add New Floorplan Categories** / save.

[please screenshot of: Floorplans → Floorplan Category screen with a few example terms]

---

## 2. Per item: Add or edit a Floorplan

1. Go to **WordPress Admin → Floorplans → Add Floorplan** (or open an existing one to edit).
2. Fill core WordPress fields:
   - **Title** (required — this is the floorplan / home name)
   - **Featured Image** (recommended — used wherever the theme shows a main thumbnail)
3. Assign **Floorplan Category** (pick one or more terms you created in section 1).
4. Fill the **Home details** ACF sections below (tabs on the edit screen).
5. **Publish / Update**.

[please screenshot of: Floorplans edit screen showing Title, Featured Image, Floorplan Category, and Home details box]

---

## 3. Home details — field map

Fields appear in tabs on the Floorplan edit screen. Fill what the page needs; leave unused fields empty.

### Basics (top of Home details)

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Short Description** | Text area | Short blurb for cards / intros |
| **SQ.FT** | Number | Living area (square feet) |
| **Bed** | Number | Bedroom count |
| **Bath** | Number | Bathroom count |
| **Normal price** | Number | Regular / list price |
| **Sale Price** | Number | Sale / promo price (use `0` or leave empty if none) |

[please screenshot of: Home details basics — Short Description, SQ.FT, Bed, Bath, prices]

---

### Tab: Floorplan details

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Lightbox floorplan** | Image | Image used for the zoom / lightbox view. **Must be an image (JPG/PNG), not a PDF.** Convert PDF → image first if needed. |
| **Floorplan subtitle** | Text | Small line above or near the heading |
| **Floorplan Heading** | Text | Main floorplan section title |
| **Floorplan PDF** | File | Optional downloadable PDF |
| **Floorplan Description** | Rich text | Longer copy under the heading |
| **Floorplan Images** | Gallery | Images for the floorplan gallery (main stage + thumbnails on the site) |

**Important:** Lightbox does **not** support PDFs. Convert the PDF floor plan to an image first (for example with a PDF → JPG converter), then upload that image to **Lightbox floorplan**.

[please screenshot of: Floorplan details tab — lightbox image, heading fields, PDF, description, Floorplan Images gallery]

---

### Tab: Room Dimensions

Use the **Room dimensions** repeater — one row per room.

| Sub-field | What to enter |
| --------- | ------------- |
| **Room** (`name`) | Room label (e.g. `Living & Dining Room`) |
| **Dimension** (`dimension`) | Measurement (e.g. `12'-10" x 13'-0"`) |

Tips:

- Click **Add Room** for each room.
- Rows with an empty **Room** or **Dimension** are **hidden on the front end**.
- Order of rows = order shown on the site.

[please screenshot of: Room dimensions repeater with a few filled rows]

---

### Tab: GALLERY Images

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Top Gallery** | Gallery | Photos for the upper / primary photo strip |
| **Bottom Gallery** | Gallery | Photos for the lower photo strip |

Add images in the order you want them to appear.

[please screenshot of: Top Gallery and Bottom Gallery fields with images added]

---

### Tab: Features & High-End Finishes

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Description** | Text area | Intro copy for the features section |
| **Features items** | Repeater | One row per feature / finish |

**Features items** rows:

| Sub-field | What to enter |
| --------- | ------------- |
| **Name** | Feature text (e.g. `Quartz countertops`) |

Click **Add Row** for each item. Empty names do not help the checklist on the site.

[please screenshot of: Features tab — Description plus Features items repeater]

---

## 4. How this content shows on the site

You do **not** need to edit Elementor to add a new floorplan. After you **Publish**, the single floorplan page pulls from **Home details**.

For reference only (already wired by the developer):

| What you fill in admin | What visitors see |
| ---------------------- | ----------------- |
| Title + Featured Image + basics (sqft, bed, bath, price…) | Floorplan header / summary areas |
| **Floorplan Images** | Floorplan gallery (stage + thumbnails + lightbox) |
| **Room dimensions** repeater | Room / dimension list |
| **Features items** repeater | Features checklist |
| **Top Gallery** / **Bottom Gallery** | Photo galleries on the page |
| **Floorplan PDF** | Download link (when a file is uploaded) |

[please screenshot of: published floorplan page on the front end with gallery, rooms, and features visible]

---

## 5. Recommended publishing order

1. Create **Floorplan Category** terms.
2. Add a Floorplan: **Title** + **Featured Image** + assign a category.
3. Fill **basics** (short description, sqft, bed, bath, prices).
4. Fill **Floorplan details** (lightbox image, headings, PDF if needed, description, **Floorplan Images**).
5. Add **Room dimensions** rows.
6. Upload **Top Gallery** / **Bottom Gallery** photos.
7. Add **Features items** (+ features description).
8. **Publish** and spot-check the live page.

[please screenshot of: Floorplans list in admin with a few published items]

---

## 6. Quick checklist

| # | Task |
| - | ---- |
| 1 | Create **Floorplan Category** terms |
| 2 | Floorplan: set **Title** + **Featured Image** |
| 3 | Assign **Floorplan Category** |
| 4 | Fill basics: short description, SQ.FT, Bed, Bath, prices |
| 5 | Floorplan details: lightbox **image** (not PDF), headings, description, **Floorplan Images** |
| 6 | Optional: upload **Floorplan PDF** for download |
| 7 | Add **Room dimensions** rows (Room + Dimension both filled) |
| 8 | Upload **Top Gallery** and/or **Bottom Gallery** |
| 9 | Add **Features items** (+ features Description) |
| 10 | Publish and check the front-end page |

---

## 7. Troubleshooting

- **Floorplan missing from the site:** Confirm status is **Published** (not Draft).
- **Lightbox / zoom looks wrong or empty:** **Lightbox floorplan** must be an **image**. PDFs will not work there — convert first, then re-upload.
- **Floorplan gallery empty:** Add at least one image to **Floorplan Images**.
- **Room list empty or missing a row:** Each row needs both **Room** and **Dimension**. Empty rows are skipped on the front end.
- **Features checklist empty:** Add **Features items** rows with **Name** filled.
- **Photo strips empty:** Fill **Top Gallery** and/or **Bottom Gallery**.
- **Download button missing:** Upload a file to **Floorplan PDF**.
- **Wrong grouping in admin:** Edit the Floorplan and re-assign **Floorplan Category**.

[please screenshot of: example troubleshooting — Draft vs Published status on a Floorplan]
