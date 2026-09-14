# WordPress admin: Meyer Homes (content guide)

This theme includes three custom post types for content management:

- **Services** (`service`)
- **Projects** (`project`)
- **Testimonials** (`testimonial`)

Use this as the admin SOP for adding and maintaining content. Page layout is handled separately — this guide covers **content only**.

**Plugin required:** [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/) — field groups sync from the child theme `acf-json` folder.

---

## 1. One-time setup: Project type terms first

Before adding projects, create filter terms:

- **Project type** / `project-type` (used by Projects only)

| Example terms | Purpose |
| ------------- | ------- |
| Renovation, New build, Commercial, etc. | Filter chips on the project gallery listing |

**Add a term:** WordPress Admin → **Project type** → **Add New Project type** → enter name → **Add New Project type**

[add screenshot here]

---

## 2. Per item: Service

1. Go to **WordPress Admin → Services → Add Service**.
2. Fill core post fields:
   - **Title** (shown in featured list and service pages)
   - **Featured Image** (required for homepage featured list — see below)
   - **Order** (Page Attributes) — lower number = earlier in featured list (default sort is menu order ascending)
3. Fill ACF fields in **Service content** (see field map below).
4. **Publish / Update**.

### Featured services (homepage list)

A service appears in the featured list only when **all** of these are true:

- Status is **Published**
- **Featured Service** toggle is **On**
- **Featured Image** is set

Without a featured image, that service is skipped even if Featured Service is on.

### Service content — field map

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Featured Service** | Toggle | On = include in homepage featured list |
| **Short Description** | Text | Short blurb / card summary |
| **Project Overview Description** | Text | Overview line used on service detail |
| **Hero image** | Image | Top hero visual |
| **Intro Gallery** | Gallery | Image set for intro section |
| **Intro** | WYSIWYG | Intro body copy |
| **Purpose Heading** | Text | Purpose section title |
| **Purpose Description** | WYSIWYG | Purpose body |
| **Purpose image** | Image | Purpose section image |
| **What possible** | WYSIWYG | “What’s possible” body |
| **What possible CTA** | Link | Button label + URL |
| **What possible image** | Image | Matching section image |
| **Typical project heading** | Text | Typical projects title |
| **Typical project description** | WYSIWYG | Typical projects body |
| **Typical project list** | WYSIWYG | Bullet / list content |
| **Planning heading** | Text | Planning section title |
| **Planning description** | Text | Planning intro line |
| **Planning CTA** | Link | Planning button |
| **Planning list** | Repeater | One row per badge/label (`Label`) |
| **Planning image** | Image | Image beside planning list |
| **Faq Image** | Image | Image beside FAQ |
| **Faq Content** | Repeater | Rows: **Question** + **Content** |
| **CTA heading** | Text | Bottom CTA title |
| **Cta description** | WYSIWYG | Bottom CTA body |
| **CTA button** | Link | Bottom CTA button |
| **CTA image** | Image | Bottom CTA image |

### Repeater tips

- **Planning list:** add one row per item; fill **Label** only.
- **Faq Content:** each row needs **Question** and **Content**; empty rows do not show useful FAQ items.

[add screenshot here]

---

## 3. Per item: Project

1. Go to **WordPress Admin → Projects → Add Project**.
2. Fill core post fields:
   - **Title**
   - **Featured Image** (used on the project gallery listing grid — required for that project to show there)
3. Assign taxonomy:
   - **Project type** — one or more terms (powers gallery filters)
4. Fill ACF fields in **Projects content**:

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Listing image size** | Select | Grid span on the listing gallery (see below) |
| **Project Overview** | WYSIWYG | Overview copy on the project page |
| **Featured Partner** | Text | Partner name callout |
| **Location** | Text | Project location |
| **Duration** | Text | Timeline / duration text |
| **Challenges & Solutions** | WYSIWYG | Challenges section body |
| **Challenges & Solutions Image** | Image | Matching image |
| **Key Outcomes** | WYSIWYG | Outcomes body |
| **Partners logo** | Gallery | Partner logo images (pill / logo strip) |
| **Project Gallery** | Gallery | Photo gallery on the project detail page |

5. **Publish / Update**.

### Listing image size (gallery grid)

Controls how wide the **Featured Image** tile is on the projects listing:

| Value | Layout effect |
| ----- | ------------- |
| **Regular (1 columns)** | Default / single-column span |
| **Medium (2 / 3 columns)** | Wider tile |
| **Large (1 full row)** | Full-row hero tile |

Default is **Regular** if left unset.

### Project gallery listing rules

- Listing uses **Featured Image**, not **Project Gallery**.
- **Project Gallery** and **Partners logo** are for the **single project** page.
- Assign **Project type** so filter chips can group the project; untyped projects still list but may not match a filter.

[add screenshot here]

---

## 4. Per item: Testimonial

1. Go to **WordPress Admin → Testimonials → Add Testimonial**.
2. Fill core post fields:
   - **Title** (admin label only — front end prefers ACF **Name**)
   - **Featured Image** (optional avatar / photo)
   - **Order** (Page Attributes) — controls scroll / masonry order (menu order ascending by default)
3. Fill ACF fields in **Testimonial Content**:

| Field | Type | What to enter |
| ----- | ---- | ------------- |
| **Name** | Text | Person or company name shown on the card |
| **Position** | Text | Role / subtitle (e.g. Homeowner, Project Manager) |
| **Content** | WYSIWYG | Quote / review body |

4. **Publish / Update**.

All published testimonials are pulled into the reviews masonry. Keep **Name** + **Content** filled so cards are readable.

[add screenshot here]

---

## 5. Where content appears (for editors)

You do **not** need Elementor to add content. Layout is already wired. For reference:

| Content | Front-end use |
| ------- | ------------- |
| Services with **Featured Service** + Featured Image | Homepage featured services list |
| Service ACF fields | Service single / detail sections |
| Projects + Featured Image + Project type | Project gallery listing |
| Project ACF fields | Project single (overview, partners logos, gallery, etc.) |
| Testimonials ACF fields | Reviews masonry |

---

## 6. Recommended publishing order

1. Create **Project type** terms.
2. Add **Services** (set Featured Image + Featured Service for homepage picks; set **Order**).
3. Add **Projects** (Featured Image, Project type, listing size, detail fields + galleries).
4. Add **Testimonials** (Name, Position, Content; set **Order**).
5. Publish and view front end to confirm listing / detail pages.

[add screenshot here]

---

## 7. Quick checklist

| # | Task |
| - | ---- |
| 1 | Create **Project type** terms |
| 2 | Service: Title + Featured Image; toggle **Featured Service** if needed; fill **Service content** |
| 3 | Service: set **Order** for featured list sequence |
| 4 | Project: Title + Featured Image + **Project type** + **Listing image size** |
| 5 | Project: fill overview / location / galleries (**Partners logo**, **Project Gallery**) |
| 6 | Testimonial: **Name**, **Position**, **Content**; set **Order** |
| 7 | Publish and spot-check homepage list, project gallery, and reviews |

[add screenshot here]

---

## 8. Troubleshooting

- **Service missing from featured homepage list:** Turn **Featured Service** On, set a **Featured Image**, and confirm status is **Published**. Missing image = skipped.
- **Featured list order wrong:** Edit each Service → **Page Attributes → Order** (lower first).
- **Project missing from gallery listing:** Set **Featured Image** and **Publish**. Listing does not use **Project Gallery** images.
- **Project filter chip empty / wrong:** Assign **Project type** terms; create missing terms under **Project type**.
- **Listing tile too small/large:** Change **Listing image size** on that Project.
- **Partner logos not showing:** Upload images to **Partners logo** gallery on the Project.
- **Project detail photos empty:** Fill **Project Gallery** (separate from Featured Image).
- **Review card blank or incomplete:** Fill ACF **Name** and **Content** (Title alone is not enough for the card body).
- **Reviews order wrong:** Set **Order** on each Testimonial.
- **ACF panels missing in admin:** Confirm ACF plugin is active, then **Custom Fields → Sync** any field groups waiting from the theme.

[add screenshot here]
