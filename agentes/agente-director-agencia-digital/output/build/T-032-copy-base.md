# Copy Base - T-032 Aves de Rapa Nui
## Sesion: 2026-05-21

## Decision de arquitectura
- `Rapa Nui` e `Isla de Pascua` van en la misma arquitectura de contenido.
- El hub principal usa `Rapa Nui` en el slug y mezcla ambos terminos en title/meta.
- Si luego hay volumen suficiente para una URL sinonima, se evalua como canonica o redireccion.

## URLs y copy base

| URL sugerida | Intencion | Title | Meta description | H1 | Intro base |
|---|---|---|---|---|---|
| `/rapa-nui/` | Guia general | Aves de Rapa Nui: guia completa de la avifauna de Isla de Pascua | Descubre las aves de Rapa Nui y de Isla de Pascua: especies marinas, introducidas, extintas y nombres rapanui explicados en una guia clara y util. | Aves de Rapa Nui | Rapa Nui concentra una avifauna muy particular por su aislamiento, su historia natural y su relacion con el Oceano Pacifico. En esta guia reunimos las especies mas representativas, los grupos de aves que todavia pueden observarse y el contexto que hace unica a la Isla de Pascua. |
| `/rapa-nui/aves-marinas/` | Aves marinas | Aves marinas de Rapa Nui: especies que se observan en la isla | Conoce las aves marinas de Rapa Nui, donde verlas y por que la isla es un punto clave para su descanso y nidificacion. | Aves marinas de Rapa Nui | Las aves marinas encuentran en Rapa Nui un lugar de descanso y nidificacion sin el ruido de grandes centros urbanos. Esta pagina ordena las especies mas relevantes, su comportamiento y los puntos donde suelen aparecer en la isla y sus motus. |
| `/rapa-nui/aves-introducidas/` | Aves introducidas | Aves introducidas en Rapa Nui: especies llegadas desde el continente | Revisa cuales son las aves introducidas en Rapa Nui, como llegaron a la isla y que impacto tienen hoy en la avifauna local. | Aves introducidas en Rapa Nui | No toda el ave que se ve en la isla es originaria del lugar. Esta pagina explica que especies fueron introducidas desde el continente, cuando llegaron y como conviven con la fauna de Rapa Nui en la actualidad. |
| `/rapa-nui/aves-extintas/` | Historia natural | Aves extintas de Rapa Nui: especies desaparecidas de la isla | Explora las aves extintas de Rapa Nui a partir de registros fosiles y evidencia historica, para entender como cambio la fauna de la isla. | Aves extintas de Rapa Nui | Antes de la llegada de especies introducidas y de los cambios ambientales historicos, Rapa Nui tuvo una fauna de aves distinta a la actual. Aqui reunimos lo que se sabe sobre las especies desaparecidas y su valor para entender el pasado de la isla. |
| `/rapa-nui/manutara/` | Leyenda y especie | Manutara: el ave sagrada de Rapa Nui y su significado | Conoce el Manutara, el ave sagrada de Rapa Nui, su lugar en la leyenda rapanui y la especie que muchos asocian con este nombre. | Manutara, el ave sagrada de Rapa Nui | El Manutara ocupa un lugar central en la memoria rapanui por su relacion con una de las leyendas mas conocidas de la isla. En esta pagina explicamos el simbolo, la posible especie asociada y por que sigue siendo una referencia cultural clave. |
| `/rapa-nui/nombres-de-aves-en-rapanui/` | Lengua y cultura | Nombres de aves en rapanui: guia de terminos y significados | Aprende los nombres de aves en rapanui mas citados en Rapa Nui, que significan y con que especies se relacionan. | Nombres de aves en rapanui | La lengua rapanui guarda nombres propios para distintas aves observadas en la isla y en su entorno marino. Esta guia resume los terminos mas utiles, su posible traduccion y el contexto cultural en que aparecen. |

## Version corta para el hub

### Title hub
Aves de Rapa Nui: guia completa de la avifauna de Isla de Pascua

### Meta hub
Descubre las aves de Rapa Nui y de Isla de Pascua en una guia ordenada por intencion: marinas, introducidas, extintas, Manutara y nombres rapanui.

### H1 hub
Aves de Rapa Nui

### Apertura hub
Rapa Nui es un territorio unico para observar y entender aves por su aislamiento, su historia natural y su peso cultural. Esta guia central agrupa las paginas clave del tema para que encuentres rapido la especie, la leyenda o el contexto que buscas, sin mezclar intenciones distintas en una sola pagina.

## Nota operativa
- Antes de publicar, validar si alguna de estas paginas merece fusionarse con el hub por bajo volumen.
- Si aparece un keyword fuerte para `Aves de Isla de Pascua`, usarlo como variante de title/meta dentro del mismo contenido o como canonica secundaria si el analisis lo justifica.
