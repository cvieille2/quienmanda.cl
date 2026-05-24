import csv
import json
import os

with open('entregables/todas-las-fichas.csv', encoding='utf-8-sig') as f:
    fichas = list(csv.DictReader(f))

known_emails = {
    'antofagasta/motel-serrano-antofagasta': 'contacto@hotelserrano.cl',
    'motel-diamante-curico': 'cabanasdiamante@gmail.com',
    'valdivia/motel-arenal-valdivia': 'contacto@motelarenal.cl',
    'calama/motel-kalu-calama': 'info@kilantur.cl',
    'san-vicente/motel-7-lunas-san-vicente-de-tagu': 'administracion@7lunas.cl',
    'recoleta/motel-azzo-recoleta': 'administracion@motelazzo.cl',
    'vina-del-mar/motel-diplomate-vina-del-mar': 'MotelDiplomate@Hispavista.com',
    'san-fernando/motel-via-veneto-san-fernando': 'viaveneto1797@gmail.com',
    'padre-hurtado/motel-las-burgas-padre-hurtado': 'lasburgas@terra.cl',
    'arica/motel-paraiso-arica': 'contacto@motelparaiso.cl',
    'la-cisterna/motel-lavien-rose-la-cisterna': 'reservas@lavienrose.cl',
    'calama/motel-euro-calama': 'contacto@motel-euro.cl',
    'arica/cabanas-del-rio-arica': 'cabanasdelrioarica@gmail.com',
}

known_phones = {
    'copiapo/motel-los-sauces': '+56522364503',
    'antofagasta/motel-serrano-antofagasta': '+56552787714',
    'motel-diamante-curico': '+56752590095',
    'talcahuano/motel-caudal-talcahuano': '+56413166255',
    'calama/motel-euro-calama': '+56552310515',
    'curico/motel-el-oasis-de-los-niches': '+56997952502',
    'curico/motel-y-cabanas-melosas-rauco': '+56752441109',
    'concepcion/motel-liguria': '+56412933553',
    'copiapo/motel-deja-vu-copiapo': '+56522226409',
    'calama/motel-las-cavernas-calama': '+56552540477',
    'valdivia/motel-arenal-valdivia': '+56632236060',
    'concepcion/motel-la-cascada-concepcion': '+56412462087',
    'lampa/motel-la-montana-lampa': '+56984422352',
    'copiapo/motel-san-fernando-copiapo': '+56522221607',
    'tome/motel-santo-pecado-tome': '+56977601653',
    'san-vicente/motel-7-lunas-san-vicente-de-tagu': '+56944329557',
    'arica/motel-los-olivos-arica': '+56998378092',
    'recoleta/motel-azzo-recoleta': '+56228393350',
    'la-cisterna/motel-el-amor-en-lila-la-cisterna': '+56225597060',
    'providencia/motel-gala-providencia': '+56222225508',
    'quilicura/motel-jardin-de-eros-quilicura': '+56936830179',
    'arica/motel-la-favorita-arica': '+56582222866',
    'maipu/motel-paso-nevado-maipu': '+56941033186',
    'concepcion/motel-el-conquistador-la-escapada-ro': '+56412382806',
    'vina-del-mar/motel-diplomate-vina-del-mar': '+56322661647',
    'temuco/motel-camy-temuco': '+56997448760',
    'concepcion/motel-venezia-rodriguez-concepcion': '+56412951980',
    'calama/motel-kalu-calama': '+56552342902',
    'san-fernando/motel-via-veneto-san-fernando': '+56722717633',
    'arica/motel-la-fuente-arica': '+56998461499',
    'coquimbo/motel-cupido-coquimbo': '+5697545362',
    'concepcion/motel-cisne-concepcion': '+56412199256',
    'la-cisterna/motel-vespucio-la-cisterna': '+56225580596',
    'san-fernando/motel-las-aranas-san-fernando': '+56722712012',
    'providencia/motel-huelen-providencia': '+56222352409',
    'talca/motel-caribe-talca': '+56995145986',
    'padre-hurtado/motel-las-burgas-padre-hurtado': '+56228111463',
    'arica/motel-cabanas-paraiso-arica': '+56582226586',
    'la-cisterna/motel-lavien-rose-la-cisterna': '+56225581033',
    'chillan-viejo/motel-nevados-de-chillan-chillan-': '+56422261000',
    'san-miguel/motel-palmera-san-miguel': '+56225556534',
    'concepcion/motel-nevada-sur-concepcion': '+56412276966',
    'arica/motel-colcas-arica': '+56987075195',
    'punta-arenas/motel-las-minas-punta-arenas': '+56612218553',
    'antofagasta/motel-romance-antofagasta': '+56553205093',
    'concepcion/motel-cruz-656-concepcion': '+56412934723',
    'vina-del-mar/motel-bahia-vina-del-mar': '+56322871863',
    'temuco/motel-new-tekena-temuco': '+56941692760',
    'coronel/motel-iguazu-coronel': '+56412715301',
    'temuco/motel-geminis-temuco': '+56452273513',
    'santiago/motel-galaxia-santiago': '+56226995812',
    'punta-arenas/motel-platinum-punta-arenas': '+56612244078',
    'san-bernardo/motel-luna-y-mar': '+56225283216',
    'antofagasta/motel-mediterraneo-antofagasta': '+56552249082',
    'la-reina/motel-villa-alpina-la-reina': '+56572768012',
    'valdivia/motel-los-leones-valdivia': '+56632217998',
    'coquimbo/motel-corazon-coquimbo': '+56512752631',
    'valparaiso/motel-puerto-principe': '+56976154185',
    'puente-alto/motel-el-trauco-puente-alto': '+56264655118',
    'santiago/motel-ricardo-cumming-ricardo-cumming': '+56226711226',
    'vina-del-mar/motel-malibu-vina-del-mar': '+56322687724',
    'valparaiso/motel-arbolito-valparaiso': '+56322291088',
    'providencia/motel-holley-providencia': '+56934860081',
    'concepcion/motel-el-parque-concepcion': '+56412948943',
    'san-vicente/motel-los-naranjos': '+56223139585',
    'valparaiso/motel-pasion-valparaiso': '+56224571817',
    'santiago/motel-kalipso-santiago': '+56232912292',
    'la-cisterna/motel-veracruz-la-cisterna': '+56229986797',
    'santiago/motel-sol-y-luna-santiago': '+56987965336',
    'arica/motel-babilonia': '+56582329002',
    'la-pintana/motel-maromas-la-pintana': '+56224878144',
    'iquique/motel-eclipse-iquique': '+5657222066',
    'los-angeles/motel-cantarrero-los-angeles': '+56997938459',
    'concepcion/motel-fish-concepcion': '+56412082058',
    'vina-del-mar/motel-kiss-vina-del-mar': '+56322972428',
    'la-cisterna/motel-los-troncos-la-cisterna': '+56224162650',
    'vina-del-mar/motel-md-vina-del-mar': '+56322876066',
    'santiago/motel-primavera-santiago': '+56226993586',
    'pudahuel/motel-aerotel-travel-pudahuel': '+56226018492',
    'melipilla/motel-jardin-secreto-melipilla': '+56981492489',
    'punta-arenas/motel-eros-punta-arenas': '+56612227863',
    'arica/motel-eclipse-de-luna-arica': '+56991395868',
    'la-reina/motel-internacional-la-reina': '+56977736671',
    'la-granja/motel-el-trauco-la-granja': '+56930828541',
    'villa-alemana/motel-tantra-villa-alemana': '+56412382531',
    'coquimbo/motel-no-se-coquimbo': '+56512323955',
    'angol/motel-nahuen-angol': '+56992191124',
}

def get_motel_name(slug):
    parts = slug.strip('/').split('/')
    if len(parts) >= 2:
        name_part = parts[-1].replace('-', ' ').title()
        city = parts[0].replace('-', ' ').title()
        return name_part, city
    return parts[0].replace('-', ' ').title(), ''

def gen_email_text(name, city, clics, imp, ctr, pos, email):
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

def gen_wa_text(name, city, imp):
    return f"Hola {name} ({city}), soy Christian de infomoteles.cl. Tu ficha está teniendo {imp} impresiones en Google — podemos ayudarte a convertir eso en más clientes. ¿Tienes 5 min?"

fichas.sort(key=lambda r: int(r.get('impresiones',0) or 0), reverse=True)

lines = []
lines.append("# Outreach Personalizado para Moteles\n")
lines.append(f"Total fichas: {len(fichas)}\n")

count_email = 0
count_wa = 0
count_no = 0

for f in fichas:
    slug = f.get('slug','').strip('/')
    clics = f.get('clics','0')
    imp = f.get('impresiones','0')
    ctr = f.get('ctr','0')
    pos = f.get('posicion','0')
    name, city = get_motel_name(slug)
    email = known_emails.get(slug, '')
    phone = known_phones.get(slug, '')

    if email:
        lines.append(f"## {name} ({city})\n")
        lines.append(f"- URL: https://infomoteles.cl/{slug}/\n")
        lines.append(f"- Email: {email}\n")
        lines.append(f"- Rendimiento: {imp} imp, {clics} clics, {ctr}% CTR, pos {pos}\n\n")
        lines.append("```\n" + gen_email_text(name, city, clics, imp, ctr, pos, email) + "\n```\n\n---\n")
        count_email += 1
    elif phone:
        lines.append(f"## {name} ({city})\n")
        lines.append(f"- URL: https://infomoteles.cl/{slug}/\n")
        lines.append(f"- WhatsApp/Tel: {phone}\n")
        lines.append(f"- Rendimiento: {imp} imp, {clics} clics\n\n")
        lines.append("WhatsApp:\n```\n" + gen_wa_text(name, city, imp) + "\n```\n\n---\n")
        count_wa += 1
    else:
        count_no += 1

lines.append(f"\n## Sin Contacto ({count_no})\n\n")
for f in fichas:
    slug = f.get('slug','').strip('/')
    if slug not in known_emails and slug not in known_phones:
        name, city = get_motel_name(slug)
        imp = f.get('impresiones','0')
        lines.append(f"- [{name} ({city})](https://infomoteles.cl/{slug}/) - {imp} impresiones\n")

lines.append(f"\n## Resumen Final\n")
lines.append(f"- Con email: {count_email}\n")
lines.append(f"- WhatsApp listo: {count_wa}\n")
lines.append(f"- Sin contacto: {count_no}\n")

with open('entregables/outreach_completo.md', 'w', encoding='utf-8') as f:
    f.writelines(lines)

os.makedirs('entregables/emails_listos', exist_ok=True)
for f in fichas:
    slug = f.get('slug','').strip('/')
    email = known_emails.get(slug, '')
    if not email: continue
    clics = f.get('clics','0')
    imp = f.get('impresiones','0')
    ctr = f.get('ctr','0')
    pos = f.get('posicion','0')
    name, city = get_motel_name(slug)
    safe = slug.replace('/','-')
    body = gen_email_text(name, city, clics, imp, ctr, pos, email)
    with open(f'entregables/emails_listos/{safe}.eml', 'w', encoding='utf-8') as eml_f:
        eml_f.write(body)

print(f"OUTREACH COMPLETO GENERADO\n")
print(f"  Emails personalizados: {count_email}")
print(f"  WhatsApp preparados: {count_wa}")
print(f"  Sin contacto: {count_no}")
print(f"\nArchivos:")
print(f"  entregables/outreach_completo.md - documento completo")
print(f"  entregables/emails_listos/ - {count_email} archivos .eml")
