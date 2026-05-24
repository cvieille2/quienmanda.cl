import imaplib
import email
import smtplib
import re
import time
import sys
from email.header import decode_header
from email.mime.text import MIMEText
from email.utils import formataddr

sys.stdout = open(1, 'w', encoding='utf-8', closefd=False)

IMAP_SERVER = "mail.visitandopuntaarenas.cl"
IMAP_PORT = 993
SMTP_SERVER = "mail.visitandopuntaarenas.cl"
SMTP_PORT = 465
EMAIL_ACCOUNT = "admin@visitandopuntaarenas.cl"
PASSWORD = "CVcv1065//"

SITE_URL = "https://visitandopuntaarenas.cl/"

# Connect to IMAP
mail = imaplib.IMAP4_SSL(IMAP_SERVER, IMAP_PORT)
mail.login(EMAIL_ACCOUNT, PASSWORD)
mail.select("INBOX")

status, messages = mail.search(None, "ALL")
email_ids = messages[0].split()
print(f"Total emails: {len(email_ids)}")

# Connect to SMTP
smtp = smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT)
smtp.login(EMAIL_ACCOUNT, PASSWORD)

def send_reply(to_email, subject, body, in_reply_to_msg_id=None, references=None):
    msg = MIMEText(body, "plain", "utf-8")
    msg["From"] = formataddr(("Visitando Punta Arenas", EMAIL_ACCOUNT))
    msg["To"] = to_email
    msg["Subject"] = subject
    if in_reply_to_msg_id:
        msg["In-Reply-To"] = in_reply_to_msg_id
        msg["References"] = (references or "") + " " + in_reply_to_msg_id
    try:
        smtp.sendmail(EMAIL_ACCOUNT, [to_email], msg.as_string())
        print(f"  [OK] Sent to {to_email}")
        return True
    except Exception as e:
        print(f"  [ERR] {e}")
        return False

replied = 0
skipped = 0
errored = 0

for eid in email_ids:
    status, data = mail.fetch(eid, "(RFC822)")
    raw = data[0][1]
    msg = email.message_from_bytes(raw)

    subject_raw = msg["Subject"]
    subject = decode_header(subject_raw)[0][0]
    if isinstance(subject, bytes):
        subject = subject.decode("utf-8", errors="replace")
    subject = subject.replace('\xa0', ' ').replace('\u202f', ' ')

    sender = msg["From"]
    msg_id = msg["Message-ID"]
    references = msg["References"] or ""

    body = ""
    if msg.is_multipart():
        for part in msg.walk():
            if part.get_content_type() == "text/plain":
                b = part.get_payload(decode=True)
                if b:
                    body = b.decode("utf-8", errors="replace")
                    break
    else:
        b = msg.get_payload(decode=True)
        if b:
            body = b.decode("utf-8", errors="replace")

    body_lower = body.lower()
    is_contact_form = "formulario de contacto" in body_lower and "esto es un aviso" in body_lower

    # Extract original sender from contact form
    orig_sender = None
    orig_subject = None
    orig_name = ""
    if is_contact_form:
        for line in body.split('\n'):
            if line.lower().startswith("de:") and "@" in line:
                m = re.search(r'[\w.+-]+@[\w-]+\.[\w.-]+', line)
                if m:
                    orig_sender = m.group(0)
                name_part = line[3:].strip()
                if '<' in name_part:
                    orig_name = name_part.split('<')[0].strip()
                else:
                    orig_name = orig_sender or ""
                break
        for line in body.split('\n'):
            if line.lower().startswith("asunto:"):
                orig_subject = line[7:].strip()
                break

    # Extract sender email
    sender_email_match = re.search(r'[\w.+-]+@[\w-]+\.[\w.-]+', sender)
    sender_email = sender_email_match.group(0) if sender_email_match else ""

    # Determine actual recipient and key identifier
    if is_contact_form and orig_sender and sender_email == EMAIL_ACCOUNT:
        actual_recipient = orig_sender
        key_email = orig_sender
        key_name = orig_name
    else:
        actual_recipient = sender_email
        key_email = sender_email
        key_name = sender.split('<')[0].strip() if '<' in sender else sender

    # Skip test/self/newsletter
    if sender_email == EMAIL_ACCOUNT and not is_contact_form:
        print(f"\nSKIP (self): ID {eid.decode()} - {subject[:50]}")
        skipped += 1
        continue

    # Clean subject for reply
    base_subject = orig_subject or subject
    base_subject = re.sub(r'^Punta Arenas\s*[\xab\xbb\u00ab\u00bb\u201c\u201d""]\s*', '', base_subject)
    base_subject = re.sub(r'[\xab\xbb\u00ab\u00bb\u201c\u201d""]\s*$', '', base_subject)
    reply_subject = f"Re: {base_subject[:80]}".strip()

    # ===== SPAM =====
    if key_email in ["dario.1955@yahoo.com", "cheyannvyp48@gmail.com"]:
        print(f"\nSKIP (spam): ID {eid.decode()} - {key_email}")
        skipped += 1
        continue
    if "need assistance" in body_lower or "freshnewleads" in body_lower or "adstocontactforms" in body_lower:
        print(f"\nSKIP (ad): ID {eid.decode()} - {key_email}")
        skipped += 1
        continue
    if "neaxans" in body_lower:
        print(f"\nSKIP (spam solicitud): ID {eid.decode()} - {key_email}")
        skipped += 1
        continue
    if "onrunads" in body_lower:
        print(f"\nSKIP (ad proposal): ID {eid.decode()} - {key_email}")
        skipped += 1
        continue

    # ===== REPLY CONTENT =====
    print(f"\nREPLY: ID {eid.decode()} -> {key_email}")

    # --- Museo Maggiorino Borgatello / Christophe Pollet ---
    if "museomaggiorinoborgatello" in key_email:
        reply_body = f"""Estimado Christophe,

Muchas gracias por su paciencia y por insistir en la actualización de los horarios del Museo Maggiorino Borgatello.

Hemos procedido a realizar la corrección en nuestra página. Los horarios actualizados son: martes a sábado de 10:00 a 12:30 y de 14:00 a 17:30.

Puede verificar los cambios en nuestro sitio web:
{SITE_URL}

Nuevamente, disculpe la demora y agradecemos su colaboración para mantener la información correcta y actualizada.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Vladimir Nesvadba (cualquier correo de él) ---
    elif "vnesvadbam" in key_email:
        reply_body = f"""Estimado Vladimir,

Gracias por su correo. Lamentamos los inconvenientes que ha tenido con la otra plataforma de consultas.

Le reiteramos que visitandopuntaarenas.cl es un sitio web informativo y gratuito sobre la región de Magallanes. Nosotros no cobramos por ninguna información ni tenemos relación con el sitio "Just Answer".

Le invitamos a revisar nuestra página donde encontrará guías y artículos útiles sobre Punta Arenas, completamente gratis:
{SITE_URL}

Quedamos atentos por si necesita más información.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Yeraldine Ortega - configurar imagen restaurante ---
    elif "azucarestaurante" in key_email:
        reply_body = f"""Estimada Yeraldine,

Gracias por contactarnos. Visitandopuntaarenas.cl es un sitio informativo y no administramos directamente las imágenes ni perfiles de restaurantes en plataformas de terceros.

Le recomendamos contactar directamente con el restaurante para que ellos soliciten la actualización de su imagen donde corresponda.

Puede visitar nuestra página para más información sobre Punta Arenas:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Silvia Rubinos - horarios barcaza ---
    elif "silviamrubinos" in key_email:
        reply_body = f"""Estimada Silvia,

Gracias por su consulta. No manejamos los horarios actualizados de la barcaza a Porvenir, ya que varían según temporada y operadora.

Le sugerimos consultar directamente en el Terminal de Transbordadores de Punta Arenas o contactar a Transbordadora Austral Broom.

En nuestra página puede encontrar información general sobre cómo llegar a Tierra del Fuego:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Giovanni Blasich - Info proa Yelcho ---
    elif "gblasich8" in key_email:
        reply_body = f"""Estimado Giovanni,

Interesante consulta. La proa original del Yelcho se encuentra en la Plaza de la Aviación de la Fuerza Aérea de Chile, cercana al Aeropuerto Presidente Carlos Ibáñez del Campo de Punta Arenas.

Puede encontrar más información histórica en nuestra página web:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Ilona Soto - Pingüinos Isla Magdalena ---
    elif "sotodonosoilona" in key_email:
        reply_body = f"""Estimada Ilona,

Gracias por su interés. Para visitar Isla Magdalena debe contactar directamente a las navieras que operan excursiones desde Punta Arenas (Solo Expediciones, Turismo Comapa, etc.). Nosotros no realizamos reservas.

Enero es una excelente fecha dentro de la temporada de pingüinos (octubre a marzo).

En nuestra página puede encontrar más información sobre excursiones:
{SITE_URL}

¡Esperamos que disfrute su visita!

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Cristian Pérez - trajes neopreno ---
    elif "c_perez60" in key_email:
        reply_body = f"""Estimado Cristian,

Gracias por su consulta. En Punta Arenas existen tiendas de artículos deportivos y outdoor donde podría encontrar trajes de neopreno. Le recomendamos buscar en EasySur u otras tiendas del centro.

Puede visitar nuestra página para más información sobre la ciudad:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Julia Svarre - Consulta sorteo ---
    elif "juliasvarre" in key_email:
        reply_body = f"""Estimada Julia,

Gracias por su consulta. No tenemos información sobre los sorteos de Zona Franca ni los requisitos para reclamar premios. Le sugerimos contactar directamente con la administración de Zona Franca de Punta Arenas.

Puede visitar nuestra página para más información:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Jhon Torres - cotización pasaje ---
    elif "jhonsteven0625" in key_email or "jhon" in key_email.lower():
        reply_body = f"""Estimado Jhon,

Gracias por su consulta. Nosotros no vendemos pasajes ni hacemos cotizaciones. Para viajar de Punta Arenas a Río Gallegos, consulte directamente con las empresas de buses internacionales.

En nuestra página encontrará información general sobre transporte:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Maria Orellana - renovación nicho ---
    elif "maryeliz7373" in key_email:
        reply_body = f"""Estimada María,

Gracias por su mensaje. Para renovación de nichos debe contactar directamente con la administración del Cementerio Municipal de Punta Arenas.

Puede visitar nuestra página para más información:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Paulina Pineda - Isla Magdalena ---
    elif "paulipineda" in key_email:
        reply_body = f"""Estimada Paulina,

Gracias por su interés. La temporada de pingüinos en Isla Magdalena va de octubre a marzo. A principios de abril es posible que aún haya excursiones, pero dependerá del clima y la disponibilidad de las navieras.

Le sugerimos contactar directamente a las operadoras turísticas.

En nuestra página encontrará más información:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Alioska Gómez / Mundo PMO - Viajar en auto ---
    elif "mundopmo" in key_email or "agomez" in key_email:
        reply_body = f"""Estimados,

Gracias por su consulta. Para viajar de Santiago a Punta Arenas en camión hay dos rutas principales: por Chile hasta Puerto Montt y luego ferry, o por Argentina cruzando la Patagonia argentina.

En nuestra página puede encontrar información sobre distancias y rutas:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Patricio Uribe - Currículum ---
    elif "patricioredondo19" in key_email:
        reply_body = f"""Estimado Patricio,

Gracias por hacernos llegar su currículum. Somos un sitio informativo turístico y no contamos con bolsa de trabajo.

Le sugerimos enviar su currículum directamente a restaurantes, hoteles y empresas gastronómicas de Punta Arenas.

Puede visitar nuestra página para conocer más sobre la ciudad:
{SITE_URL}

Le deseamos éxito en su búsqueda laboral.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Eduardo Aedo - servicio restaurante ---
    elif "eaedo@uach" in key_email:
        reply_body = f"""Estimado Eduardo,

Gracias por su consulta. Somos un sitio informativo, no un restaurante ni servicio de catering.

Le sugerimos contactar directamente con restaurantes locales. En nuestra página puede encontrar un directorio:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Paola - Horario atención ---
    elif "paomartinez26" in key_email:
        reply_body = f"""Estimada Paola,

Gracias por su mensaje. No tenemos información actualizada sobre qué local está atendiendo o en remodelación. Consulte directamente con el establecimiento.

Puede revisar nuestra página para más información:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Paulina Fajardo - fuente investigación ---
    elif "paulina.fajardoc" in key_email:
        reply_body = f"""Estimada Paulina,

¡Muchas gracias por su interés! Nos alegra que nuestra información histórica le sea útil para su trabajo en turismo y patrimonio.

Puede utilizar nuestras publicaciones como referencia. En nuestra página encontrará artículos sobre la familia Menéndez y el Parque María Behety:
{SITE_URL}

Si necesita más detalles, no dude en escribirnos.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Yves Cadaux - Reserva Porvenir ---
    elif "cadaux.yves" in key_email:
        reply_body = f"""Bonjour Yves,

Gracias por su mensaje. Para reservar pasajes en la barcaza a Porvenir debe hacerlo directamente con la naviera (Transbordadora Austral Broom). Nosotros no gestionamos reservas.

Puede encontrar información útil en nuestra página:
{SITE_URL}

¡Buen viaje!

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Romina Estrada - Reclamo accidente ---
    elif "r.estradamansilla" in key_email:
        reply_body = f"""Estimada Romina,

Lamentamos lo sucedido en el Mercado Municipal. Para formalizar un reclamo, diríjase a la Municipalidad de Punta Arenas, Oficina de Atención Ciudadana o Departamento de Inspección.

En nuestra página puede encontrar información sobre la ciudad:
{SITE_URL}

Esperamos que su reclamo sea atendido.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Linda - Viaje Porvenir bicicleta ---
    elif "lulusurvida" in key_email:
        reply_body = f"""Estimada Linda,

Gracias por su consulta. Para viajar a Porvenir con bicicleta debe tomar el ferry. Consulte con la naviera sobre el costo adicional por llevar bicicleta y los horarios.

En nuestra página encontrará información sobre la ruta:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Valentina Herrera - búsqueda padre ---
    elif "valantohv" in key_email:
        reply_body = f"""Estimada Valentina,

Gracias por contactarnos. Entendemos lo importante que es esta búsqueda para su familia. Le sugerimos:

1. Contactar al Cementerio Municipal de Punta Arenas.
2. Solicitar certificados en el Registro Civil.
3. Consultar en el Archivo Histórico Municipal.

Nosotros no tenemos acceso a registros de defunciones. En nuestra página puede encontrar información general:
{SITE_URL}

Le deseamos éxito en su búsqueda.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Nelly Valdivia - Visita pedagógica ---
    elif "nellyvaldiviam1" in key_email:
        reply_body = f"""Estimada Nelly,

Gracias por su mensaje. No administramos parques ni reservas. Si se refiere al Parque María Behety, contacte a la Municipalidad de Punta Arenas para coordinar la visita educativa.

En nuestra página encontrará información sobre parques:
{SITE_URL}

¡Que los niños disfruten la visita!

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Marco Cuadra - número restaurante ---
    elif "marco.cuadra" in key_email:
        reply_body = f"""Estimado Marco,

Gracias por su consulta. No disponemos del número actualizado del Restaurant Ravoy. Le sugerimos buscar en redes sociales o directorios locales.

En nuestra página encontrará información sobre restaurantes:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Ana Aspee - Reclamo estacionamiento ---
    elif "ana.aspee" in key_email:
        reply_body = f"""Estimada Ana,

Gracias por su mensaje. Para reclamos sobre parquímetros, contacte a la Municipalidad de Punta Arenas o al Departamento de Tránsito.

En nuestra página encontrará información útil:
{SITE_URL}

Esperamos que pueda resolverlo.

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Mario Ochoa - Reserva Okusa ---
    elif "marioochoa145" in key_email:
        reply_body = f"""Estimado Mario,

Gracias por su consulta. Nosotros no realizamos reservas en restaurantes. Contacte directamente al Restaurant Okusa para su reserva del 31 de diciembre.

En nuestra página encontrará información sobre restaurantes:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Máximo Meza - FOTOS FALSAS ---
    elif "maxameza" in key_email:
        reply_body = f"""Estimado Máximo,

Muchas gracias por su observación. Revisaremos la fotografía del Restaurant A Brasas para verificar su autenticidad. Agradecemos su ayuda para mantener la información correcta.

Valoramos este tipo de correcciones. Puede visitar nuestra página para más contenido:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Hugo Trecco - Antártica ---
    elif "hutrecco" in key_email:
        reply_body = f"""Estimado Hugo,

Gracias por su consulta. Para viajar a la Antártica desde Punta Arenas, las opciones más económicas suelen ser vuelos charter a Isla Rey Jorge o expediciones en barco con descuentos de última hora.

Consulte con operadores turísticos especializados en Punta Arenas.

En nuestra página encontrará información sobre la Antártica:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Elizabeth Lausic - Corrección información ---
    elif "elausic" in key_email:
        reply_body = f"""Estimada Elizabeth,

Muchas gracias por su observación. Tiene toda la razón y agradecemos que nos haya escrito para corregir esta información. Revisaremos nuestra página para distinguir correctamente entre el Museo Regional de Magallanes (Palacio Braun Menéndez) y el Museo Sara Braun.

Valoramos mucho su ayuda para mantener información precisa.

Puede ver los cambios en nuestra página:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    # --- Paulina Pineda ---
    elif "paulipineda" in key_email:
        reply_body = f"""Estimada Paulina,

Gracias por su interés. La temporada de pingüinos va de octubre a marzo. A principios de abril consulte directamente con las navieras.

En nuestra página encontrará más información:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    else:
        reply_body = f"""Estimado/a,

Gracias por contactarnos a través de visitandopuntaarenas.cl. Somos un sitio informativo sobre la región de Magallanes y la Patagonia chilena.

Le invitamos a visitar nuestra página web donde encontrará información actualizada sobre Punta Arenas, sus atractivos turísticos, restaurantes, alojamiento y más:
{SITE_URL}

Saludos cordiales,
Visitando Punta Arenas"""

    success = send_reply(actual_recipient, reply_subject, reply_body, msg_id, references)
    if success:
        replied += 1
    else:
        errored += 1
    time.sleep(1.5)

smtp.quit()
mail.logout()

print(f"\n\n========== RESUMEN ==========")
print(f"Total emails: {len(email_ids)}")
print(f"Replied: {replied}")
print(f"Skipped: {skipped}")
print(f"Errors: {errored}")
