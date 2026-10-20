<?php
require_once("../../../pages/system/seguranca.php");
require_once("../../../pages/system/config.php");
require_once("../../../pages/system/funcoes.system.php");
require_once(__DIR__ . "/shelby_email_tpl.php");
protegePagina("admin");

$volta = '../../home.php?page=email/enviaremail';
$P = function ($k) { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : ''; };
$preview = isset($_POST['acao']) && $_POST['acao'] === 'previa';

$cfg = shelby_email_cfg();
$dados = array('Login' => $P('login'), 'Senha' => $P('senha'), 'Validade' => $P('validade'));
$msg = $P('msg');
$html = shelby_email_html($cfg, $msg, $dados);

if ($preview) { header('Content-Type: text/html; charset=utf-8'); echo $html; exit; }

$email = strtolower($P('email'));
$srv = array('2' => '@gmail.com', '3' => '@outlook.com', '4' => '@hotmail.com', '5' => '@yahoo.com.br');
$sel = $P('servidoremail');
if (isset($srv[$sel]) && strpos($email, '@') === false) $email .= $srv[$sel];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo myalertuser('error', 'Email inválido', $volta); exit; }
if ($msg === '') { echo myalertuser('error', 'Escreva a mensagem!', $volta); exit; }

$st = $conn->prepare("SELECT * FROM smtp WHERE usuario_id = ?");
$st->execute(array($_SESSION['usuarioID']));
$smtp = $st->fetch();
if (!$smtp) { echo myalertuser('error', 'Configure o SMTP primeiro!', $volta); exit; }

$assunto = $P('assunto') !== '' ? $P('assunto') : 'Mensagem de ' . $cfg['remetente'];

require_once("../phpmailer/class.phpmailer.php");
require_once("../phpmailer/class.smtp.php");
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPSecure = $smtp['ssl_secure'];
$mail->Host = $smtp['servidor'];
$mail->Port = $smtp['porta'];
$mail->SMTPDebug = 0;
$mail->SMTPAuth = true;
$mail->Username = $smtp['email'];
$mail->Password = $smtp['senha'];
$mail->From = $smtp['email'];
$mail->FromName = $cfg['remetente'];
$mail->AddAddress($email);
$mail->IsHTML(true);
$mail->CharSet = 'UTF-8';
$mail->Subject = $assunto;
$mail->Body = $html;
$alt = $msg;
foreach ($dados as $k => $v) if ($v !== '') $alt .= "\n$k: $v";
if (shelby_url_ok($cfg['app_link'])) $alt .= "\nBaixar App: " . shelby_url_ok($cfg['app_link']);
if (shelby_wa_link($cfg['wa_link'])) $alt .= "\nWhatsApp: " . shelby_wa_link($cfg['wa_link']);
$mail->AltBody = $alt;
$ok = $mail->Send();
if ($ok) {
    echo myalertuser('success', 'Email enviado!', $volta);
} else {
    echo '<script>alert(' . json_encode('Erro ao enviar: ' . $mail->ErrorInfo) . ');window.location=' . json_encode($volta) . ';</script>';
}
