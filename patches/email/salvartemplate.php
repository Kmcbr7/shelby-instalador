<?php
require_once("../../../pages/system/seguranca.php");
require_once("../../../pages/system/config.php");
require_once("../../../pages/system/funcoes.system.php");
require_once(__DIR__ . "/shelby_email_tpl.php");
protegePagina("admin");
$volta = '../../home.php?page=email/enviaremail';
$c = shelby_email_cfg();
foreach (array('remetente','titulo','banner','banner_link','app_texto','app_link','wa_texto','wa_link','rodape') as $k) {
    if (isset($_POST[$k])) $c[$k] = trim((string)$_POST[$k]);
}
if (!empty($_FILES['banner_arquivo']['tmp_name']) && is_uploaded_file($_FILES['banner_arquivo']['tmp_name'])) {
    $info = @getimagesize($_FILES['banner_arquivo']['tmp_name']);
    $ext = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp');
    if (!$info || !isset($ext[$info[2]])) { echo myalertuser('error', 'Imagem inválida (use JPG, PNG, GIF ou WEBP)', $volta); exit; }
    if ($_FILES['banner_arquivo']['size'] > 5 * 1024 * 1024) { echo myalertuser('error', 'Imagem acima de 5 MB', $volta); exit; }
    $root = realpath(__DIR__ . '/../../..');
    $dir = $root . '/apps/email';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    foreach (glob($dir . '/banner-*') ?: array() as $old) @unlink($old);
    $nome = 'banner-' . date('YmdHis') . '.' . $ext[$info[2]];
    if (!move_uploaded_file($_FILES['banner_arquivo']['tmp_name'], $dir . '/' . $nome)) { echo myalertuser('error', 'Sem permissão para salvar a imagem', $volta); exit; }
    $c['banner'] = 'https://' . $_SERVER['HTTP_HOST'] . '/apps/email/' . $nome;
}
if (isset($_POST['remover_banner'])) $c['banner'] = '';
$f = shelby_email_cfg_path();
if (@file_put_contents($f . '.tmp', json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false || !@rename($f . '.tmp', $f)) {
    echo myalertuser('error', 'Sem permissão para salvar a configuração', $volta); exit;
}
echo myalertuser('success', 'Visual do email salvo!', $volta);
