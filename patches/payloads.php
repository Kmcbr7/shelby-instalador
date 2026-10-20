<?php
// Shelby SSH - Atualizar App: cola o update.json completo, ajusta a versão e publica
if (basename($_SERVER["SCRIPT_FILENAME"] ?? '') === basename(__FILE__)) { header('Location: ../../index.php?e=404'); exit; }
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

$webRoot  = realpath(__DIR__ . '/../../..');
$jsonFile = $webRoot . '/update.json';
$bakDir   = $webRoot . '/config/payload-backups';
$host     = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? '');
$publicUrl = 'https://' . preg_replace('/:\d+$/', '', $host) . '/update.json';

if (empty($_SESSION['shelby_csrf'])) { $_SESSION['shelby_csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['shelby_csrf'];
$msg = ''; $msgType = 'ok'; $keepRaw = null;

function shelby_bak($file, $dir) {
    if (!is_file($file)) return;
    @mkdir($dir, 0750, true);
    @copy($file, $dir . '/update-' . date('Ymd-His') . '.json');
    $all = glob($dir . '/update-*.json') ?: []; rsort($all);
    foreach (array_slice($all, 20) as $old) @unlink($old);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shelby_action'])) {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $msg = 'Sessão expirada. Recarregue a página.'; $msgType = 'err';
    } else {
        $raw = '';
        if ($_POST['shelby_action'] === 'restore') {
            $b = basename($_POST['backup'] ?? '');
            if (preg_match('/^update-\d{8}-\d{6}\.json$/', $b) && is_file("$bakDir/$b")) $raw = file_get_contents("$bakDir/$b");
        } elseif ($_POST['shelby_action'] === 'save' && trim($_POST['json'] ?? '') === '' && preg_match('#^https://#i', trim($_POST['link'] ?? ''))) {
            $ctx = stream_context_create(['http' => ['timeout' => 15, 'header' => "User-Agent: ShelbySSH\r\n"]]);
            $raw = (string) @file_get_contents(trim($_POST['link']), false, $ctx);
            if ($raw === '' && function_exists('curl_init')) { $ch = curl_init(trim($_POST['link'])); curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 15]); $raw = (string) curl_exec($ch); curl_close($ch); }
        } else {
            $raw = (string) ($_POST['json'] ?? '');
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', trim($raw));
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $msg = 'JSON inválido: ' . json_last_error_msg() . '. Nada foi publicado — confira vírgulas e aspas.'; $msgType = 'err'; $keepRaw = $raw;
        } elseif (!isset($data['Networks']) || !is_array($data['Networks'])) {
            $msg = 'Falta a lista "Networks". Nada foi publicado.'; $msgType = 'err'; $keepRaw = $raw;
        } else {
            if ($_POST['shelby_action'] === 'save' && trim($_POST['versao'] ?? '') !== '') {
                $data['Version'] = preg_replace('/[^0-9.]/', '', $_POST['versao']);
            }
            shelby_bak($jsonFile, $bakDir);
            $out = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $tmp = $jsonFile . '.tmp';
            if (@file_put_contents($tmp, $out) !== false && @rename($tmp, $jsonFile)) {
                @chmod($jsonFile, 0644);
                $msg = 'Publicado! Versão ' . htmlspecialchars($data['Version'] ?? '?') . ' com ' . count($data['Networks']) . ' conexões. Os clientes recebem o aviso de atualização ao abrir o app.';
            } else {
                $msg = 'Sem permissão para gravar o update.json na VPS.'; $msgType = 'err'; $keepRaw = $raw;
            }
        }
    }
}

$current = is_file($jsonFile) ? file_get_contents($jsonFile) : '';
$cur = json_decode($current, true);
$pretty = is_array($cur) ? json_encode($cur, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
$curVer = is_array($cur) ? (string) ($cur['Version'] ?? '1') : '0';
$curNets = is_array($cur) && isset($cur['Networks']) ? count($cur['Networks']) : 0;
$curSrv = is_array($cur) && isset($cur['Servers']) ? count($cur['Servers']) : 0;
$nextVer = ctype_digit($curVer) ? (string) ((int) $curVer + 1) : $curVer;
$backups = glob($bakDir . '/update-*.json') ?: []; rsort($backups);
$updated = is_file($jsonFile) ? date('d/m/Y H:i', filemtime($jsonFile)) : '—';
?>
<style>
#shelbyUp{--c:#00e5ff;--v:#8b5cf6;--g:#ffc94d;--mut:#8ea0c4}
#shelbyUp .box{background:linear-gradient(180deg,rgba(13,24,52,.95),rgba(7,13,30,.97));border:1px solid rgba(0,229,255,.2);border-radius:20px;padding:20px;margin-bottom:18px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
#shelbyUp h3{font-family:Orbitron,Inter,sans-serif;font-size:19px;margin:0 0 4px;background:linear-gradient(90deg,var(--g),#fff3c4,var(--c));-webkit-background-clip:text;color:transparent}
#shelbyUp .mut{color:var(--mut);font-size:13px}
#shelbyUp .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:14px}
#shelbyUp .stats div{background:rgba(6,11,26,.6);border:1px solid rgba(0,229,255,.15);border-radius:14px;padding:10px;text-align:center}
#shelbyUp .stats b{display:block;font-family:Orbitron,sans-serif;color:var(--c);font-size:18px}
#shelbyUp .stats span{font-size:11px;color:var(--mut)}
#shelbyUp .step{display:flex;align-items:center;gap:10px;font-weight:700;color:#e6edfb;margin-bottom:10px}
#shelbyUp .step i{font-style:normal;width:26px;height:26px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,var(--c),var(--v));color:#fff;font-size:13px}
#shelbyUp textarea{width:100%;min-height:360px;background:#050a18!important;color:#d8f6ff!important;border:1px solid rgba(0,229,255,.25)!important;border-radius:14px!important;font:12.5px/1.5 ui-monospace,Menlo,Consolas,monospace;padding:14px;resize:vertical}
#shelbyUp .row2{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
#shelbyUp .ver{display:flex;align-items:center;gap:8px;background:rgba(6,11,26,.6);border:1px solid rgba(255,201,77,.35);border-radius:14px;padding:6px 8px}
#shelbyUp .ver input{width:90px;text-align:center;font-family:Orbitron,sans-serif;font-size:20px;background:transparent!important;border:0!important;color:var(--g)!important;box-shadow:none!important}
#shelbyUp .ver button{width:34px;height:34px;border-radius:10px;border:1px solid rgba(255,201,77,.4);background:rgba(255,201,77,.1);color:var(--g);font-size:18px;line-height:1}
#shelbyUp .bt{border:0;border-radius:12px;padding:11px 16px;font-weight:800;cursor:pointer}
#shelbyUp .bt-pub{background:linear-gradient(90deg,var(--g),#ffe08a,#ff9f1c);color:#1a1100;box-shadow:0 8px 22px rgba(255,170,40,.25);flex:1;min-width:180px;font-size:15px}
#shelbyUp .bt-sec{background:rgba(0,229,255,.1);color:var(--c);border:1px solid rgba(0,229,255,.3)}
#shelbyUp .msg{border-radius:14px;padding:12px 14px;margin-top:12px;font-weight:600}
#shelbyUp .msg.ok{background:rgba(34,229,139,.12);border:1px solid rgba(34,229,139,.4);color:#7ef5bf}
#shelbyUp .msg.err{background:rgba(255,93,122,.12);border:1px solid rgba(255,93,122,.4);color:#ff9fb1}
#shelbyUp #live{font-size:13px;margin-top:8px}
#shelbyUp select{background:#050a18!important;color:#e6edfb!important;border:1px solid rgba(0,229,255,.25)!important;border-radius:12px!important;padding:9px;max-width:100%}
@media(max-width:600px){#shelbyUp .stats{grid-template-columns:repeat(2,1fr)}}
</style>

<div id="shelbyUp">
  <div class="box">
    <h3>⚡ Atualizar App SHELBY NET</h3>
    <div class="mut">Cole o update.json completo, ajuste a versão e publique. O app lê: <a href="/update.json?v=<?= time() ?>" target="_blank"><?= htmlspecialchars($publicUrl) ?></a></div>
    <div class="stats">
      <div><b><?= htmlspecialchars($curVer) ?></b><span>Versão no ar</span></div>
      <div><b><?= $curNets ?></b><span>Conexões</span></div>
      <div><b><?= $curSrv ?></b><span>Servidores</span></div>
      <div><b style="font-size:13px;line-height:22px"><?= $updated ?></b><span>Última publicação</span></div>
    </div>
    <?php if ($msg): ?><div class="msg <?= $msgType ?>"><?= $msg ?></div><?php endif; ?>
  </div>

  <form method="post" class="box" id="fPub">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <input type="hidden" name="shelby_action" value="save">
    <div class="step"><i>1</i> Cole a payload completa</div>
    <textarea name="json" id="js" spellcheck="false" placeholder='{ "Version": "1", ... "Networks": [ ... ] }'><?= htmlspecialchars($keepRaw ?? $pretty) ?></textarea>
    <div id="live" class="mut"></div>
    <div class="row2" style="margin-top:10px">
      <label class="bt bt-sec" style="margin:0">📁 Escolher arquivo .json<input type="file" id="fArq" accept=".json,application/json,text/plain" style="display:none"></label>
      <input type="url" name="link" id="lnk" placeholder="ou cole um link https://...json" style="flex:1;min-width:200px;background:#050a18;color:#e6edfb;border:1px solid rgba(0,229,255,.25);border-radius:12px;padding:10px">
    </div>
    <div class="mut" style="font-size:12px;margin-top:6px">Dica: se copiar o texto pelo celular sumir as aspas, use o arquivo ou o link.</div>
    <div class="row2" style="margin-top:10px">
      <button type="button" class="bt bt-sec" id="bClear">Limpar</button>
      <button type="button" class="bt bt-sec" id="bPaste">Colar</button>
      <button type="button" class="bt bt-sec" id="bFmt">Organizar</button>
      <button type="button" class="bt bt-sec" id="bCopy">Copiar</button>
    </div>
    <div class="step" style="margin-top:18px"><i>2</i> Versão</div>
    <div class="row2">
      <div class="ver"><button type="button" id="vm">−</button><input name="versao" id="ver" value="<?= htmlspecialchars($nextVer) ?>" inputmode="numeric"><button type="button" id="vp">+</button></div>
      <span class="mut">Versão maior que a do ar = clientes recebem o aviso.</span>
    </div>
    <div class="step" style="margin-top:18px"><i>3</i> Publicar</div>
    <button type="submit" class="bt bt-pub">🚀 Publicar atualização</button>
  </form>

  <?php if ($backups): ?>
  <form method="post" class="box" onsubmit="return confirm('Voltar para este backup?')">
    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="shelby_action" value="restore">
    <div class="step"><i>↺</i> Voltar uma versão anterior</div>
    <div class="row2">
      <select name="backup"><?php foreach ($backups as $b): $n = basename($b); $t = preg_replace('/update-(\d{4})(\d\d)(\d\d)-(\d\d)(\d\d)\d\d\.json/', '$3/$2/$1 $4:$5', $n); $bj = json_decode(@file_get_contents($b), true); ?>
        <option value="<?= htmlspecialchars($n) ?>"><?= $t ?> — v<?= htmlspecialchars($bj['Version'] ?? '?') ?> (<?= isset($bj['Networks']) ? count($bj['Networks']) : 0 ?> conexões)</option>
      <?php endforeach; ?></select>
      <button class="bt bt-sec" type="submit">Restaurar</button>
    </div>
  </form>
  <?php endif; ?>
</div>
<script>
(function(){
  var js=document.getElementById('js'),live=document.getElementById('live'),ver=document.getElementById('ver');
  function check(){
    var t=js.value.trim(); if(!t){live.innerHTML='Cole o JSON aqui.';return null;}
    try{var d=JSON.parse(t);var n=(d.Networks||[]).length,s=(d.Servers||[]).length;
      live.innerHTML='<span style="color:#7ef5bf">✔ JSON válido</span> · '+n+' conexões · '+s+' servidor(es) · versão no texto: '+(d.Version||'?');return d;}
    catch(e){live.innerHTML='<span style="color:#ff9fb1">✖ JSON com erro: '+e.message+'</span>';return null;}
  }
  js.addEventListener('input',check); check();
  document.getElementById('fArq').onchange=function(){var f=this.files[0];if(!f)return;var r=new FileReader();r.onload=function(){js.value=String(r.result).replace(/^\uFEFF/,'');check();try{var d=JSON.parse(js.value);if(d.Version)ver.value=d.Version;}catch(e){}};r.readAsText(f,'utf-8');};
  document.getElementById('bClear').onclick=function(){js.value='';check();js.focus();};
  document.getElementById('bPaste').onclick=function(){if(navigator.clipboard&&navigator.clipboard.readText){navigator.clipboard.readText().then(function(t){js.value=t;check();});}else{js.focus();}};
  document.getElementById('bFmt').onclick=function(){var d=check();if(d)js.value=JSON.stringify(d,null,2);};
  document.getElementById('bCopy').onclick=function(){js.select();try{navigator.clipboard.writeText(js.value);}catch(e){document.execCommand('copy');}};
  document.getElementById('vp').onclick=function(){ver.value=(parseInt(ver.value||'0',10)+1);};
  document.getElementById('vm').onclick=function(){ver.value=Math.max(1,parseInt(ver.value||'1',10)-1);};
  document.getElementById('fPub').onsubmit=function(e){var L=document.getElementById('lnk').value.trim();if(!js.value.trim()&&L){return confirm('Importar do link e publicar versão '+ver.value+'?');}if(!check()){e.preventDefault();alert('O JSON tem erro. Corrija antes de publicar.');return false;}return confirm('Publicar versão '+ver.value+'?');};
})();
</script>
