from pathlib import Path

# court-manager
p = Path(r"c:\Users\gtejada\Documents\canchas_deportivas\resources\views\livewire\admin\court-manager.blade.php")
t = p.read_text(encoding="utf-8")
reps = [
    ("$court->location->name", "$court->sede->nombre"),
    ("$court->location->address", "$court->sede->direccion"),
    ("$court->location->nombre", "$court->sede->nombre"),
    ("$court->location->direccion", "$court->sede->direccion"),
    ("$court->location", "$court->sede"),
]
for a, b in reps:
    t = t.replace(a, b)
p.write_text(t, encoding="utf-8")
print("court:", "$court->sede" in t, "$court->location" in t)

# user-management
p = Path(r"c:\Users\gtejada\Documents\canchas_deportivas\resources\views\livewire\user-management.blade.php")
t = p.read_text(encoding="utf-8")
reps = [
    ("$user->profile->document_number", "$user->perfil->numero_documento"),
    ("$user->profile->document_type", "$user->perfil->tipo_documento"),
    ("$user->profile->names", "$user->perfil->nombres"),
    ("$user->profile->last_name_paternal", "$user->perfil->apellido_paterno"),
    ("$user->profile->last_name_maternal", "$user->perfil->apellido_materno"),
    ("$user->profile->address", "$user->perfil->direccion"),
    ("$user->profile", "$user->perfil"),
]
for a, b in reps:
    t = t.replace(a, b)
p.write_text(t, encoding="utf-8")
print("user:", "$user->perfil" in t, "$user->profile" in t)
