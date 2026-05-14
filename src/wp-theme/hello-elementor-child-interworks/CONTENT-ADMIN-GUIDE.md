# Interworks Contracting — Content admin guide

Buddy here — this guide is for anyone entering **Projects**, **Expertise**, **Services**, and **Teams** in WordPress. The public layout is built in **Elementor**; structured fields come from **ACF (Advanced Custom Fields)**. Use the [Interworks Contracting Figma file](https://www.figma.com/design/P8sW7yVVNC0jpSkZNB8DSG/Interworks-Contracting?node-id=7-140&p=f) as the visual reference for what each block is meant to show.

Where you see **`[place screenshot here]`**, drop in an image from your site (admin screen, Elementor panel, or front end) and save the markdown file or export to PDF for your client.

---

## Before you start

| Requirement | Why it matters |
|---------------|----------------|
| **ACF** (field groups active) | Custom fields appear on each post type. |
| **Elementor** | Pages and templates are designed there. |
| **Custom post types registered** | You should see **Projects**, **Expertise**, **Services**, and **Teams** in the admin menu. If any are missing, ask your developer to sync ACF (Local JSON) or restore CPT definitions — field groups in this theme expect those post types. |

**[place screenshot here]** *(e.g. left admin sidebar showing Projects, Expertise, Services, Teams)*

---

## Big picture: how content types relate

There is **no** WordPress “post object” field connecting a Project to an Expertise or Service post. Instead, **Projects use taxonomies** (shared tags). You maintain **Expertise** and **Services** as their own post types for the marketing copy and layout, and you maintain **parallel taxonomy terms** (Expertise type, Services type) that you assign on each project. Elementor can then query projects by those terms—without a relationship field in the editor. **Client Category** is the same idea: optional taxonomy terms on projects for extra grouping (for example by industry).

**Practical rule:** For each **Expertise** or **Service** you want to filter or display with projects, create a **taxonomy term** with a clear name (and keep naming consistent with the related CPT entry). On each **Project**, assign the terms that describe that job, including **Client Category** when you use that grouping.

**[place screenshot here]** *(e.g. front-end project listing filtered by expertise or service, or project editor with taxonomies visible)*

---

## Taxonomies (on Projects only)

These appear in the project editor sidebar (like categories). They are **hierarchical** (you can use parent/child terms if you need groups).

| Taxonomy (admin label) | Machine slug | Used for |
|------------------------|--------------|----------|
| Expertise type | `expertise-type` | Links a project to one or more “expertise” areas (aligned with **Expertise** content). |
| Services type | `service-type` | Links a project to one or more “service” lines (aligned with **Services** content). |
| Client Category | `client-category` | Optional extra grouping (e.g. industry or client segment). |

**Managing terms:** In the dashboard, open **Projects → [taxonomy name]** (or the taxonomy screens under **Posts** menu placement depending on ACF menu settings) to add, rename, or reorder terms before assigning them on projects.

**[place screenshot here]** *(e.g. taxonomy list screen or project editor sidebar with Expertise type / Services type / Client Category)*

---

## Projects (`project`)

**Menu:** Projects  

**Built-in WordPress fields**

- **Title** — Project name (often shorter label in cards).
- **Featured image** — Main thumbnail / hero where the design uses it.

**ACF field group: “Project Detail”**

| Field | What to enter |
|-------|----------------|
| Video | Full URL to a video file or embed URL (per your player setup). |
| Project full name | Longer official name if different from the title. |
| City | City text. |
| Location | Region, address line, or “where” descriptor (as designed). |
| Year | Year or date range as plain text. |
| Delivery Model | Short label (e.g. design-build, CM). |
| Content | Rich text for the main case study body (ACF WYSIWYG — not the same as the optional WordPress block editor on the post). |
| Gallery | Image gallery for the project page. |

**Taxonomies on this screen**

- Assign **Expertise type**, **Services type**, and **Client Category** as needed so Elementor loops and grids can query “projects that use this expertise/service.”

**[place screenshot here]** *(e.g. full Project edit screen: title, featured image, ACF Project Detail, taxonomies)*

---

## Expertise (`expertise`)

**Menu:** Expertise  

**Built-in**

- **Title** — Name of the expertise area.
- **Featured image** — Default image for cards/headers if the template uses it.

**ACF field group: “Expertise detail”**

| Field | What to enter |
|-------|----------------|
| Homepage card image | Image used where the homepage card needs a specific crop. |
| Homepage card tagline | Short line under the title on cards. |
| Short text | Brief supporting line. |
| Content heading | Section heading on the expertise page. |
| Content description | Main body copy (WYSIWYG). |
| Expertise point | Repeater: rows of **title** + **description** for bullet-style points. |

**Linking to projects**

- Create or pick matching terms under **Expertise type** and assign those same terms on relevant **Projects**. That is how listings stay in sync without a direct post-to-post link.

**Shortcode (optional)**

- Where the design expects the repeater as a grid, editors may use: `[expertise_points_grid]`  
  It outputs the **Expertise point** repeater for the **current post** in context (works when the page/post in view is the expertise entry or when loop context is set correctly — if it shows empty, your developer can wire the template).

**[place screenshot here]** *(e.g. Expertise edit screen with ACF fields + Expertise point repeater rows)*

---

## Services (`service`)

**Menu:** Services  

**Built-in**

- **Title** — Service name.
- **Featured image** — Visual for listings/detail as designed.

**ACF field group: “Service detail”** (stored in theme ACF JSON; sync in ACF if you do not see it)

| Field | What to enter |
|-------|----------------|
| Short Text | Short intro line. |
| Content Heading | Section heading. |
| Content description | Main body (WYSIWYG). |
| Alternative image | Secondary image where the layout swaps art. |

**Linking to projects**

- Use **Services type** terms on **Projects** that were delivered under that service.

**[place screenshot here]** *(e.g. Service edit screen with Service detail fields)*

---

## Teams (`team`)

**Menu:** Teams  

**Built-in**

- **Title** — Person’s name.
- **Featured image** — Headshot.

**ACF field group: “Team Details”**

| Field | What to enter |
|-------|----------------|
| Role | Job title or role (textarea; may include line breaks). |
| Short Bio | Rich text bio used in modals or detail areas. |

**Front-end note:** The theme registers AJAX for team popups (`ajax_popup_team`). Cards or buttons that open the popup must pass the correct **team post ID** (your developer configures this in Elementor or custom HTML).

**[place screenshot here]** *(e.g. Team edit screen: title, featured image, Role, Short Bio)*

---

## Elementor tips for editors

1. **Query Projects by taxonomy** — In a Posts/Loop/Portfolio widget (depending on your Elementor setup), set the post type to **Projects** and add a **Taxonomy** filter for **Expertise type** or **Services type** (term = the area you are building a page for).
2. **Same term on many projects** — One taxonomy term can sit on many projects; the loop shows all matching projects.
3. **Order of work** — Safe workflow: create **Expertise** / **Services** entries and matching **taxonomy terms**, then create **Projects** and tick the right terms. Renaming a term updates all projects using it.

**[place screenshot here]** *(e.g. Elementor loop/query panel: post type = Projects, taxonomy filter = Expertise type or Services type)*

---

## Design reference

- Figma: [Interworks Contracting — design](https://www.figma.com/design/P8sW7yVVNC0jpSkZNB8DSG/Interworks-Contracting?node-id=7-140&p=f)

**[place screenshot here]** *(e.g. Figma frame or exported PNG of key templates)*

---

## If something is missing in the admin

1. **Custom Fields → Sync available** — After theme updates, sync field groups from Local JSON.  
2. **Post types not listed** — CPT registration may need to be re-saved in ACF or JSON files restored; contact your developer.  
3. **Elementor does not show ACF in a widget** — Some widgets only read standard post data; dynamic tags or custom loops may be required — developer task.

**[place screenshot here]** *(e.g. ACF → Tools → Sync available, or missing menu before/after sync)*

---

*Document version: aligned with Hello Elementor Child + ACF JSON in this theme. Update this file when fields or taxonomies change.*
