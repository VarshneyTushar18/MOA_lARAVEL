"""Build MD5 manifest of TB pledge images from local folders."""
import hashlib
import json
import os
import re

ROOT = r"F:\1. LTBI WEBSITE FOLDERS\5. ACSM and IEC\8. Tb Pledge and Awareness Videos"
EXTS = (".jpg", ".jpeg", ".png", ".webp")
OUT = os.path.join(os.path.dirname(__file__), "_tb_pledge_image_manifest.json")


def year_from_path(path: str) -> int | None:
    normalized = path.replace("\\", "/").lower()
    for year in (2023, 2024, 2025, 2026):
        markers = (
            f"_{year}",
            f" {year}",
            f"/{year}",
            f"videos_{year}",
            f"videos _ {year}",
            f"all videos_{year}",
            f"all videos _ {year}",
        )
        if any(m in normalized for m in markers):
            return year

    base = os.path.basename(path)
    for pattern in (
        r"\b(202[3-6])\b",
        r"^(202[3-6])\d{4,}",
        r"IMG[_-](202[3-6])",
        r"(?:WhatsApp\s+(?:Image|Video)\s+)(202[3-6])-",
    ):
        m = re.search(pattern, base, re.I)
        if m:
            return int(m.group(1))

    m = re.search(r"IMG[_-](\d{4})\d{4}", base, re.I)
    if m:
        y = int(m.group(1))
        if 2023 <= y <= 2026:
            return y

    return None


def md5_file(path: str) -> str | None:
    try:
        h = hashlib.md5()
        with open(path, "rb") as f:
            for chunk in iter(lambda: f.read(1024 * 1024), b""):
                h.update(chunk)
        return h.hexdigest()
    except OSError as exc:
        print("SKIP", path, exc)
        return None


def main() -> None:
    entries = []
    for dp, _, fn in os.walk(ROOT):
        for name in fn:
            if not name.lower().endswith(EXTS):
                continue
            full = os.path.join(dp, name)
            year = year_from_path(full)
            if year is None:
                print("WARN no year:", full)
                continue
            digest = md5_file(full)
            if digest is None:
                continue
            entries.append(
                {
                    "md5": digest,
                    "size": os.path.getsize(full),
                    "year": year,
                    "name": name,
                }
            )

    with open(OUT, "w", encoding="utf-8") as f:
        json.dump(entries, f, indent=2)

    by_year = {}
    for e in entries:
        by_year[e["year"]] = by_year.get(e["year"], 0) + 1
    print(f"wrote {len(entries)} entries to {OUT}")
    print("by_year:", by_year)


if __name__ == "__main__":
    main()
