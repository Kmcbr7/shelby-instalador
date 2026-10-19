<?php
// Shelby SSH - Painel Shelby: edita o update.json lido pelo app SHELBY NET
if (basename($_SERVER["SCRIPT_FILENAME"] ?? '') === basename(__FILE__)) { header('Location: ../../index.php?e=404'); exit; }
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

$webRoot  = realpath(__DIR__ . '/../../..');
$jsonFile = $webRoot . '/update.json';
$bakDir   = $webRoot . '/config/payload-backups';
$host     = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? '');
$publicUrl = 'https://' . preg_replace('/:\d+$/', '', $host) . '/update.json';

if (empty($_SESSION['shelby_csrf'])) { $_SESSION['shelby_csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['shelby_csrf'];
$msg = ''; $msgType = 'success';

function shelby_bak($file, $dir) {
    if (!is_file($file)) return;
    @mkdir($dir, 0750, true);
    @copy($file, $dir . '/update-' . date('Ymd-His') . '.json');
    $all = glob($dir . '/update-*.json') ?: [];
    rsort($all);
    foreach (array_slice($all, 15) as $old) @unlink($old);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shelby_action'])) {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $msg = 'Sessão expirada. Recarregue a página.'; $msgType = 'danger';
    } else {
        $raw = '';
        if ($_POST['shelby_action'] === 'upload' && !empty($_FILES['arquivo']['tmp_name'])) {
            $raw = (string) file_get_contents($_FILES['arquivo']['tmp_name']);
        } elseif ($_POST['shelby_action'] === 'restore') {
            $b = basename($_POST['backup'] ?? '');
            if (preg_match('/^update-\d{8}-\d{6}\.json$/', $b) && is_file("$bakDir/$b")) $raw = file_get_contents("$bakDir/$b");
        } else {
            $raw = (string) ($_POST['json'] ?? '');
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $msg = 'JSON inválido: ' . json_last_error_msg() . '. Nada foi salvo.'; $msgType = 'danger';
        } elseif (!isset($data['Networks']) || !is_array($data['Networks'])) {
            $msg = 'O arquivo precisa ter a lista "Networks". Nada foi salvo.'; $msgType = 'danger';
        } else {
            if (!empty($_POST['bump']) && $_POST['shelby_action'] === 'save') {
                $data['Version'] = (string) ((int) ($data['Version'] ?? 0) + 1);
            }
            shelby_bak($jsonFile, $bakDir);
            $out = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $tmp = $jsonFile . '.tmp';
            if (@file_put_contents($tmp, $out) !== false && @rename($tmp, $jsonFile)) {
                @chmod($jsonFile, 0644);
                $msg = 'Salvo! Versão ' . htmlspecialchars($data['Version'] ?? '?') . ' com ' . count($data['Networks']) . ' redes. O app baixa na próxima abertura.';
            } else {
                $msg = 'Sem permissão para gravar ' . htmlspecialchars($jsonFile) . '. Rode o comando de correção na VPS.'; $msgType = 'danger';
            }
        }
    }
}

$current = is_file($jsonFile) ? file_get_contents($jsonFile) : '';
$cur = json_decode($current, true);
if (!is_array($cur)) {
    $cur = ['Version' => '1', 'ReleaseNotes' => '', 'UrlUpdate' => $publicUrl, 'EmailFeedback' => '', 'UrlContato' => '', 'UrlTermos' => '', 'CheckUser' => 'true', 'Udp' => [], 'Servers' => [], 'Networks' => []];
}
$backups = glob($bakDir . '/update-*.json') ?: []; rsort($backups);
$jsonAttr = htmlspecialchars(json_encode($cur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES);
?>
<style>
.shelby-net .card{background:#0f1a33;border:1px solid rgba(0,229,255,.25);box-shadow:0 0 18px rgba(0,229,255,.08)}
.shelby-net .card-title{color:#ffd166}
.shelby-net .table td{vertical-align:top}
.shelby-net textarea.pl{min-width:260px;font-family:monospace;font-size:12px}
.shelby-net .net-row input,.shelby-net .net-row textarea{font-size:12px}
.shelby-net code{color:#00e5ff}
</style>
<div class="shelby-net" id="shelbyNet" data-json="<?= $jsonAttr ?>">
  <div class="card"><div class="card-body">
    <h4 class="card-title">⚡ Painel Shelby — Payloads do app SHELBY NET</h4>
    <p class="mb-50">Link que o app lê: <code><?= htmlspecialchars($publicUrl) ?></code>
      <a class="btn btn-sm btn-outline-info ms-50" target="_blank" href="/update.json?v=<?= time() ?>">Abrir</a></p>
    <small>Toda vez que salvar com “subir versão” marcado, o app avisa o cliente que tem atualização.</small>
    <?php if ($msg): ?><div class="alert alert-<?= $msgType ?> mt-1 p-1"><?= $msg ?></div><?php endif; ?>
  </div></div>

  <form method="post" id="formSave">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <input type="hidden" name="shelby_action" value="save">
    <input type="hidden" name="json" id="jsonOut">

    <div class="card"><div class="card-body">
      <h5 class="card-title">Geral</h5>
      <div class="row">
        <div class="col-md-2 mb-1"><label>Versão</label><input class="form-control" data-k="Version"></div>
        <div class="col-md-4 mb-1"><label>WhatsApp / contato</label><input class="form-control" data-k="UrlContato"></div>
        <div class="col-md-3 mb-1"><label>Termos (URL)</label><input class="form-control" data-k="UrlTermos"></div>
        <div class="col-md-3 mb-1"><label>E-mail feedback</label><input class="form-control" data-k="EmailFeedback"></div>
        <div class="col-md-6 mb-1"><label>URL de atualização</label><input class="form-control" data-k="UrlUpdate"></div>
        <div class="col-md-2 mb-1"><label>CheckUser</label><select class="form-select" data-k="CheckUser"><option>true</option><option>false</option></select></div>
        <div class="col-md-4 mb-1"><label>Portas UDP (separe por vírgula)</label><input class="form-control" id="udp"></div>
        <div class="col-12"><label>Notas da atualização (aparece no app)</label><textarea class="form-control" rows="5" data-k="ReleaseNotes"></textarea></div>
      </div>
    </div></div>

    <div class="card"><div class="card-body">
      <h5 class="card-title">Servidores <button type="button" class="btn btn-sm btn-outline-warning ms-1" id="addSrv">+ servidor</button></h5>
      <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Nome</th><th>IP / domínio</th><th>Porta SSH</th><th>Porta SSL</th><th>CheckUser</th><th>Bandeira</th><th></th></tr></thead><tbody id="srvBody"></tbody></table></div>
    </div></div>

    <div class="card"><div class="card-body">
      <h5 class="card-title">Redes / payloads (<span id="netCount">0</span>)
        <button type="button" class="btn btn-sm btn-outline-warning ms-1" id="addNet">+ rede</button></h5>
      <input class="form-control mb-1" id="netFilter" placeholder="Filtrar por nome (ex.: TIM, VIVO, CLARO)">
      <div class="table-responsive"><table class="table table-sm"><thead><tr><th>#</th><th>Nome</th><th>Payload</th><th>SNI / TLS IP</th><th>Proxy : porta</th><th>Info / ícone</th><th></th></tr></thead><tbody id="netBody"></tbody></table></div>
    </div></div>

    <div class="card"><div class="card-body d-flex flex-wrap align-items-center gap-1">
      <div class="form-check me-2"><input class="form-check-input" type="checkbox" name="bump" value="1" id="bump" checked><label class="form-check-label" for="bump">Subir versão (+1) ao salvar</label></div>
      <button class="btn btn-warning" type="submit">💾 Salvar e publicar</button>
      <button class="btn btn-outline-info" type="button" id="dl">⬇️ Baixar JSON</button>
      <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawBox">{ } Editar JSON bruto</button>
    </div>
    <div class="collapse" id="rawBox"><div class="card"><div class="card-body">
      <textarea class="form-control" id="raw" rows="18" style="font-family:monospace;font-size:12px"></textarea>
      <button class="btn btn-sm btn-outline-warning mt-1" type="button" id="applyRaw">Aplicar JSON bruto nos campos</button>
    </div></div></div>
  </form>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Importar / restaurar</h5>
    <form method="post" enctype="multipart/form-data" class="d-flex flex-wrap gap-1 mb-1">
      <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="shelby_action" value="upload">
      <input class="form-control" style="max-width:320px" type="file" name="arquivo" accept=".json,application/json" required>
      <button class="btn btn-outline-warning" type="submit">Enviar update.json</button>
    </form>
    <?php if ($backups): ?>
    <form method="post" class="d-flex flex-wrap gap-1">
      <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="shelby_action" value="restore">
      <select class="form-select" style="max-width:320px" name="backup">
        <?php foreach ($backups as $b): $n = basename($b); ?><option value="<?= htmlspecialchars($n) ?>"><?= htmlspecialchars($n) ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-outline-secondary" type="submit" onclick="return confirm('Restaurar este backup?')">Restaurar backup</button>
    </form>
    <?php endif; ?>
  </div></div>
</div>

<script>
(function(){
  var box=document.getElementById('shelbyNet'); var D=JSON.parse(box.dataset.json||'{}');
  D.Servers=D.Servers||[]; D.Networks=D.Networks||[]; D.Udp=D.Udp||[];
  function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');}
  function inp(arr,i,k,cls){return '<input class="form-control form-control-sm '+(cls||'')+'" data-a="'+arr+'" data-i="'+i+'" data-f="'+k+'" value="'+esc(D[arr][i][k])+'">';}
  function renderGeneral(){
    box.querySelectorAll('[data-k]').forEach(function(el){el.value=D[el.dataset.k]==null?'':D[el.dataset.k];});
    document.getElementById('udp').value=(D.Udp||[]).map(function(u){return u.Porta;}).join(', ');
  }
  function renderSrv(){
    document.getElementById('srvBody').innerHTML=D.Servers.map(function(s,i){
      return '<tr><td>'+inp('Servers',i,'Name')+'</td><td>'+inp('Servers',i,'ServerIP')+'</td><td>'+inp('Servers',i,'ServerPort')+'</td><td>'+inp('Servers',i,'SSLPort')+'</td><td>'+inp('Servers',i,'CheckUser')+'</td><td>'+inp('Servers',i,'FLAG')+'</td><td><button type="button" class="btn btn-sm btn-outline-danger" data-del="Servers" data-i="'+i+'">✕</button></td></tr>';
    }).join('');
  }
  function renderNet(){
    var f=(document.getElementById('netFilter').value||'').toLowerCase();
    document.getElementById('netCount').textContent=D.Networks.length;
    document.getElementById('netBody').innerHTML=D.Networks.map(function(n,i){
      if(f && String(n.Name||'').toLowerCase().indexOf(f)<0) return '';
      return '<tr class="net-row"><td>'+(i+1)+'<br><button type="button" class="btn btn-sm p-0" data-mv="-1" data-i="'+i+'">▲</button><button type="button" class="btn btn-sm p-0" data-mv="1" data-i="'+i+'">▼</button></td>'+
        '<td>'+inp('Networks',i,'Name')+'</td>'+
        '<td><textarea class="form-control pl" rows="3" data-a="Networks" data-i="'+i+'" data-f="Payload">'+esc(n.Payload)+'</textarea></td>'+
        '<td>'+inp('Networks',i,'SNI')+inp('Networks',i,'TlsIP','mt-25')+'</td>'+
        '<td>'+inp('Networks',i,'ProxyIP')+inp('Networks',i,'ProxyPort','mt-25')+'</td>'+
        '<td>'+inp('Networks',i,'Info')+inp('Networks',i,'FLAG','mt-25')+'</td>'+
        '<td><button type="button" class="btn btn-sm btn-outline-info mb-25" data-dup="'+i+'">⧉</button><br><button type="button" class="btn btn-sm btn-outline-danger" data-del="Networks" data-i="'+i+'">✕</button></td></tr>';
    }).join('');
  }
  function renderAll(){renderGeneral();renderSrv();renderNet();}
  function collect(){
    box.querySelectorAll('[data-k]').forEach(function(el){D[el.dataset.k]=el.value;});
    D.Udp=document.getElementById('udp').value.split(',').map(function(p){return p.trim();}).filter(Boolean).map(function(p){return {Porta:p};});
    return D;
  }
  box.addEventListener('input',function(e){var t=e.target; if(t.dataset.a){D[t.dataset.a][+t.dataset.i][t.dataset.f]=t.value;}});
  box.addEventListener('click',function(e){
    var t=e.target.closest('button'); if(!t) return;
    if(t.dataset.del){ if(confirm('Remover?')){D[t.dataset.del].splice(+t.dataset.i,1); renderSrv(); renderNet();} }
    else if(t.dataset.dup){var i=+t.dataset.dup; var c=JSON.parse(JSON.stringify(D.Networks[i])); c.Name=(c.Name||'')+' (cópia)'; D.Networks.splice(i+1,0,c); renderNet();}
    else if(t.dataset.mv){var i=+t.dataset.i, j=i+(+t.dataset.mv); if(j>=0&&j<D.Networks.length){var x=D.Networks[i];D.Networks[i]=D.Networks[j];D.Networks[j]=x;renderNet();}}
  });
  document.getElementById('netFilter').addEventListener('input',renderNet);
  document.getElementById('addNet').onclick=function(){D.Networks.push({Name:'NOVA REDE',FLAG:'ic_vivo.png',Payload:'',SNI:'',TlsIP:'',ProxyIP:'',ProxyPort:'80',Info:'Proxy'});document.getElementById('netFilter').value='';renderNet();};
  document.getElementById('addSrv').onclick=function(){D.Servers.push({Name:'SERVIDOR - '+String(D.Servers.length+1).padStart(2,'0'),TYPE:'premium',FLAG:'br.png',ServerIP:'',CheckUser:'',ServerPort:'22',SSLPort:'443',USER:'',PASS:''});renderSrv();};
  document.querySelector('[data-bs-target="#rawBox"]').addEventListener('click',function(){document.getElementById('raw').value=JSON.stringify(collect(),null,2);});
  document.getElementById('applyRaw').onclick=function(){try{D=JSON.parse(document.getElementById('raw').value);D.Servers=D.Servers||[];D.Networks=D.Networks||[];renderAll();alert('Aplicado. Agora clique em Salvar.');}catch(err){alert('JSON inválido: '+err.message);}};
  document.getElementById('dl').onclick=function(){var b=new Blob([JSON.stringify(collect(),null,2)],{type:'application/json'});var a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='update.json';a.click();};
  document.getElementById('formSave').addEventListener('submit',function(){document.getElementById('jsonOut').value=JSON.stringify(collect());});
  renderAll();
})();
</script>
