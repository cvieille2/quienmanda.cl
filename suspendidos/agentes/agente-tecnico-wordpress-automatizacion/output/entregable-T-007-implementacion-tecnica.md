# Entregable T-007: Implementación Técnica — infomoteles.cl
## Agente: Técnico WordPress / Automatización
## Fecha: 2026-05-16
## Proyecto: infomoteles.cl

### 1. SITUACIÓN ACTUAL (DIAGNÓSTICO RÁPIDO)

- WordPress (tema visible: aparentemente un tema estándar)
- Plugins no identificados
- Sin página de aterrizaje comercial (/para-moteles/)
- Bloque de planes: no existe
- Tracking: no se detectó GA4 ni Meta Pixel en el HTML
- Cache: no se detectó plugin de caché evidente
- Velocidad: imágenes con lazy loading placeholder base64

### 2. PLAN DE IMPLEMENTACIÓN

#### Paso 1: Backup (antes de todo) — 15 min
- [ ] Backup completo (archivos + BD) vía cPanel o plugin UpdraftPlus
- [ ] Verificar que el backup se pueda descargar

#### Paso 2: Crear página /para-moteles/ — 1 hora
- [ ] Nueva página en WordPress: "Para Moteles"
- [ ] Insertar HTML del bloque de planes (entregable T-006)
- [ ] Aplicar el copy de T-004
- [ ] Asignar plantilla "Página completa" (sin sidebar)
- [ ] Publicar como borrador primero → revisar → publicar

#### Paso 3: Agregar CTA flotante "¿Dueño de motel?" — 30 min
- [ ] Insertar código en footer.php o mediante plugin de código (Insert Headers and Footers)
- [ ] Posición: fixed, esquina inferior derecha
- [ ] Texto: "¿Dueño de motel? Aparece aquí →"
- [ ] Link: /para-moteles/
- [ ] Diseño: botón rojo (#e74c3c), texto blanco, border-radius

Código sugerido:
```html
<a href="/para-moteles/"
   style="position:fixed; bottom:20px; right:20px; background:#e74c3c; color:#fff;
          padding:12px 20px; border-radius:50px; font-weight:700; font-size:15px;
          text-decoration:none; z-index:9999; box-shadow:0 4px 12px rgba(0,0,0,0.2);">
  🏨 ¿Dueño de motel? Aparece aquí →
</a>
```

#### Paso 4: Verificar/formulario de contacto — 20 min
- [ ] Revisar /contacto/ — ¿el formulario envía correctamente?
- [ ] Si no: instalar/activar Contact Form 7 o WPForms Lite
- [ ] Agregar campo "Soy dueño de motel" en el formulario actual

#### Paso 5: Google Analytics 4 — 30 min
- [ ] Verificar si ya existe código GA en el sitio
- [ ] Si no: crear propiedad GA4 en analytics.google.com
- [ ] Insertar código vía plugin (Site Kit by Google o manual en header.php)
- [ ] Verificar con GA Debugger o Tag Assistant

#### Paso 6: Search Console — 15 min
- [ ] Verificar si el sitio ya está en Search Console
- [ ] Si no: agregar propiedad, verificar con mismo método que GA (Site Kit)
- [ ] Revisar datos actuales (posiciones, clics, queries)

#### Paso 7: Meta Pixel — 30 min
- [ ] Crear pixel en Meta Business Suite
- [ ] Insertar código base en <head>
- [ ] Agregar eventos: PageView, ViewContent (en /para-moteles/)
- [ ] Verificar con Meta Pixel Helper

#### Paso 8: Pruebas de velocidad — 15 min
- [ ] Test en PageSpeed Insights (mobile + desktop)
- [ ] Si > 4s: activar plugin de caché (WP Rocket o W3 Total Cache)
- [ ] Si hay imágenes pesadas: instalar ShortPixel o Smush

#### Paso 9: Verificación responsive — 20 min
- [ ] Probar /para-moteles/ en Chrome DevTools (320px, 768px, 1024px)
- [ ] Verificar CTA flotante en mobile (que no tape contenido)
- [ ] Verificar envío de formulario desde mobile

### 3. CHECKLIST DE VERIFICACIÓN

- [ ] Backup realizado y descargable
- [ ] Página /para-moteles/ pública y visible
- [ ] Bloque de planes se ve correcto en desktop
- [ ] Bloque de planes se ve correcto en mobile
- [ ] CTA flotante visible y funcional
- [ ] Formulario de contacto envía correctamente
- [ ] GA4 instalado y recibiendo eventos
- [ ] Search Console verificada con datos
- [ ] Meta Pixel instalado y disparando
- [ ] Velocidad < 3s en PageSpeed Insights
- [ ] Todos los enlaces funcionan (sin 404)

### 4. CRONOGRAMA

| Tarea | Tiempo | Quién |
|-------|--------|-------|
| Backup | 15 min | Técnico |
| Crear /para-moteles/ | 1 hr | Técnico + Copy de T-004 |
| CTA flotante | 30 min | Técnico |
| Formulario | 20 min | Técnico |
| GA4 + Search Console | 45 min | Técnico |
| Meta Pixel | 30 min | Técnico |
| Velocidad | 15 min | Técnico |
| Pruebas responsive | 20 min | Técnico |
| **Total** | **~4 horas** | |

### 5. CREDENCIALES Y ACCESOS

Para implementar necesito acceder a:
- WordPress admin de infomoteles.cl
- Google Analytics (GA4)
- Google Search Console
- Meta Business Suite

La App Password guardada en credenciales/ permite acceso a la API REST
de WordPress para automatizar la creación de contenido y posts.
