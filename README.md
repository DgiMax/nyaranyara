# Nyara Nyara Nyara — Landing Site

Official waitlist / support landing page for **Nyara Nyara Nyara**, a Kenyan comedy-drama by **DGI COLLEKTIVE**.

This is a static site (HTML / CSS / JS) plus one PHP file for waitlist signups. It is hosted on **Hostinger** using **File Manager** uploads (Git deploy may be unavailable on some Hostinger accounts).

---

## What’s on the page

| Section | Purpose |
|--------|---------|
| Hero | Film title, short pitch, waitlist CTA, rotating “why join” lines |
| Storyline | Synopsis + key film facts |
| Waitlist sidebar | Email / WhatsApp signup (sticky on desktop) |
| On the slate | Production path: Script → Casting → Finance → Shoot → Premiere |
| Stages accordion | Completed stages (latest open); mixed image/video media |
| Cast | Character placeholders (Jimmy, Lory, Jitu, TBA) |
| Support | Waitlist / social / platform counts |
| Footer | Email + socials + production credit |

---

## Folder structure

```
nyaranyara/
├── index.html          # The whole page
├── waitlist.php        # Receives waitlist signups
├── css/style.css
├── js/script.js
├── images/             # WebP stills (MP4 clips optional)
├── data/
│   ├── waitlist.csv    # Saved signups (not public)
│   └── .htaccess       # Blocks browser access to data/
└── README.md
```

On Hostinger, these files live inside **`public_html`** (or your subdomain folder).  
`index.html` and `waitlist.php` must be at the **root** of that folder — not inside another `nyaranyara/` folder.

---

## Handover: how to update the live site (File Manager)

GitHub is useful as a backup of the code. **Live updates on Hostinger are done by uploading files** in File Manager.

### Step 1 — Prepare media first

Before you edit the page:

1. Add or replace images/videos in the local `images/` folder.
2. Use **web-friendly formats** only:
   - Photos / stills → **WebP** (preferred) or compressed JPG
   - Video clips → **MP4**
3. Keep files **small** so the site stays fast on mobile:
   - Stills: aim under ~300–500 KB each when possible
   - Videos: compress before upload; avoid multi‑GB files
4. Keep the **same filenames** if you are replacing existing media  
   (e.g. `nyara-hero.webp`, `scriptandcast1.webp`) so you do not have to change every path in HTML.

### Step 2 — Update `index.html`

Edit the local `index.html` for copy, links, slate status, cast, support counts, etc.  
(See “What to edit in the content” below.)

Also update `css/style.css` or `js/script.js` only if design or behaviour changed.

### Step 3 — Zip the project folder

1. Select the site files that should go live:
   - `index.html`
   - `waitlist.php`
   - `css/`
   - `js/`
   - `images/`
   - `data/.htaccess` (keep this)
2. **Do not** include:
   - `.git/`
   - this README (optional to include)
   - random zips or desktop junk
3. Create a zip of those files / folders.

**Important about `data/waitlist.csv`:**  
If the live site already has real signups, **do not overwrite** the live CSV with an empty local copy. Skip uploading `waitlist.csv` on updates unless you intentionally mean to replace it.

### Step 4 — Upload on Hostinger and overwrite

1. Log in to Hostinger **hPanel**
2. Open **Files → File Manager**
3. Go into **`public_html`** (or the subdomain’s folder)
4. Upload the zip
5. **Extract** it into that same folder
6. When Hostinger asks about existing files → choose **Overwrite / Replace**
7. Confirm the structure looks like:

```text
public_html/
  index.html
  waitlist.php
  css/
  js/
  images/
  data/
```

Not:

```text
public_html/
  nyaranyara/
    index.html   ← wrong (extra folder)
```

If you extracted into an extra folder by mistake, move all files up into `public_html`.

### Step 5 — Delete the zip

After a successful extract, **delete the uploaded `.zip`** from File Manager so it does not sit on the server.

### Step 6 — Quick test

1. Open the live site URL (hard refresh if needed)
2. Check the sections you changed
3. Submit one test waitlist signup
4. Confirm it appears in File Manager → `data/waitlist.csv` and/or the notification email

---

## Small updates (without a full zip)

You do **not** always need to zip the whole site.

| What changed | Upload only |
|--------------|-------------|
| Text / links / counts | `index.html` |
| Styling | `css/style.css` |
| Form / animations | `js/script.js` |
| Waitlist backend | `waitlist.php` |
| One image or video | that file inside `images/` |

Upload with the **same filename** → choose **Overwrite**.  
You do **not** need to delete the old file first.

---

## What to edit in the content

### 1. On the slate (where we are)

Find the block with class `slate-path`.

- Move `class="is-now"` to the current step
- Change step labels (`Done` / `Now` / `Next` / `Ahead` / `Goal`)
- Update each `slate-note`
- Update the intro under **On the slate** when the stage changes

### 2. Stages accordion (progress updates)

Section `#done` (`.stages`) is an **accordion**. Only one stage is open at a time. The **latest** stage should sit at the **top** and have `class="stage is-open"`.  
If there is **only one** stage, the accordion is disabled automatically (always open, no chevron).

**To add a new completed stage:**
1. Copy an existing `<article class="stage">…</article>`
2. Paste it **above** the older stages
3. Put `is-open` only on the new (top) stage; remove `is-open` from the others
4. Give the button/panel unique `id` values
5. Update the title (`.stage-label`) and copy (`.stage-copy`)
6. Fill `.stage-media` with images and/or videos

**Media ratios** — add one class per item so layout stays premium:
- `stage-media-item--landscape` → wide (16:9)
- `stage-media-item--portrait` → tall (3:4)
- `stage-media-item--square` → 1:1

You can mix images and videos in any combination. On large screens all items share the **same height**; width adapts to the ratio. Use compressed **WebP** / **MP4**.

### 3. Support numbers

- **Waitlist** count updates automatically from `waitlist.php` when people join — do not hardcode it.
- Edit `data-count` only for **social** and **platforms**:

```html
<div class="stat stat--social">
  <p class="stat-value" data-count="1">0<span>+</span></p>
</div>
<div class="stat stat--platforms">
  <p class="stat-value" data-count="5">0</p>
</div>
```

The page animates from `0` to the `data-count` value.

### 4. Cast / storyline / hero / socials

- Cast names and images in the cast section
- Hero / storyline images in `images/`
- Synopsis in `.story-copy`
- Film facts in `.story-meta`
- Real social URLs for Instagram / TikTok / Facebook / YouTube / LinkedIn
- Footer email and credit

---

## Waitlist (how signups work)

1. Form posts to **`waitlist.php`**
2. Contact is saved in **`data/waitlist.csv`** (file lock so concurrent signups don’t corrupt the file)
3. Live supporter count is updated and returned to the page automatically
4. Notification email goes to **nyara@dgi-collektive.com**

The Support “Waitlist supporters” number is loaded from the server (not hardcoded).  
`data/.htaccess` blocks public downloads of the CSV. Download it only through File Manager.

To change the notification address, edit the top of `waitlist.php`.

**Do not overwrite** live `data/waitlist.csv` or `data/waitlist.count` when uploading site updates.

---

## First-time Hostinger setup

1. Open File Manager → `public_html` (or subdomain folder)
2. Upload / extract the site files as described above
3. Confirm PHP is enabled
4. Confirm the mailbox **nyara@dgi-collektive.com** exists
5. Test one waitlist signup

---

## Local preview

Layout can be checked by opening `index.html` in a browser.  
Waitlist submit needs PHP:

```bash
cd nyaranyara
php -S localhost:8080
```

Then open `http://localhost:8080`.

---

## Quick checklist before each update

- [ ] New images/videos are **WebP / MP4** and **compressed / small**
- [ ] `index.html` (and any CSS/JS) updated locally first
- [ ] Zip prepared **without** `.git` and without wiping live `waitlist.csv`
- [ ] Uploaded to `public_html` → extracted → **overwrite** existing files
- [ ] Uploaded **zip deleted** from the server
- [ ] Live site checked + one waitlist test signup confirmed
