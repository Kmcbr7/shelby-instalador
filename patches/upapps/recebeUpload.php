<?php
/**
 * Shelby SSHPLUS - upload dos APKs da loja de apps (app_s1.apk, app_s2.apk, app_s3.apk).
 */
require_once(__DIR__ . "/../pages/system/seguranca.php");
protegePagina("admin");

$slot = isset($slot) ? (int) $slot : 1;
$voltar = '../admin/home.php?page=apps';

function shelby_upload_fim($ok, $msg, $voltar)
{
    $icone = $ok ? 'success' : 'error';
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><title>Shelby SSHPLUS | Apps</title></head><body>'
        . '<script>Swal.fire({icon:"' . $icone . '",title:' . json_encode($msg) . ',timer:2500,timerProgressBar:true})'
        . '.then(function(){window.location=' . json_encode($voltar) . ';});</script></body></html>';
    exit;
}

if (isset($_FILES['arquivo']['error']) && in_array($_FILES['arquivo']['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
    shelby_upload_fim(false, 'Arquivo grande demais para o limite do servidor (' . ini_get('upload_max_filesize') . ').', $voltar);
}
if (empty($_FILES['arquivo']) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    shelby_upload_fim(false, 'Arquivo grande demais para o limite do servidor (' . ini_get('post_max_size') . ').', $voltar);
}
if (empty($_FILES['arquivo']['name']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    shelby_upload_fim(false, 'Você não enviou nenhum arquivo!', $voltar);
}

$extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
if ($extensao !== 'apk') {
    shelby_upload_fim(false, 'Envie apenas arquivos .apk', $voltar);
}

$pasta = __DIR__ . '/../apps/apk/';
if (!is_dir($pasta)) {
    @mkdir($pasta, 0755, true);
}
$destino = $pasta . 'app_s' . $slot . '.apk';
if (is_file($destino)) {
    @unlink($destino);
}

if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
    shelby_upload_fim(true, 'App ' . $slot . ' enviado com sucesso!', $voltar);
}
shelby_upload_fim(false, 'Erro ao salvar o arquivo. Verifique a permissão da pasta apps/apk.', $voltar);
