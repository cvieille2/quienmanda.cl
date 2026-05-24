# Mensajes Operativos - T-039 Imagenes pendientes Piqueros
## Sesion: 2026-05-23

## Instruccion general
Hay una inconsistencia entre el proceso previo de asignacion de imagenes y la auditoria posterior. Primero se valida `featured_media` real; despues se corrige solo lo que siga pendiente.

---

### Tecnico WordPress / Automatizacion
Valida en WordPress el estado real de estas URLs: `/piqueros/piquero-peruano/` y `/piqueros/piquero-de-patas-coloradas/`. Si no tienen imagen destacada, asigna una adecuada y deja `featured_media`, `alt_text`, `caption` y verificacion listos. No modifiques `piquero-blanco` ni `piquero-cafe` salvo que encuentres una discrepancia.

### PM Aves
Recibe el estado final, confirma que no quedan URLs del cluster `Piqueros` sin imagen y cierra la tarea con evidencia de verificacion.

---

## Resultado esperado
- Inventario validado.
- Imagenes pendientes resueltas.
- Cierre documentado por URL y responsable.
