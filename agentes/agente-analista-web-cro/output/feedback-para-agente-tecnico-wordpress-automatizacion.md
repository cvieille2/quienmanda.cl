# Feedback: CRO → Técnico
## Fecha: 2026-05-16 | Tarea revisada: T-007

### 1. Plan de implementación
Buen plan. Priorización correcta (backup → página → CTA → tracking).

### 2. Prioridades CRO que debes implementar primero
1. CTA flotante "Dueño de motel" → crítica, la necesitamos ya
2. Página /para-moteles/ → crítica, sin esto no hay conversión
3. Meta description nueva → rápida, hazla tú mismo
4. GA4 → necesitamos datos desde el día 1

### 3. Sugerencias técnicas
El código del CTA flotante puede ir en el footer.php del tema hijo.
Si no hay tema hijo, créalo antes de tocar el footer.

Recomiendo instalar estos plugins:
- Insert Headers and Footers (para códigos de tracking)
- WP Rocket o Flying Pages (caché, si la velocidad es > 3s)
- Site Kit by Google (GA4 + Search Console)

### 4. Eventos GA4 que necesito
Implementa estos eventos personalizados en GA4:
- `click_cta_motelero` → cuando hacen clic en el botón flotante
- `view_plan_page` → cuando ven /para-moteles/
- `submit_motelero_form` → cuando envían el formulario

### 5. Verificación
Después de implementar, pásame el link de la página y lo reviso.
