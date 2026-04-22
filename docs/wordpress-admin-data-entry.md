# WordPress admin: Amenities Map & Our Homes (content guide)

This theme includes two custom post types for content management:

- **Amenities Map** (`amenity`)
- **Our Homes** (`our-home`)

Use this as the admin SOP for adding and maintaining content.

---

## 1. One-time setup: taxonomies first

Before adding posts, make sure these taxonomies and terms exist:

- **Neighbourhood** (used by both Amenities Map and Our Homes)
- **Types** / `amenity-type` (used by Amenities Map)
- **Home Types** / `home-type` (used by Our Homes)
- **Listing Types** / `listing-type` (used by Our Homes)

[add screenshot here]

---

## 2. One-time setup: Neighbourhood map location

1. Go to **WordPress Admin -> Neighbourhood**.
2. Add or edit each neighbourhood term.
3. Fill ACF field **`Location`** (Google Map) on the term.
4. **Save**.

This term-level location controls the default map center when map shortcodes are filtered by neighbourhood.

[add screenshot here]

---

## 3. Per item: Amenities Map

1. Go to **WordPress Admin -> Amenities map -> Add Amenity**.
2. Fill core content:
   - **Title** (used as marker/list title)
   - **Featured Image** (recommended; used in map popup thumbnail)
   - ACF **`Location`** (Google Map; required for marker placement)
3. Assign taxonomies:
   - **Neighbourhood**
   - **Types** (`amenity-type`)
4. **Publish / Update**.

[add screenshot here]

### Optional: marker color per Amenity Type

- Theme code reads a term field named **`color`** on `amenity-type`.
- If your ACF term fields include it, set a color per type for consistent marker colors.
- If empty, markers fall back to default color.

[add screenshot here]

---

## 4. Per item: Our Homes

1. Go to **WordPress Admin -> Our Homes -> Add Our Home**.
2. Fill core post fields:
   - **Title**
   - **Featured Image**
3. Assign taxonomies:
   - **Neighbourhood**
   - **Home Types** (`home-type`)
   - **Listing Types** (`listing-type`)
4. Fill ACF fields in **Home details**:
   - **`Name`** (short display name)
   - **`short_name`** (detail/sub name)
   - **`Price`**
   - **`Bedroom`**
   - **`Bathroom`**
   - **`Size`** (Sq. Ft.)
   - **`map_location_url`**
   - **`custom_cta`** (optional URL override)
   - **`Description`**
   - **`Key Features`**
   - **`gallery_sliders`** (image gallery)
5. Fill repeater **`floorplans`** (one row per floorplan):
   - **`title`**
   - **`size_floorplan`**
   - **`picture`**
6. **Publish / Update**.

If no floorplans are entered, frontend fallback text is: **"No floorplans available."**

[add screenshot here]

---

## 5. Recommended publishing order

1. Create **Neighbourhood** terms and set each term **`Location`**.
2. Create **Types** (`amenity-type`) terms.
3. Create **Home Types** and **Listing Types** terms.
4. Add **Amenities Map** posts with location + taxonomy assignment.
5. Add **Our Homes** posts with ACF fields + floorplans.

[add screenshot here]

---

## 6. Quick checklist

| Step | Action |
| ---- | ------ |
| 1 | Create taxonomy terms: Neighbourhood, Types, Home Types, Listing Types |
| 2 | Set Neighbourhood term **`Location`** (map center source) |
| 3 | Amenities: fill Title + Location + assign Neighbourhood and Types |
| 4 | Our Homes: fill Home details fields + assign taxonomies |
| 5 | Floorplans: add repeater rows with title, size, and picture |

[add screenshot here]

---

## 7. Troubleshooting

- **Amenity marker not showing:** Check post ACF **`Location`** has valid lat/lng.
- **Wrong map center:** Check selected Neighbourhood term ACF **`Location`**.
- **Empty map after filtering:** Ensure amenity has both `amenity-type` and `neighbourhood` assigned.
- **Our Homes floorplans not rendering:** Ensure at least one `floorplans` row has a valid **`picture`**.
