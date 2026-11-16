# Nyara Nyara Nyara — Landing Site

Official waitlist / support landing page for **Nyara Nyara Nyara**, a Kenyan comedy-drama by **DGI COLLEKTIVE**.

This is a static site (HTML / CSS / JS) plus one PHP file for waitlist signups. It is meant to be hosted on **Hostinger**.

---

## What’s on the page

| Section | Purpose |
|--------|---------|
| Hero | Film title, short pitch, waitlist CTA, rotating “why join” lines |
| Storyline | Synopsis + key film facts |
| Waitlist sidebar | Email / WhatsApp signup (sticky on desktop) |
| On the slate | Production path: Script → Finance → Shoot → Premiere |
| Preproduction (done so far) | Scriptwriting & casting update + 2 stills + 2 clips |
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
├── images/             # WebP stills + MP4 placeholders
├── data/
│   ├── waitlist.csv    # Saved signups (not public)
│   └── .htaccess       # Blocks browser access to data/
└── README.md
```

---

## Hostinger setup

1. In Hostinger **hPanel → Files → File Manager**, open `public_html` (or your domain’s web root).
2. Upload **everything inside** this `nyaranyara` folder into that root  
   (so `index.html` and `waitlist.php` sit at the top level of the site).
3. Make sure PHP is enabled (default on Hostinger shared hosting).
4. Confirm the domain email **nyara@dgi-collektive.com** exists in Hostinger **Emails** so notifications can send.
5. Visit the site and submit a test waitlist entry.
6. Check:
   - Email inbox for `nyara@dgi-collektive.com`
   - File Manager → `data/waitlist.csv`

### Waitlist: how signups are received

When someone joins:

1. The form posts to **`waitlist.php`**
2. The contact is appended to **`data/waitlist.csv`**
3. An email notification is sent to **nyara@dgi-collektive.com**

`data/.htaccess` blocks public downloads of the CSV. Download it only through File Manager / FTP.

To change the notification address, edit the top of `waitlist.php`:

```php
$notifyEmail = 'nyara@dgi-collektive.com';
$fromEmail   = 'nyara@dgi-collektive.com';
```

If email fails but the CSV grows, Hostinger mail may need setup — the CSV is still the backup list.

Open `waitlist.csv` in Excel / Google Sheets to count or export contacts.

---

## How to update as the film progresses

Edit **`index.html`** (and replace files in **`images/`** when needed). Re-upload only the changed files.

### 1. On the slate (where we are)

Find the block with class `slate-path`.

- Move `class="is-now"` to the current step
- Change step labels (`Done` / `Now` / `Next` / `Ahead` / `Goal`)
- Update each `slate-note` line of copy
- Update the intro paragraph under **On the slate** when the stage changes

Example when finance is done and shoot begins:

- Script → `Done`
- Finance → `Done`
- Shoot → `Now` + `is-now`
- Premiere → still ahead

### 2. Preproduction / progress media

Section `#done`:

- Update the small title (`.done-label`) and paragraph (`.done-copy`)
- Replace stills: `images/scriptandcast1.webp`, `images/scriptandcast2.webp`
- Replace clips: `images/nyara-prepro-1.mp4`, `images/nyara-prepro-2.mp4`
- Keep using **WebP** for photos (smaller / faster). Convert before upload if you can.

When a new stage is complete, duplicate or rewrite this block rather than leaving old “done” copy that is no longer true.

### 3. Support numbers

In the Support section, edit `data-count` on each `.stat-value`:

```html
<p class="stat-value stat-value--waitlist" data-count="1240">0<span>+</span></p>
<p class="stat-value stat-value--social" data-count="8600">0<span>+</span></p>
<p class="stat-value stat-value--platforms" data-count="3">0</p>
```

- Waitlist count: use rows in `data/waitlist.csv` (minus the header), or your CRM total
- Social following: update from Instagram / TikTok / X totals
- Platforms: how many channels are active

The page animates from `0` to the `data-count` value on scroll — you only change the number.

### 4. Cast

When names / photos are public:

- Replace the `?` avatars with `<img>` tags
- Update `.cast-name` text
- Keep or drop the TBA card as needed

### 5. Storyline / hero

- Hero image: `images/nyara-hero.webp`
- Storyline banner: `images/nyara-storyline-wide.webp`
- Edit synopsis paragraphs inside `.story-copy`
- Update Country / Language / Setting / Release in `.story-meta`

### 6. Social links

Search `instagram.com`, `x.com`, `tiktok.com`, `facebook.com` in `index.html` and replace with the real profile URLs.

### 7. Footer

Email link and production credit live in the `<footer>`.

---

## Local preview

You can open `index.html` in a browser for layout, but **waitlist submit needs PHP** (Hostinger, or a local PHP server):

```bash
cd nyaranyara
php -S localhost:8080
```

Then open `http://localhost:8080`.

---

## Quick checklist before each update

- [ ] Slate step + `is-now` matches reality  
- [ ] Progress copy / media match the current stage  
- [ ] Support `data-count` numbers refreshed  
- [ ] New images are WebP and not huge  
- [ ] Test one waitlist signup after deploy  
- [ ] Confirm CSV and/or email received the test  
