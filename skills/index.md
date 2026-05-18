# Catalogo de Skills Compartidos

Este directorio centraliza las definiciones de todas las habilidades del equipo. Cada archivo `.md` describe un skill con:
- Descripcion y alcance
- Entregables tipicos
- Dependencias con otros skills

Los agentes individuales referencian estos archivos desde sus definiciones en `agentes/<agente>/<agente>.md`.

## Skills disponibles

| Skill | Archivo | Agentes que lo usan |
|---|---|---|
| Direccion de Agencia Digital | `direccion-agencia-digital.md` | Director |
| Gestion de Proyectos Web | `gestion-proyectos-web.md` | Todos los PMs |
| Consultoria SEO y Monetizacion | `consultoria-seo-monetizacion.md` | Consultor SEO |
| Estrategia de Negocio Digital | `estrategia-negocio-digital.md` | Estratega |
| Asesoria Financiera Estrategica | `asesoria-financiera-estrategica.md` | Asesor Financiero |
| Ventas B2B | `ventas-b2b.md` | Mentor Ventas |
| Copywriting de Conversion | `copywriting-conversion.md` | Copywriter |
| Analisis Web CRO | `analisis-web-cro.md` | Analista CRO |
| Diseno UI Web | `diseno-ui-web.md` | Disenador UI |
| WordPress y Automatizacion | `wordpress-automatizacion.md` | Tecnico WordPress |
| Pauta Digital | `pauta-digital.md` | Pauta Digital |
| Lanzamientos Digitales | `lanzamientos-digitales.md` | Lanzamientos |
| Referencia Vilma Nunez | `referencia-vilma-nunez.md` | Referente Vilma Nunez |
| Enlazado Interno SEO | `enlazado-interno.md` | Especialista en Enlazado Interno |
| SEO Estrategico | `seo-estrategico.md` | Consultor SEO, Especialista en Enlazado Interno |

## Modelo compartido: Ficha de Motel
El archivo `input/como-debe-ser-ficha-motel.txt` define el modelo unico para crear fichas de motel en infomoteles.cl. Todos los skills involucrados (Copywriting, Diseno UI, CRO, WordPress, SEO) deben seguirlo al crear o revisar una ficha.

## Como agregar un nuevo skill
1. Crear archivo `skills/<nombre>.md` siguiendo el formato: Descripcion, Entregables tipicos, Dependencias
2. Agregar entrada en `skills/index.md`
3. Referenciar el skill desde el/los agentes que lo usan
