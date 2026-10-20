<?php
// Shelby SSH - Upload de Apps (visual premium)
$apkDir = realpath(__DIR__ . '/../../apps/apk') ?: (__DIR__ . '/../../apps/apk');
$meses = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
$slots = [];
foreach ([1,2,3] as $i) {
    $f = $apkDir . '/app_s' . $i . '.apk';
    $slots[$i] = is_file($f)
        ? ['on' => true, 'mb' => number_format(filesize($f)/1048576,1,',','.'),
           'data' => date('d',filemtime($f)).' '.$meses[(int)date('n',filemtime($f))-1].' '.date('Y',filemtime($f)),
           'url' => '../apps/apk/app_s'.$i.'.apk?v='.filemtime($f)]
        : ['on' => false];
}
// limite de upload efetivo (pra avisar o usuário)
function shelby_bytes($v){$v=trim($v);$u=strtolower(substr($v,-1));$n=(int)$v;return $u=='g'?$n*1073741824:($u=='m'?$n*1048576:($u=='k'?$n*1024:$n));}
$lim = min(shelby_bytes(ini_get('upload_max_filesize')), shelby_bytes(ini_get('post_max_size')));
$limMb = round($lim/1048576);
?>
<style>
.shelby-up{--navy:#0b1630;--glass:rgba(14,26,56,.6);--line:rgba(0,229,255,.2);--cyan:#00e5ff;--gold:#ffc94d;--gold2:#ff9f1c;--violet:#8b5cf6;--mut:#93a4c8}
.shelby-up{display:block;border-radius:24px;padding:20px;background:radial-gradient(600px 300px at 0% 0%,rgba(0,229,255,.16),transparent 60%),radial-gradient(500px 300px at 100% 0%,rgba(139,92,246,.18),transparent 60%),#070d1f;box-shadow:0 10px 40px rgba(0,0,0,.25);border:1px solid rgba(0,229,255,.18)}
.shelby-up *{box-sizing:border-box}
.shelby-up form,.shelby-up .drop,.shelby-up .drop span,.shelby-up .drop b,.shelby-up .btn{display:block}
.shelby-up .drop svg{display:block;margin:0 auto 6px}
.shelby-up .head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}
.shelby-up .head h4{font-weight:800;background:linear-gradient(90deg,var(--gold),#fff,var(--cyan));-webkit-background-clip:text;color:transparent;margin:0}
.shelby-up .toloja{border:1px solid var(--line);background:rgba(0,229,255,.08);color:#dbe7ff;padding:8px 14px;border-radius:999px;font-weight:600;font-size:13px;text-decoration:none}
.shelby-up .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.shelby-up .slot{border-radius:20px;padding:1px;background:linear-gradient(140deg,rgba(0,229,255,.5),rgba(139,92,246,.25),rgba(255,201,77,.5))}
.shelby-up .slot .body{border-radius:19px;background:linear-gradient(180deg,rgba(13,24,52,.95),rgba(7,13,30,.97));padding:18px}
.shelby-up .slot h5{font-weight:800;color:#eaf1ff;display:flex;align-items:center;gap:8px;margin-bottom:4px}
.shelby-up .n{width:26px;height:26px;border-radius:8px;display:grid;place-items:center;font-size:13px;font-weight:800;color:#fff;background:linear-gradient(135deg,var(--cyan),var(--violet))}
.shelby-up .status{font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;margin-left:auto}
.shelby-up .live{color:#06240f;background:linear-gradient(90deg,#22e58b,#7ef5bf)}
.shelby-up .off{color:#93a4c8;border:1px solid rgba(255,255,255,.14)}
.shelby-up .cur{font-size:12px;color:var(--mut);margin:6px 0 12px}
.shelby-up .cur a{color:var(--cyan)}
.shelby-up .drop{border:1.5px dashed var(--line);border-radius:14px;padding:18px;text-align:center;color:var(--mut);cursor:pointer;transition:.2s;background:rgba(255,255,255,.02)}
.shelby-up .drop:hover,.shelby-up .drop.drag{border-color:var(--cyan);background:rgba(0,229,255,.08);color:#dbe7ff}
.shelby-up .drop svg{width:30px;height:30px;stroke:var(--cyan);margin-bottom:6px}
.shelby-up .drop b{display:block;color:#eaf1ff}
.shelby-up .fname{font-size:12px;color:var(--cyan);margin-top:8px;word-break:break-all;min-height:16px}
.shelby-up input[type=file]{display:none}
.shelby-up .bar{height:6px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden;margin-top:10px;display:none}
.shelby-up .bar i{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--cyan),var(--violet))}
.shelby-up .btn{margin-top:12px;width:100%;border:0;padding:13px;border-radius:12px;font-weight:800;letter-spacing:.3px;color:#140d00;cursor:pointer;background:linear-gradient(90deg,var(--gold),#ffe08a,var(--gold2));background-size:200% 100%;transition:.3s}
.shelby-up .btn:hover{background-position:100% 0}
.shelby-up .btn:disabled{opacity:.5;cursor:not-allowed}
.shelby-up .hint{margin-top:14px;font-size:12px;color:var(--mut);text-align:center}
.shelby-up .hint b{color:var(--gold)}
</style>
<div class="shelby-up">
  <div class="head">
    <h4>⚡ Upload de Apps</h4>
    <a class="toloja" href="../apps" target="_blank">Ver a loja de apps →</a>
  </div>
  <div class="grid">
  <?php foreach ($slots as $i => $s): ?>
    <div class="slot"><div class="body">
      <h5><span class="n"><?= $i ?></span> App <?= $i ?>
        <span class="status <?= $s['on'] ? 'live' : 'off' ?>"><?= $s['on'] ? 'PUBLICADO' : 'VAZIO' ?></span></h5>
      <div class="cur">
        <?php if ($s['on']): ?>Atual: <?= $s['mb'] ?> MB · <?= $s['data'] ?> · <a href="<?= htmlspecialchars($s['url']) ?>" target="_blank">baixar</a><?php else: ?>Nenhum APK enviado ainda.<?php endif; ?>
      </div>
      <form method="post" enctype="multipart/form-data" action="recebeUpload<?= $i == 1 ? '' : $i ?>.php" class="upform">
        <label class="drop">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M12 16V4m0 0L7 9m5-5l5 5M4 20h16"/></svg>
          <b>Toque para escolher o APK</b>
          <span>ou arraste o arquivo aqui</span>
          <input name="arquivo" type="file" accept=".apk,application/vnd.android.package-archive" required>
          <div class="fname"></div>
        </label>
        <div class="bar"><i></i></div>
        <button class="btn" type="submit" disabled><?= $s['on'] ? 'Substituir APK' : 'Enviar APK' ?></button>
      </form>
    </div></div>
  <?php endforeach; ?>
  </div>
  <p class="hint">Limite por arquivo: <b><?= $limMb ?> MB</b>. Depois de enviar, o app aparece na loja automaticamente.</p>
</div>
<script>
document.querySelectorAll('.shelby-up .upform').forEach(function(f){
  var inp=f.querySelector('input[type=file]'), drop=f.querySelector('.drop'),
      name=f.querySelector('.fname'), btn=f.querySelector('.btn'),
      bar=f.querySelector('.bar'), fill=f.querySelector('.bar i'), LIM=<?= $lim ?>;
  function pick(file){
    if(!file) return;
    if(!/\.apk$/i.test(file.name)){name.textContent='Envie um arquivo .apk';btn.disabled=true;return;}
    if(file.size>LIM){name.textContent='Arquivo grande demais ('+(file.size/1048576).toFixed(1)+' MB). Limite: <?= $limMb ?> MB.';btn.disabled=true;return;}
    name.textContent='✓ '+file.name+' ('+(file.size/1048576).toFixed(1)+' MB)';btn.disabled=false;
  }
  inp.addEventListener('change',function(){pick(inp.files[0]);});
  ['dragover','dragenter'].forEach(function(e){drop.addEventListener(e,function(ev){ev.preventDefault();drop.classList.add('drag');});});
  ['dragleave','drop'].forEach(function(e){drop.addEventListener(e,function(ev){ev.preventDefault();drop.classList.remove('drag');});});
  drop.addEventListener('drop',function(ev){if(ev.dataTransfer.files.length){inp.files=ev.dataTransfer.files;pick(inp.files[0]);}});
  f.addEventListener('submit',function(ev){
    if(!window.XMLHttpRequest||!inp.files[0]) return;
    ev.preventDefault();btn.disabled=true;btn.textContent='Enviando...';bar.style.display='block';
    var xhr=new XMLHttpRequest(),fd=new FormData(f);
    xhr.upload.onprogress=function(e){if(e.lengthComputable)fill.style.width=(e.loaded/e.total*100)+'%';};
    xhr.onload=function(){document.open();document.write(xhr.responseText);document.close();};
    xhr.onerror=function(){btn.textContent='Erro de conexão';btn.disabled=false;};
    xhr.open('POST',f.action);xhr.send(fd);
  });
});
</script>
