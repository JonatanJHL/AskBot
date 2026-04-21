<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$nombre = htmlspecialchars($data['nombre'] ?? '');
$empresa = htmlspecialchars($data['empresa'] ?? '');
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$plan = htmlspecialchars($data['plan'] ?? '');
$mensaje = htmlspecialchars($data['mensaje'] ?? '');

if (!$nombre || !$email) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER') ?: 'tu-email@gmail.com';
    $mail->Password   = getenv('SMTP_PASS') ?: 'tu-app-password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('noreply@botia.com', 'Bot IA - Web');
    $mail->addAddress('tu-email@ejemplo.com', 'Creador');
    $mail->addReplyTo($email, $nombre);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $mail->Subject = "Nuevo contacto - Bot IA - $plan";
    
    $body = "
    <h2>Nuevo contacto desde web Bot IA</h2>
    <p><strong>Nombre:</strong> $nombre</p>
    <p><strong>Empresa:</strong> $empresa</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Plan:</strong> $plan</p>
    <p><strong>Mensaje:</strong></p>
    <p>$mensaje</p>
    ";
    $mail->Body    = $body;
    $mail->AltBody = strip_tags($body);

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . $mail->ErrorInfo]);
}
