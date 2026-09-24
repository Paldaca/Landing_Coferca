<?php
header('Content-Type: application/json; charset=utf-8');

$destinatario   = "info@coferca.com.ve"; 
$remitente_web  = "no-reply@coferca.com"; // DEBE existir en tu servidor cPanel/Hosting
$asunto_prefijo = "Nuevo mensaje web - Ingeniería Cofer: ";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre    = isset($_POST['nombre'])    ? trim(strip_tags($_POST['nombre'])) : '';
    $empresa   = isset($_POST['empresa'])   ? trim(strip_tags($_POST['empresa'])) : 'No especificada';
    $email     = isset($_POST['email'])     ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $telefono  = isset($_POST['telefono'])  ? trim(strip_tags($_POST['telefono'])) : 'No proporcionado';
    $servicio  = isset($_POST['servicio'])  ? trim(strip_tags($_POST['servicio'])) : 'General';
    $mensaje   = isset($_POST['mensaje'])   ? trim(strip_tags($_POST['mensaje'])) : '';

    if (empty($nombre) || empty($email) || empty($mensaje)) {
        echo json_encode(["status" => "error", "message" => "Por favor, completa los campos obligatorios."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "El correo electrónico no es válido."]);
        exit;
    }

    $asunto = $asunto_prefijo . $servicio;
    
    // Texto plano como alternativa (previene filtros antispam)
    $cuerpoTexto = "Nuevo mensaje desde la web:\n\n";
    $cuerpoTexto .= "Nombre: $nombre\n";
    $cuerpoTexto .= "Empresa: $empresa\n";
    $cuerpoTexto .= "Correo: $email\n";
    $cuerpoTexto .= "Teléfono: $telefono\n";
    $cuerpoTexto .= "Servicio: $servicio\n";
    $cuerpoTexto .= "Mensaje:\n$mensaje\n";

    // Cuerpo HTML
    $cuerpoHTML = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f8fafc; color: #1e293b; padding: 20px; }
            .container { max-width: 600px; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; }
            .header { border-bottom: 3px solid #FF7609; padding-bottom: 15px; margin-bottom: 20px; }
            .header h2 { color: #0A1349; margin: 0; }
            .field { margin-bottom: 12px; }
            .label { font-weight: bold; color: #0A1349; }
            .message-box { background: #f1f5f9; padding: 15px; border-left: 4px solid #FF7609; margin-top: 15px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'><h2>Nuevo Contacto - Ingeniería Cofer</h2></div>
            <div class='field'><span class='label'>Nombre:</span> {$nombre}</div>
            <div class='field'><span class='label'>Empresa:</span> {$empresa}</div>
            <div class='field'><span class='label'>Correo:</span> {$email}</div>
            <div class='field'><span class='label'>Teléfono:</span> {$telefono}</div>
            <div class='field'><span class='label'>Servicio:</span> {$servicio}</div>
            <div class='message-box'>
                <span class='label'>Mensaje:</span><br>" . nl2br($mensaje) . "
            </div>
        </div>
    </body>
    </html>";

    // Cabeceras avanzadas anti-spam
    $headers   = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: Ingeniería Cofer Web <{$remitente_web}>"; // Usar correo del propio dominio
    $headers[] = "Reply-To: {$nombre} <{$email}>";       // Permite responder al cliente
    $headers[] = "Return-Path: <{$remitente_web}>";
    $headers[] = "X-Mailer: PHP/" . phpversion();

    // Parámetro -f en la función mail para forzar el Return-Path a nivel de servidor
    if (mail($destinatario, $asunto, $cuerpoHTML, implode("\r\n", $headers), "-f" . $remitente_web)) {
        echo json_encode(["status" => "success", "message" => "¡Gracias por contactarnos! Tu mensaje ha sido enviado."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al enviar el correo. Por favor intenta más tarde."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Acceso no permitido."]);
}
?>