$smtpServer = "mail.infomoteles.cl"
$smtpPort = 465
$from = "contacto@infomoteles.cl"
$fromPassword = "CVcv1065//"
$to = "contacto@motel-euro.cl"

$subject = "Motel Euro Calama - 640 personas buscan 'motel euro' en Google cada mes"

$body = @"
Hola,

Soy Cristian, de infomoteles.cl — el directorio de moteles mas visitado de Chile.

El Motel Euro aparece en posiciones destacadas cuando alguien busca "motel euro calama" en Google, con mas de 640 impresiones al mes. Es un flujo constante de clientes potenciales que hoy no tiene un canal de contacto directo en nuestra plataforma.

Te ofrezco activar una ficha destacada premium por $59.990/mes, sin permanencia. Incluye:

- Fotos de tus habitaciones
- Precios y promociones actualizados
- Boton de WhatsApp directo
- Enlace a tu web si tienes

La ficha queda visible en infomoteles.cl, que recibe trafico organico de parejas buscando moteles en todo Chile.

Sin permanencia. Si no te funciona, cancelas cuando quieras.

¿Te parece si te envio un link de ejemplo para que lo veas?

Saludos,
Cristian Vieille
contacto@infomoteles.cl
infomoteles.cl
"@

try {
    $smtp = New-Object Net.Mail.SmtpClient($smtpServer, $smtpPort)
    $smtp.EnableSsl = $true
    $smtp.Credentials = New-Object System.Net.NetworkCredential($from, $fromPassword)
    $smtp.Send($from, $to, $subject, $body)
    Write-Host "EMAIL ENVIADO a contacto@motel-euro.cl" -ForegroundColor Green
} catch {
    Write-Host "ERROR al enviar: $_" -ForegroundColor Red
    Write-Host "Prueba manual: copia el cuerpo del email y envialo desde tu correo" -ForegroundColor Yellow
}
