<?php
if (basename($_SERVER["REQUEST_URI"]) === basename(__FILE__)) {
    exit('<h1>ERROR 404</h1>');
}
require_once(__DIR__ . "/shelby_email_tpl.php");
$st = $conn->prepare("SELECT * FROM smtp WHERE usuario_id = ?");
$st->execute(array($_SESSION['usuarioID']));
$smtp = $st->fetch();
$c = shelby_email_cfg();
$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
?>
<style>
.sh-mail .card{border-radius:18px}
.sh-mail h4{color:#f5b81c;font-weight:700;margin:0}
.sh-mail .sub{color:#8fa0c8;font-size:13px}
.sh-mail label{color:#cfd8f0;font-size:13px;margin:10px 0 4px;display:block}
.sh-mail .btns .btn{margin:6px 4px;border-radius:12px;font-weight:700}
.sh-mail .bprev{max-width:100%;max-height:160px;border-radius:12px;border:1px solid #1f6f86;margin-top:8px}
.sh-mail .status{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700}
</style>
<section class="sh-mail">
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-header"><div><h4>✉️ Enviar email</h4>
<div class="sub">SMTP: <?php if ($smtp) { ?><span class="status" style="background:#123d2a;color:#3ee38a">conectado · <?= $h($smtp['email']) ?></span><?php } else { ?><span class="status" style="background:#3d1220;color:#ff6b8a">não configurado</span><?php } ?></div></div></div>
<div class="card-body">
<form action="pages/email/enviandoemail.php" method="POST" id="fmail">
<div class="row">
<div class="col-sm-8 col-12"><label>Email do cliente</label><input required type="text" class="form-control" name="email" placeholder="cliente@gmail.com"></div>
<div class="col-sm-4 col-12"><label>Completar com</label><select class="form-select" name="servidoremail">
<option value="1">Eu digito completo</option><option value="2">@gmail.com</option><option value="3">@outlook.com</option><option value="4">@hotmail.com</option><option value="5">@yahoo.com.br</option></select></div>
<div class="col-12"><label>Assunto</label><input type="text" class="form-control" name="assunto" value="Acesso de SSH Liberado"></div>
<div class="col-sm-4 col-12"><label>Login (opcional)</label><input type="text" class="form-control" name="login"></div>
<div class="col-sm-4 col-12"><label>Senha (opcional)</label><input type="text" class="form-control" name="senha"></div>
<div class="col-sm-4 col-12"><label>Validade (opcional)</label><input type="text" class="form-control" name="validade" value="30 Dias"></div>
<div class="col-12"><label>Mensagem</label><textarea class="form-control" name="msg" rows="5">Obrigado pela preferência, prezamos pela qualidade de conexão.</textarea></div>
<div class="col-12 text-center btns">
<button type="submit" name="acao" value="enviar" class="btn btn-warning">🚀 Enviar</button>
<button type="submit" name="acao" value="previa" formtarget="_blank" class="btn btn-info">👁️ Prévia</button>
<a class="btn btn-primary" href="home.php?page=email/1etapasmtp">⚙️ Configurar SMTP</a>
</div>
</div>
</form>
</div>
</div>
</div>

<div class="col-12">
<div class="card">
<div class="card-header"><div><h4>🎨 Visual do email</h4><div class="sub">Imagem do topo, links clicáveis e botões. Vale para todos os envios.</div></div></div>
<div class="card-body">
<form action="pages/email/salvartemplate.php" method="POST" enctype="multipart/form-data">
<div class="row">
<div class="col-sm-6 col-12"><label>Nome da marca</label><input class="form-control" name="remetente" value="<?= $h($c['remetente']) ?>"></div>
<div class="col-sm-6 col-12"><label>Título da mensagem</label><input class="form-control" name="titulo" value="<?= $h($c['titulo']) ?>"></div>
<div class="col-12"><label>🖼️ Imagem do topo: enviar arquivo</label><input type="file" class="form-control" name="banner_arquivo" accept="image/*"></div>
<div class="col-12"><label>…ou colar link da imagem</label><input class="form-control" name="banner" value="<?= $h($c['banner']) ?>" placeholder="https://...jpg">
<?php if ($c['banner']) { ?><img class="bprev" src="<?= $h($c['banner']) ?>"><br><label style="display:inline"><input type="checkbox" name="remover_banner"> remover imagem</label><?php } ?></div>
<div class="col-12"><label>🔗 Link ao tocar na imagem</label><input class="form-control" name="banner_link" value="<?= $h($c['banner_link']) ?>" placeholder="https://..."></div>
<div class="col-sm-5 col-12"><label>Texto do botão do app</label><input class="form-control" name="app_texto" value="<?= $h($c['app_texto']) ?>"></div>
<div class="col-sm-7 col-12"><label>Link do botão do app</label><input class="form-control" name="app_link" value="<?= $h($c['app_link']) ?>" placeholder="https://.../app.apk"></div>
<div class="col-sm-5 col-12"><label>Texto do botão WhatsApp</label><input class="form-control" name="wa_texto" value="<?= $h($c['wa_texto']) ?>"></div>
<div class="col-sm-7 col-12"><label>WhatsApp (número ou link)</label><input class="form-control" name="wa_link" value="<?= $h($c['wa_link']) ?>" placeholder="62 99800-1820"></div>
<div class="col-12"><label>Rodapé</label><input class="form-control" name="rodape" value="<?= $h($c['rodape']) ?>"></div>
<div class="col-12 sub" style="margin-top:8px">Deixe um link vazio para esconder o botão.</div>
<div class="col-12 text-center btns"><button type="submit" class="btn btn-success">💾 Salvar visual</button></div>
</div>
</form>
</div>
</div>
</div>
</div>
</section>
