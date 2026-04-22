# Product page: model info popover (merchant guide)

This theme can show a **model information** control on the **product image gallery**. Shoppers tap or click it to open a short popover with text you store on the product. The control appears **only on the gallery image you choose** (for example the photo where the model is visible).

There is **no theme editor toggle** for this; everything is configured in **Settings** and on each **product**.

---

## 1. One-time setup: product metafields

1. Go to **Settings → Custom data → Products**.
2. Ensure these metafields exist on the **Product** resource (or create them with the same namespace and key):

### A. Model copy (text)

- **Name (label):** e.g. `Maat model` / `Model copy`
- **Namespace and key:** **`custom.maat_model`**
  - Namespace: `custom`
  - Key: `maat_model`
- **Type:** **Single line text** (or compatible text type your store already uses).
- **Content:** The sentence or line shown in the popover (e.g. model height and size worn). You can edit this per product.

### B. Gallery image index (optional)

- **Name (label):** e.g. `Model indicator image`
- **Namespace and key:** **`custom.model_position`**
  - Namespace: `custom`
  - Key: `model_position`
- **Type:** **Integer** (whole number).
- **Meaning:** **Which image in the product gallery** should show the button, counting from **1** in **admin media order**:
  - `1` = first image
  - `2` = second image
  - `3` = third image

If you leave this **empty**, the theme defaults to **`1`** (first image).

Where the button sits **on that image** (e.g. corner) is defined in the theme **CSS**, not in the admin.

---

## 2. Per product

1. Open **Products** and choose a product.
2. In **Media**, note the order of images: the **first** thumbnail is **1**, the next is **2**, etc.
3. Scroll to **Metafields**.
4. Fill **`custom.maat_model`** with the text shoppers should see.
5. Optionally set **`custom.model_position`** to the image number where the model appears (e.g. `3`), or leave empty for the first image.
6. **Save**.

If **`maat_model`** is empty, the indicator does not show and no extra assets load for that product.

---

## 3. Storefront text (translations)

The **button** accessible label uses the theme string **`product.model_indicator.open`** (e.g. “Model information” / “Modelinformatie”). You can change it under **Online store → Themes → … → Edit default theme content** or in locale files, depending on your workflow.

The **popover body** is exactly what you save in **`custom.maat_model`**, not a separate theme sentence.

---

## 4. Quick checklist

| Step | Action                                                                          |
| ---- | ------------------------------------------------------------------------------- |
| 1    | Product metafield **`custom.maat_model`** — text to show in the popover         |
| 2    | Optional: **`custom.model_position`** — image number (1 = first, 2 = second, …) |
| 3    | On each product: fill `maat_model` (+ position if needed) → **Save**            |

---

## 5. Troubleshooting

- **Popover does not show:** Check that **`custom.maat_model`** is filled for that product.
- **Button on wrong slide:** **`custom.model_position`** must match **media order** in admin. Reorder images or change the number.
- **Variant / filtered gallery:** Some setups hide certain images per variant. If the chosen image is hidden for the selected variant, the indicator is hidden with that slide.
- **Wrong namespace/key:** The theme expects **`custom.maat_model`** and **`custom.model_position`**. If your definitions differ, ask your developer to align the theme.
