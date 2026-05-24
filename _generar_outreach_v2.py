import csv
import os

with open('entregables/todas-las-fichas.csv', encoding='utf-8-sig') as f:
    fichas = list(csv.DictReader(f))

# Email lookup by keyword in slug (more flexible)
email_rules = [
    ('serrano-antofagasta', 'contacto@hotelserrano.cl'),
    ('diamante-curico', 'cabanasdiamante@gmail.com'),
    ('arenal-valdivia', 'contacto@motelarenal.cl'),
    ('kalu-calama', 'info@kilantur.cl'),
    ('7-lunas', 'administracion@7lunas.cl'),
    ('azzo', 'administracion@motelazzo.cl'),
    ('diplomate', 'MotelDiplomate@Hispavista.com'),
    ('via-veneto', 'viaveneto1797@gmail.com'),
    ('las-burgas', 'lasburgas@terra.cl'),
    ('cabanas-paraiso-arica', 'contacto@motelparaiso.cl'),
    ('lavien-rose', 'reservas@lavienrose.cl'),
    ('euro-calama', 'contacto@motel-euro.cl'),
    ('cabanas-del-rio-arica', 'cabanasdelrioarica@gmail.com'),
]

# Phone lookup by keyword
phone_rules = [
    ('los-sauces', '+56522364503'),
    ('serrano-antofagasta', '+56552787714'),
    ('diamante-curico', '+56752590095'),
    ('caudal-talcahuano', '+56413166255'),
    ('euro-calama', '+56552310515'),
    ('oasis-de-los-niches', '+56997952502'),
    ('melosas', '+56752441109'),
    ('liguria', '+56412933553'),
    ('deja-vu', '+56522226409'),
    ('las-cavernas-calama', '+56552540477'),
    ('arenal-valdivia', '+56632236060'),
    ('la-cascada', '+56412462087'),
    ('la-montana-lampa', '+56984422352'),
    ('san-fernando-copiapo', '+56522221607'),
    ('santo-pecado', '+56977601653'),
    ('7-lunas', '+56944329557'),
    ('los-olivos-arica', '+56998378092'),
    ('azzo', '+56228393350'),
    ('el-amor-en-lila', '+56225597060'),
    ('gala-providencia', '+56222225508'),
    ('jardin-de-eros', '+56936830179'),
    ('la-favorita-arica', '+56582222866'),
    ('paso-nevado-maipu', '+56941033186'),
    ('el-conquistador', '+56412382806'),
    ('diplomate', '+56322661647'),
    ('camy-temuco', '+56997448760'),
    ('venezia-rodriguez', '+56412951980'),
    ('kalu-calama', '+56552342902'),
    ('via-veneto', '+56722717633'),
    ('la-fuente-arica', '+56998461499'),
    ('cupido-coquimbo', '+5697545362'),
    ('cisne-concepcion', '+56412199256'),
    ('vespucio', '+56225580596'),
    ('las-aranas', '+56722712012'),
    ('huelen', '+56222352409'),
    ('caribe-talca', '+56995145986'),
    ('las-burgas', '+56228111463'),
    ('cabanas-paraiso-arica', '+56582226586'),
    ('lavien-rose', '+56225581033'),
    ('nevados-de-chillan', '+56422261000'),
    ('palmera-san-miguel', '+56225556534'),
    ('nevada-sur', '+56412276966'),
    ('colcas-arica', '+56987075195'),
    ('las-minas-punta-arenas', '+56612218553'),
    ('romance-antofagasta', '+56553205093'),
    ('cruz-656', '+56412934723'),
    ('bahia-vina', '+56322871863'),
    ('new-tekena', '+56941692760'),
    ('iguazu-coronel', '+56412715301'),
    ('geminis-temuco', '+56452273513'),
    ('galaxia-santiago', '+56226995812'),
    ('platinum-punta-arenas', '+56612244078'),
    ('luna-y-mar', '+56225283216'),
    ('mediterraneo-antofagasta', '+56552249082'),
    ('villa-alpina', '+56572768012'),
    ('los-leones-valdivia', '+56632217998'),
    ('corazon-coquimbo', '+56512752631'),
    ('puerto-principe', '+56976154185'),
    ('el-trauco-puente-alto', '+56264655118'),
    ('ricardo-cumming', '+56226711226'),
    ('malibu-vina', '+56322687724'),
    ('arbolito-valparaiso', '+56322291088'),
    ('holley-providencia', '+56934860081'),
    ('el-parque-concepcion', '+56412948943'),
    ('los-naranjos', '+56223139585'),
    ('pasion-valparaiso', '+56224571817'),
    ('kalipso-santiago', '+56232912292'),
    ('veracruz', '+56229986797'),
    ('sol-y-luna', '+56987965336'),
    ('babilonia', '+56582329002'),
    ('maromas', '+56224878144'),
    ('eclipse-iquique', '+5657222066'),
    ('cantarrero', '+56997938459'),
    ('fish-concepcion', '+56412082058'),
    ('kiss-vina', '+56322972428'),
    ('los-troncos', '+56224162650'),
    ('md-vina', '+56322876066'),
    ('primavera-santiago', '+56226993586'),
    ('aerotel-travel', '+56226018492'),
    ('jardin-secreto', '+56981492489'),
    ('eros-punta-arenas', '+56612227863'),
    ('eclipse-de-luna', '+56991395868'),
    ('internacional-la-reina', '+56977736671'),
    ('el-trauco-la-granja', '+56930828541'),
    ('tantra-villa-alemana', '+56412382531'),
    ('no-se-coquimbo', '+56512323955'),
    ('nahuen-angol', '+56992191124'),
    ('caudal-concepcion', '+56413166255'),
]

def match_slug(slug, rules):
    for keyword, value in rules:
        if keyword in slug:
            return value
    return ''

def get_name(slug):
    parts = slug.strip('/').split('/')
    if len(parts) >= 2:
        name_part = parts[-1].replace('-', ' ').title()
        city = parts[0].replace('-', ' ').title()
        return name_part, city
    return parts[0].replace('-', ' ').title(), ''

def body_email(name, city, clics, imp, ctr, pos, email):
    return f"""De: Christian Vieille <cvieille@infomoteles.cl>
Para: {email}
Asunto: Sugerencia para mejorar tu ficha en infomoteles.cl

Hola {name},

Soy Christian, de infomoteles.cl. Paso directo al grano:

Tu ficha en infomoteles.cl ({city}) está recibiendo {imp} impresiones en Google, pero solo {clics} clics. Eso significa que tu página se ve pero no convence.

Tu CTR actual es {ctr}% y tu posición promedio es {pos}.

Podemos mejorar eso sin que muevas un dedo. Tengo un servicio de optimización de fichas que incluye:
• Títulos y descripciones meta optimizados para mejorar tu posición
• Fotos profesionales sin cargo
• Textos pensados para convertir visitas en reservas
• Integración de WhatsApp en la ficha

¿Te interesa que te muestre un par de ejemplos de cómo se vería tu ficha optimizada?

Saludos,
Christian Vieille
infomoteles.cl"""

def body_wa(name, city, imp):
    return f"Hola {name} ({city}), soy Christian de infomoteles.cl. Tu ficha está teniendo {imp} impresiones en Google — podemos ayudarte a convertir eso en más clientes. ¿Tienes 5 min?"

fichas.sort(key=lambda r: int(r.get('impresiones',0) or 0), reverse=True)

lines = []
lines.append("# Outreach Personalizado para Moteles en infomoteles.cl\n\n")
lines.append(f"**Total fichas:** {len(fichas)}\n\n")
lines.append("---\n\n")

email_count = 0
wa_count = 0
no_count = 0

email_batch = []
wa_batch = []

for f in fichas:
    slug = f.get('slug','').strip('/')
    clics = f.get('clics','0')
    imp = f.get('impresiones','0')
    ctr = f.get('ctr','0')
    pos = f.get('posicion','0')
    name, city = get_name(slug)
    email = match_slug(slug, email_rules)
    phone = match_slug(slug, phone_rules)

    if email:
        lines.append(f"## {name} ({city})\n")
        lines.append(f"- URL: https://infomoteles.cl/{slug}/\n")
        lines.append(f"- Email: {email}\n")
        lines.append(f"- Rendimiento SC: {imp} impresiones, {clics} clics, {ctr}% CTR, posición {pos}\n\n")
        lines.append("```\n" + body_email(name, city, clics, imp, ctr, pos, email) + "\n```\n\n---\n")
        email_batch.append((slug, email, name, city, clics, imp, ctr, pos))
        email_count += 1
    elif phone:
        lines.append(f"## {name} ({city})\n")
        lines.append(f"- URL: https://infomoteles.cl/{slug}/\n")
        lines.append(f"- WhatsApp/Tel: {phone}\n")
        lines.append(f"- Rendimiento SC: {imp} impresiones, {clics} clics, {ctr}% CTR, posición {pos}\n\n")
        lines.append("WhatsApp:\n```\n" + body_wa(name, city, imp) + "\n```\n\n---\n")
        wa_batch.append((slug, phone, name, city, imp))
        wa_count += 1
    else:
        no_count += 1

lines.append(f"\n# Sin Contacto ({no_count})\n\n")
for f in fichas:
    slug = f.get('slug','').strip('/')
    if not match_slug(slug, email_rules) and not match_slug(slug, phone_rules):
        name, city = get_name(slug)
        imp = f.get('impresiones','0')
        lines.append(f"- [{name} ({city})](https://infomoteles.cl/{slug}/) - {imp} impresiones\n")

lines.append(f"\n---\n## Resumen\n\n")
lines.append(f"- **Con email:** {email_count}\n")
lines.append(f"- **WhatsApp listo:** {wa_count}\n")
lines.append(f"- **Sin contacto:** {no_count}\n")
lines.append(f"- **Total:** {len(fichas)}\n")

with open('entregables/outreach_completo.md', 'w', encoding='utf-8') as f:
    f.writelines(lines)

os.makedirs('entregables/emails_listos', exist_ok=True)
for slug, email, name, city, clics, imp, ctr, pos in email_batch:
    safe = slug.replace('/','-')
    with open(f'entregables/emails_listos/{safe}.eml', 'w', encoding='utf-8') as f:
        f.write(body_email(name, city, clics, imp, ctr, pos, email))

os.makedirs('entregables/whatsapp_listos', exist_ok=True)
# Group WhatsApp by city for efficient sending
wa_by_city = {}
for slug, phone, name, city, imp in wa_batch:
    wa_by_city.setdefault(city, []).append((phone, name, imp))

with open('entregables/whatsapp_listos/README.md', 'w', encoding='utf-8') as f:
    f.write("# Mensajes WhatsApp para Moteles\n\n")
    f.write("Enviar desde: +56 9 XXXX XXXX\n\n")
    for city, moteles in sorted(wa_by_city.items()):
        f.write(f"## {city}\n\n")
        for phone, name, imp in moteles:
            f.write(f"### {name} - {phone}\n\n")
            f.write(f"```\n{body_wa(name, city, imp)}\n```\n\n")

print(f"OUTREACH COMPLETO GENERADO\n")
print(f"  Emails personalizados: {email_count}")
print(f"  WhatsApp preparados: {wa_count}")
print(f"  Sin contacto: {no_count}")
print(f"\nArchivos generados:")
print(f"  entregables/outreach_completo.md")
print(f"  entregables/emails_listos/ ({email_count} archivos)")
print(f"  entregables/whatsapp_listos/ ({wa_count} mensajes)")
