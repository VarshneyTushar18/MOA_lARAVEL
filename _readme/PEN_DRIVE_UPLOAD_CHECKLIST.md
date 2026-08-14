# Pen Drive → Console upload checklist

## Local preview (already loaded)

Server: http://127.0.0.1:8001/

- Home: http://127.0.0.1:8001/
- About Us: http://127.0.0.1:8001/about
- Fact Sheet: http://127.0.0.1:8001/factsheet
- ACSM / IEC: http://127.0.0.1:8001/acsm_iec
- Best Practices: http://127.0.0.1:8001/best_practices
- Patient Corner: http://127.0.0.1:8001/patient_corner
- Contact: http://127.0.0.1:8001/contact
- Console login: http://127.0.0.1:8001/console/login  
  `admin@example.com` / `admin123`

Pen drive photos/PDFs/videos were copied into local `storage/app/public` and DB records were replaced. Re-run anytime with:

`php artisan moa:import-pendrive`

---

Open this file: `_readme/PEN_DRIVE_UPLOAD_CHECKLIST.md`

In Cursor: `Ctrl+P` → type `PEN_DRIVE_UPLOAD_CHECKLIST` → Enter.  
Then `Ctrl+Shift+V` for preview (checkboxes are easier there).

Source: `E:\MOA DATABASE\Pen Drive Data` vs `public/laravel_db_backup.sql`  
This is a checklist only. Nothing was implemented.

---

## Console pages (from DB dump)

| Page | ID | Open this |
|---|---|---|
| Home | 1 | `/console/pages/sections/1/list` |
| About Us | 2 | `/console/pages/sections/2/list` |
| Contact Us | 3 | `/console/pages/sections/3/list` |
| Face Sheet | 4 | `/console/pages/sections/4/list` |
| ACSM/IEC | 5 | `/console/pages/sections/5/list` |
| Best Practices | 6 | `/console/pages/sections/6/list` |
| Patient Corner | 7 | Patient Corner page → OPD Excel Upload |
| Performance Report | 8 | `/console/pages/sections/8/list` |

Note: Fact Sheet slug in DB is `facesheet`, public route is `/factsheet`.

---

## Do first (in this order)

1. About Us → `ntpc` gallery — replace 4 photos with all **21**
2. Fact Sheet → **W2** replace (25 photos) + **W4** replace (44 photos) + **W5-6** weblinks
3. ACSM → posters full set + 2025/2026 banners
4. About Us → scheme — upload Ayurswasthya PDF (Download Guidelines is empty)
5. Compress launch video before upload (~300 MB, limit ~200 MB)

PPTX files must be converted to **PDF** first.

---

## 1. About Us — ADD / REPLACE

Console: `/console/pages/sections/2/list`

### Text / PDF

- [ ] `project_detail` text ← `3. ABOUT US\1. Project Details\1. LTBI PROJECT DETAILS.docx`
- [ ] `scheme` + 3 subsections text ← `3. ABOUT US\2. Details of MOA Schemes\2.(a)_Details of MoA Scheme.docx`
- [ ] **ADD** scheme PDF ← `3. ABOUT US\2. Details of MOA Schemes\2.(b)_Ayurswasthya_Refrence Document.pdf`
- [ ] `ntpc` text ← `3. ABOUT US\3. OPD Details\3. About NTPC OPD DETAILS.docx`
- [ ] Convert then ADD PDF ← `3. ABOUT US\infrastructure ppt.pptx`

### `ntpc` gallery — REPLACE all 4 with these 21 photos

Folder: `3. ABOUT US\4. photographs\`

- [ ] `1.(a) _PI_1_F.jpg`
- [ ] `1.(b) _PI_2 (2).jpg`
- [ ] `1.(c)_PI_3.jpg`
- [ ] `2. _PI_2.jpg`
- [ ] `3._PI_3.jpeg`
- [ ] `4._PI_4.jpeg`
- [ ] `5._PI_5.jpeg`
- [ ] `6._C_1.jpg`
- [ ] `7._C_2.jpeg`
- [ ] `8._DEO.jpeg`
- [ ] `9.(a)_Lab Testing.jpg`
- [ ] `9.(b)_Lab Testing.jpeg`
- [ ] `10.(a)_Medicine_distribuation.jpg`
- [ ] `10.(b)_Medicine_distribuation.jpeg`
- [ ] `11.(a).jpg`
- [ ] `12.(a)_WhatsApp Image 2026-01-07 at 12.55.58.jpeg`
- [ ] `12.(b)_WhatsApp Image 2026-01-07 at 12.55.59.jpeg`
- [ ] `12.(c)_WhatsApp Image 2026-01-07 at 12.55.59.jpeg`
- [ ] `12.(d)_WhatsApp Image 2026-01-07 at 12.55.59.jpeg`
- [ ] `13. WhatsApp Image 2026-01-07 at 12.55.43.jpeg`
- [ ] `14. IMG_20230511_103745_544.jpg`

---

## 2. Fact Sheet — ADD / REPLACE

Console: `/console/pages/sections/4/list`

### W2 — REPLACE all (folder is `W-2_to be replaced`)

DB has 20. Pen drive has **25**. Clear old, upload all.

Folder: `4. FACT Sheet\1. Training and workshop Program\W-2_to be replaced\`

- [ ] `20230315_172830.jpg`
- [ ] `20230315_172841.jpg`
- [ ] `20230315_172842.jpg`
- [ ] `20230315_172845.jpg`
- [ ] `20230315_172846.jpg`
- [ ] `20230315_172855.jpg`
- [ ] `20230315_172856.jpg`
- [ ] `20230315_172857.jpg`
- [ ] `20230315_172859.jpg`
- [ ] `20230315_172938.jpg`
- [ ] `20230315_173001.jpg`
- [ ] `20230315_173002.jpg`
- [ ] `20230315_173006.jpg`
- [ ] `20230315_173008.jpg`
- [ ] `20230315_173009.jpg`
- [ ] `20230315_173036.jpg`
- [ ] `20230315_173037.jpg`
- [ ] `20230315_173039.jpg`
- [ ] `20230315_173040.jpg`
- [ ] `20230315_173109.jpg`
- [ ] `20230315_173121.jpg`
- [ ] `20230315_173136.jpg`
- [ ] `20230315_173137.jpg`
- [ ] `20230907_125240.jpg`
- [ ] `20230907_125303.jpg`

### W4 — REPLACE all

DB has 20. Pen drive has **44**. Clear old, upload all.

Folder: `4. FACT Sheet\1. Training and workshop Program\W-4\`

- [ ] `20230414_104118.jpg`
- [ ] `20230414_104124.jpg`
- [ ] `20230414_104126.jpg`
- [ ] `20230414_104127.jpg`
- [ ] `20230414_104132.jpg`
- [ ] `20230414_104135.jpg`
- [ ] `20230414_104153.jpg`
- [ ] `20230414_104409.jpg`
- [ ] `20230414_104412.jpg`
- [ ] `20230414_104459.jpg`
- [ ] `20230414_104501.jpg`
- [ ] `20230414_104503.jpg`
- [ ] `20230414_104629.jpg`
- [ ] `20230414_104632.jpg`
- [ ] `20230414_104751.jpg`
- [ ] `20230414_104755.jpg`
- [ ] `20230907_103927.jpg`
- [ ] `20230907_103932.jpg`
- [ ] `20230907_104918.jpg`
- [ ] `20230907_104925.jpg`
- [ ] `20230907_104940.jpg`
- [ ] `20230907_104949.jpg`
- [ ] `20230907_105002.jpg`
- [ ] `20230907_105010.jpg`
- [ ] `20230907_105016.jpg`
- [ ] `20230907_105025.jpg`
- [ ] `20230907_105033.jpg`
- [ ] `20230907_105039.jpg`
- [ ] `20230907_105041.jpg`
- [ ] `20230907_105047.jpg`
- [ ] `20230907_105054.jpg`
- [ ] `20230907_105055.jpg`
- [ ] `20230907_105058.jpg`
- [ ] `20230907_105101.jpg`
- [ ] `20230907_105106.jpg`
- [ ] `20230907_105108.jpg`
- [ ] `20230907_105114.jpg`
- [ ] `20230907_105115.jpg`
- [ ] `20230907_105122.jpg`
- [ ] `20230907_105123.jpg`
- [ ] `20230907_105132.jpg`
- [ ] `20230907_105133.jpg`
- [ ] `20230907_105142.jpg`
- [ ] `20230907_105149.jpg`
- [ ] **ADD** YouTube/web links from `WEBLINK.pdf` / `WEBLINK.docx` into W4 videos

### W5–6 — ADD links only (no photos)

- [ ] `4. FACT Sheet\1. Training and workshop Program\W-5-6\WEBLINK.pdf` → paste into W5 videos (create W6 if needed)

### Survey

- [ ] **ADD** PDF + update Lorem text ← `4. FACT Sheet\2. Survey Data\Screening-Performa-QR-Code-Google-Form-Link.pdf`

### Skip (folders empty)

- Diagnostic Facilities — empty
- MoU — empty

### Verify only (counts already match)

- W1 — 18 photos + YouTube link file
- W3 — 6 photos

---

## 3. ACSM / IEC — ADD / REPLACE

Console: `/console/pages/sections/5/list`

### Diet chart — REPLACE both PDFs (2026 Kadha recipe may be new)

- [ ] `5.  ACSM and IEC\2. Diet Chart\Diet Chart.pdf`
- [ ] `5.  ACSM and IEC\2. Diet Chart\Kadha Recipe_2026.pdf`

### Daily regimen — REPLACE all 4 (2026 set)

- [ ] `Booklet_2026.pdf`
- [ ] `English_(add Project name)_2026.pdf`
- [ ] `Hindi_(add Project name)_2026.pdf`
- [ ] `Regimen.pdf`

### Posters / banners / wall stickers

DB has **20 images**. Pen drive has **48 images + PDFs**.  
Clear old gallery, then upload.

Folder: `5.  ACSM and IEC\6. Poster Banners and Wall Stics\`

**Core posters (replace/include)**

- [ ] `8._P-1.jpg`
- [ ] `9._P-2.jpg`
- [ ] `10.(b)_P-3.jpg`
- [ ] `10.(c)_P-3.JPG`
- [ ] `11.(c)_P-4.JPG`
- [ ] `12.(c) P-5.JPG`
- [ ] `13.(b)_IMG_2769.JPG`
- [ ] `14.(a)_P-7.jpeg`
- [ ] SKIP `14.(b)_IMG_2769.JPG` (duplicate of 13.b)
- [ ] `15.(c)_IMG_2782.JPG`
- [ ] `16.(a)_IMG_2792.JPG`
- [ ] `16.(b)_Tb and Latent Tb.jpg`
- [ ] `17.(a)_Tb symptoms.jpg`
- [ ] `17.(b)_tb symptoms.JPG`
- [ ] `18. Road Signs.JPG`
- [ ] `21. First Workshop_ID CARD.jpg`
- [ ] `24. 3th Workshop Standee.jpg`
- [ ] `28.(a) Standee_2025.jpeg`
- [ ] `37. End LTB.jpg`
- [ ] SKIP tiny thumbnails `38. images.jpg` … `43. images.jpg`

**ADD PDFs**

- [ ] `11.(b)_P-4.pdf`
- [ ] `12.(b)_P-5.pdf`
- [ ] `13.(a)_P-6.pdf`
- [ ] `19. First Workshop_standee.pdf`
- [ ] `20. First Workshop_Banner.pdf`
- [ ] `Banner_2026.pdf`
- [ ] `Standee_2026.pdf`
- [ ] Convert then ADD `15.(a)_LTBI Project- flow chart-1.pptx`
- [ ] Convert then ADD `15.(b)_LTBI Project- flow chart-2.pptx`

**ADD 2025 series (not in old 20)**

- [ ] `44. 16 April 2025.jpeg`
- [ ] `45. 17 April 2025.jpeg`
- [ ] `46. 18, 19, 20 April 2025.jpeg`
- [ ] `47. 21 April 2025.jpeg`
- [ ] `48. 22 April 2025.jpeg`
- [ ] `49. 23 April 2025.jpeg`
- [ ] `50. 24 April 2025.jpeg`
- [ ] `51. 25 April 2025.jpeg`
- [ ] `52. 26 April 2025.jpeg`
- [ ] `53. 28 April 2025.jpeg`
- [ ] `54. 29 April 2025.jpeg`
- [ ] `55. 30 April 2025.jpg`
- [ ] `56. 1st May to 12th May 2025.jpg`
- [ ] `57. 13th May to 18th May 2025.jpg`
- [ ] `58. 19th May to 25th May 2025 (AIIA).jpeg`
- [ ] `59.1 19th May to 25th May 2025.jpeg`
- [ ] `60. 26th May to 1st June 2025 (AIIA).jpeg`
- [ ] `61.1 26th May to 1st June 2025.jpeg`
- [ ] `62. 2nd June to 8th June 2025.jpg`
- [ ] `63. 9th June to 15th June 2025.jpg`
- [ ] `64. 16th June to 22nd June 2025.jpg`
- [ ] `65. 23rd June to 29th June 2025.jpg`
- [ ] `66. Top Three Institute.jpeg`

### Launch video — REPLACE (compress first)

- [ ] `5.  ACSM and IEC\8. Lanch Videos\1.Infrastructure_Lanuch_ video.mp4` (~300 MB)

### Verify only (counts already match)

- Pledge — 3 images + 2 PDFs
- Logo — `LTBI Logo.jpg` + `ID Card.png`
- Pamphlets — 6 PDFs
- Promotional — 1 image + 3 PDFs
- TB Pledge / Awareness — 7 GPS photos (folder name says videos, but they are photos)

### Skip (empty on pen drive)

- Documentary and audio Video Clip
- Journal
- How to Enroll yourself

---

## 4. Home — TEXT only (no photos on pen drive)

Console: `/console/pages/sections/1/list`

- [ ] Update copy from `2. HOME\1. INTRODUCATION\1. INTRODUCATION.docx`
- [ ] Optional PDF on `pm_yojna` ← `1. Front Page Content\Website LTBI.pdf` (same PDF is also under PM/MoA/RNTCP — use once)
- [ ] Update staff titles from `2. HOME\5. Personals Rols and Responsibilty\2. PERSNOALS ROLE AND RESPONSIBILTY.docx`  
  Keep existing staff photos (pen drive has none)

---

## 5. Best Practices

Console: `/console/pages/sections/6/list`

- [ ] Convert then REPLACE PDF ← `7. Best Practises\1. Success Stories\success story ppt.pptx` (~76 MB)
- [ ] VERIFY video ← `7. Best Practises\2. Patient Appraisal Videos\2. Minstry of  Ayush_Doorderson_6.mp4`
- [ ] VERIFY photo ← `7. Best Practises\3. Photos\20240109_115441.jpg`

---

## 6. Patient Corner / Contact / Performance

- [ ] **IMPORT only if Excel is newer** ← `8. Patient Corner\1. OPD PATIENT DATA\NTPC OPD Data.xlsx`  
  Use Patient Corner → OPD Excel Upload. DB already has many rows — do not duplicate blindly.
- [ ] SKIP Patient Corner folders 2–6 (empty)
- [ ] SKIP Performance Report folder (empty)
- [ ] Contact text check ← `Contact us\35. Email-ID-Contact-No..pdf`
- [ ] Contact text check ← `Contact us\Compleat address.docx`  
  Current DB: NTPC Campus D-010 AIIA, `philtbi533@gmail.com`, `9311391885`

---

## Action meanings

| Action | Meaning |
|---|---|
| ADD | Not in DB. Upload as new. |
| REPLACE | Section exists but set/count is wrong. Clear old files, upload this set. |
| VERIFY | Counts already match. Re-upload only if storage files are missing. |
| TEXT | Copy from DOCX/PDF into title/description. |
| IMPORT | Excel into patients, not a page section. |
| SKIP | Duplicate, tiny thumbnail, empty folder, or already covered. |
