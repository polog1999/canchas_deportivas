from pathlib import Path

needles = [
    "is_active",
    "getRoleNames",
    "tusne_group",
    "location_id",
    "document_number",
    'wire:model="name"',
    "local_description",
    "link_maps",
    "UserRole::cases",
]

root = Path(r"c:\Users\gtejada\Documents\canchas_deportivas\resources\views\livewire")
for f in root.rglob("*.blade.php"):
    t = f.read_text(encoding="utf-8")
    bad = [x for x in needles if x in t]
    print(f"{f.name}: {'OK' if not bad else bad}")
