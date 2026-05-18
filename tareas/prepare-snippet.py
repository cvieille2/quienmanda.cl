import json
import base64

# Read the template file
with open('tareas/single-ficha_motel-template.php', 'r', encoding='utf-8') as f:
    template_code = f.read()

# Create the snippet
snippet = {
    "name": "CREAR TEMPLATE single-ficha_motel.php - temporal",
    "desc": "Escribe el archivo single-ficha_motel.php en el tema activo. Desactivar despues de ejecutar.",
    "code": "<?php\n/**\n * Write template file to theme directory\n */\n$template_code = " + json.dumps(template_code) + ";\n$theme_dir = get_stylesheet_directory();\n$file_path = $theme_dir . '/single-ficha_motel.php';\n$result = file_put_contents($file_path, $template_code);\nif ($result !== false) {\n    update_option('ficha_motel_template_created', 'yes', false);\n}",
    "scope": "global",
    "active": True
}

# Write the JSON
with open('tareas/snippet-write-template.json', 'w', encoding='utf-8') as f:
    json.dump(snippet, f, ensure_ascii=False, indent=2)

print("JSON file created")
print(f"Template code length: {len(template_code)} chars")
