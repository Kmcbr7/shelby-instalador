<?php
// Shelby SSH - Loja de Apps (visual premium)
$WHATS = '5562998001820';
$apps = [
  ['slot' => 1, 'nome' => 'SHELBY NET',    'tag' => 'APP-SHELBY-V1', 'servidor' => 'BR-1', 'img' => 'img/shelby-net.png', 'destaque' => true],
  ['slot' => 2, 'nome' => 'SHELBY NET 2',  'tag' => 'APP-SHELBY-V2', 'servidor' => 'BR-2', 'img' => 'img/app-vpn-02.png', 'destaque' => false],
  ['slot' => 3, 'nome' => 'SHELBY NET 3',  'tag' => 'APP-SHELBY-V3', 'servidor' => 'BR-3', 'img' => 'img/app-vpn-03.png', 'destaque' => false],
];
$meses = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
$lista = [];
foreach ($apps as $a) {
  $f = __DIR__ . '/apk/app_s' . $a['slot'] . '.apk';
  if (!is_file($f)) continue;
  $a['url']  = './apk/app_s' . $a['slot'] . '.apk?v=' . filemtime($f);
  $a['mb']   = number_format(filesize($f) / 1048576, 1, ',', '');
  $t = filemtime($f);
  $a['data'] = date('d', $t) . ' ' . $meses[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
  $lista[] = $a;
}
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Apps Shelby SSH · Internet ilimitada</title>
<meta name="description" content="Baixe o app Shelby e navegue sem limites na Vivo, Claro, TIM e Oi.">
<meta name="theme-color" content="#060b1a">
<link rel="icon" href="img/shelby-logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#060b1a;--navy:#0b1630;--glass:rgba(14,26,56,.55);--line:rgba(0,229,255,.18);--cyan:#00e5ff;--gold:#ffc94d;--gold2:#ff9f1c;--violet:#8b5cf6;--txt:#e8eefc;--mut:#8ea0c4}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:Inter,system-ui,sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;overflow-x:hidden}
a{color:inherit;text-decoration:none}
/* fundo */
.bg{position:fixed;inset:0;z-index:-2;background:
 radial-gradient(900px 500px at 15% -10%,rgba(0,229,255,.18),transparent 60%),
 radial-gradient(800px 600px at 110% 20%,rgba(139,92,246,.20),transparent 60%),
 radial-gradient(700px 500px at 50% 120%,rgba(255,201,77,.12),transparent 60%),var(--bg)}
.grid{position:fixed;inset:0;z-index:-1;opacity:.35;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:44px 44px;mask-image:radial-gradient(ellipse at 50% 0%,#000 30%,transparent 75%);animation:grid 18s linear infinite}
@keyframes grid{to{background-position:0 44px,44px 0}}
canvas#stars{position:fixed;inset:0;z-index:-1;pointer-events:none}
.wrap{max-width:1100px;margin:0 auto;padding:0 18px}
/* topo */
nav{display:flex;align-items:center;justify-content:space-between;padding:16px 0}
.brand{display:flex;align-items:center;gap:10px;font-family:Orbitron;font-weight:800;letter-spacing:1px}
.brand img{width:40px;height:40px;border-radius:50%;box-shadow:0 0 18px rgba(0,229,255,.45)}
.brand b{background:linear-gradient(90deg,var(--gold),#fff,var(--cyan));-webkit-background-clip:text;color:transparent}
.pill{border:1px solid var(--line);background:var(--glass);backdrop-filter:blur(10px);padding:8px 14px;border-radius:999px;font-size:13px;font-weight:600;display:inline-flex;gap:8px;align-items:center}
.dot{width:8px;height:8px;border-radius:50%;background:#22e58b;box-shadow:0 0 10px #22e58b;animation:pulse 1.6s infinite}
@keyframes pulse{50%{opacity:.35}}
/* hero */
.hero{text-align:center;padding:38px 0 26px}
.hero .logo{width:150px;height:150px;border-radius:50%;margin:0 auto 18px;position:relative}
.hero .logo img{width:100%;height:100%;border-radius:50%;position:relative;z-index:1}
.hero .logo:before{content:"";position:absolute;inset:-6px;border-radius:50%;background:conic-gradient(var(--cyan),var(--violet),var(--gold),var(--cyan));animation:spin 6s linear infinite;filter:blur(1px)}
.hero .logo:after{content:"";position:absolute;inset:-26px;border-radius:50%;background:radial-gradient(circle,rgba(0,229,255,.35),transparent 65%);z-index:-1}
@keyframes spin{to{transform:rotate(360deg)}}
h1{font-family:Orbitron;font-weight:800;font-size:clamp(30px,8vw,58px);line-height:1.05;letter-spacing:1px}
h1 .g{background:linear-gradient(92deg,var(--gold) 0%,#fff3c4 40%,var(--cyan) 100%);-webkit-background-clip:text;color:transparent;filter:drop-shadow(0 0 18px rgba(0,229,255,.35))}
.sub{color:var(--mut);margin:14px auto 22px;max-width:560px;font-size:clamp(15px,3.8vw,18px)}
.ops{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
.op{padding:7px 14px;border-radius:999px;font-weight:700;font-size:13px;border:1px solid;background:rgba(255,255,255,.03)}
.vivo{color:#c084fc;border-color:#a855f766}.claro{color:#ff6b6b;border-color:#ef444466}.tim{color:#60a5fa;border-color:#3b82f666}.oi{color:#fbbf24;border-color:#f59e0b66}
/* stats */
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:28px 0 8px}
.stat{background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:14px 8px;text-align:center;backdrop-filter:blur(10px)}
.stat b{display:block;font-family:Orbitron;font-size:clamp(17px,4.5vw,24px);color:var(--cyan)}
.stat span{font-size:12px;color:var(--mut)}
/* apps */
h2{font-family:Orbitron;font-size:20px;margin:34px 0 14px;display:flex;align-items:center;gap:10px}
h2:after{content:"";flex:1;height:1px;background:linear-gradient(90deg,var(--line),transparent)}
.apps{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:18px}
.card{position:relative;border-radius:22px;padding:1px;background:linear-gradient(140deg,rgba(0,229,255,.55),rgba(139,92,246,.25) 45%,rgba(255,201,77,.55));transition:transform .25s}
.card:hover{transform:translateY(-4px)}
.in{border-radius:21px;background:linear-gradient(180deg,rgba(13,24,52,.92),rgba(7,13,30,.96));padding:20px;height:100%;display:flex;flex-direction:column;gap:16px}
.top{display:flex;gap:14px;align-items:center}
.ico{width:74px;height:74px;border-radius:18px;object-fit:cover;background:#000;box-shadow:0 0 0 1px var(--line),0 8px 24px rgba(0,229,255,.18)}
.top h3{font-family:Orbitron;font-size:18px}
.tagl{font-size:12px;color:var(--cyan);font-weight:600;letter-spacing:.5px}
.badge{position:absolute;top:14px;right:14px;background:linear-gradient(90deg,var(--gold),var(--gold2));color:#1a1200;font-size:11px;font-weight:800;padding:4px 10px;border-radius:999px;box-shadow:0 0 14px rgba(255,201,77,.5)}
.meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.meta div{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:9px 6px;text-align:center}
.meta small{display:block;color:var(--mut);font-size:11px;margin-bottom:2px}
.meta b{font-size:13px}
.btn{display:flex;align-items:center;justify-content:center;gap:10px;padding:15px;border-radius:14px;font-weight:800;font-size:16px;letter-spacing:.5px;color:#140d00;background:linear-gradient(90deg,var(--gold),#ffe08a,var(--gold2));background-size:200% 100%;box-shadow:0 10px 28px rgba(255,170,40,.28);transition:background-position .4s,transform .15s;position:relative;overflow:hidden}
.btn:hover{background-position:100% 0}.btn:active{transform:scale(.98)}
.btn:after{content:"";position:absolute;top:0;left:-60%;width:40%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.6),transparent);transform:skewX(-20deg);animation:shine 3.2s infinite}
@keyframes shine{60%,100%{left:130%}}
.btn svg{width:20px;height:20px}
.empty{text-align:center;padding:30px;color:var(--mut);border:1px dashed var(--line);border-radius:18px}
/* passos */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}
.step{background:var(--glass);border:1px solid var(--line);border-radius:18px;padding:18px;backdrop-filter:blur(10px)}
.step i{font-style:normal;font-family:Orbitron;font-weight:800;width:36px;height:36px;border-radius:10px;display:grid;place-items:center;background:linear-gradient(135deg,var(--cyan),var(--violet));color:#fff;margin-bottom:10px;box-shadow:0 0 16px rgba(0,229,255,.35)}
.step h4{margin-bottom:4px}.step p{color:var(--mut);font-size:14px}
/* rodapé */
.cta{margin:36px 0;text-align:center;padding:26px 18px;border-radius:22px;border:1px solid var(--line);background:linear-gradient(135deg,rgba(0,229,255,.08),rgba(139,92,246,.08))}
.cta p{color:var(--mut);margin:6px 0 16px}
.wa{display:inline-flex;align-items:center;gap:10px;padding:13px 22px;border-radius:999px;background:#22c55e;color:#04210f;font-weight:800;box-shadow:0 0 22px rgba(34,197,94,.45)}
.wa svg{width:20px;height:20px}
footer{text-align:center;color:var(--mut);font-size:13px;padding:10px 0 34px;line-height:1.8}
footer a{color:var(--gold)}
.fab{position:fixed;right:16px;bottom:16px;width:54px;height:54px;border-radius:50%;display:grid;place-items:center;background:#22c55e;box-shadow:0 0 20px rgba(34,197,94,.55);z-index:5}
.fab svg{width:28px;height:28px;fill:#fff}
.reveal{opacity:0;transform:translateY(16px);transition:all .6s ease}.reveal.on{opacity:1;transform:none}
@media(max-width:420px){.stats{gap:6px}.in{padding:16px}}
</style>
</head>
<body>
<div class="bg"></div><div class="grid"></div><canvas id="stars"></canvas>
<div class="wrap">
  <nav>
    <div class="brand"><img src="img/shelby-logo.png" alt="Shelby"><b>SHELBY SSH</b></div>
    <span class="pill"><span class="dot"></span>Servidores online</span>
  </nav>

  <section class="hero reveal">
    <div class="logo"><img src="img/shelby-logo.png" alt="Shelby Systems"></div>
    <h1><span class="g">INTERNET<br>SEM LIMITES</span></h1>
    <p class="sub">Conexão premium 4G/5G com servidores no Brasil. Baixe o app, conecte e navegue à vontade.</p>
    <div class="ops"><span class="op vivo">VIVO</span><span class="op claro">CLARO</span><span class="op tim">TIM</span><span class="op oi">OI</span></div>
    <div class="stats">
      <div class="stat"><b>24/7</b><span>Online</span></div>
      <div class="stat"><b>BR</b><span>Servidores</span></div>
      <div class="stat"><b>SSL</b><span>Criptografado</span></div>
    </div>
  </section>

  <h2 class="reveal">Baixe o app</h2>
  <section class="apps">
  <?php if (!$lista): ?>
    <div class="empty">Nenhum app publicado ainda. Volte em instantes.</div>
  <?php endif; foreach ($lista as $a): ?>
    <div class="card reveal">
      <?php if ($a['destaque']): ?><span class="badge">RECOMENDADO</span><?php endif; ?>
      <div class="in">
        <div class="top">
          <img class="ico" src="<?= $h($a['img']) ?>" alt="<?= $h($a['nome']) ?>">
          <div><div class="tagl"><?= $h($a['tag']) ?></div><h3><?= $h($a['nome']) ?></h3></div>
        </div>
        <div class="meta">
          <div><small>Servidor</small><b><?= $h($a['servidor']) ?></b></div>
          <div><small>Tamanho</small><b><?= $h($a['mb']) ?> MB</b></div>
          <div><small>Atualizado</small><b><?= $h($a['data']) ?></b></div>
        </div>
        <a class="btn" href="<?= $h($a['url']) ?>" download>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 3v12m0 0l-5-5m5 5l5-5M4 20h16"/></svg>BAIXAR APP</a>
      </div>
    </div>
  <?php endforeach; ?>
  </section>

  <h2 class="reveal">Como instalar</h2>
  <section class="steps">
    <div class="step reveal"><i>1</i><h4>Baixe</h4><p>Toque em “Baixar app” e espere o download terminar.</p></div>
    <div class="step reveal"><i>2</i><h4>Permita a instalação</h4><p>Se o Android pedir, libere “instalar apps desconhecidos” para o navegador.</p></div>
    <div class="step reveal"><i>3</i><h4>Abra com internet</h4><p>Abra o app com dados ou Wi-Fi ligado para carregar as configurações e conecte.</p></div>
  </section>

  <section class="cta reveal">
    <h2 style="justify-content:center;margin:0">Precisa de ajuda ou de um acesso?</h2>
    <p>Fale direto com o suporte Shelby no WhatsApp.</p>
    <a class="wa" href="https://wa.me/<?= $WHATS ?>" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.8 11.8 0 0 0 1.9 17.6L.5 23.5l6-1.6A11.8 11.8 0 0 0 20.5 3.5zM12 21.4a9.6 9.6 0 0 1-4.9-1.3l-.4-.2-3.5.9.9-3.4-.2-.4A9.6 9.6 0 1 1 12 21.4zm5.3-7.2c-.3-.1-1.7-.9-2-1s-.5-.1-.7.1-.8 1-1 1.2-.4.2-.7.1a7.9 7.9 0 0 1-3.9-3.4c-.3-.5.3-.5.8-1.6.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.8.4 3.5 3.5 0 0 0-1.1 2.6 6 6 0 0 0 1.3 3.2 13.8 13.8 0 0 0 5.3 4.7c2 .8 2.7.9 3.7.7.6-.1 1.7-.7 2-1.4s.2-1.2.2-1.4-.3-.2-.6-.3z"/></svg>
      Chamar no WhatsApp</a>
  </section>

  <footer>
    © <?= date('Y') ?> Shelby Systems · <a href="termos.php" target="_blank">Termos de uso</a><br>
    Desenvolvido por <b>Martins DevOper</b>
  </footer>
</div>
<a class="fab" href="/" aria-label="Início"><svg viewBox="0 0 24 24"><path d="M12 3l9 8h-3v9h-5v-6h-2v6H6v-9H3z"/></svg></a>
<script>
(function(){
  var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('on');io.unobserve(e.target);}});},{threshold:.12});
  document.querySelectorAll('.reveal').forEach(function(el){io.observe(el);});
  var c=document.getElementById('stars'),x=c.getContext('2d'),p=[],W,H;
  function rs(){W=c.width=innerWidth;H=c.height=innerHeight;p=[];for(var i=0;i<Math.min(70,W/12);i++)p.push({x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.6+.3,v:Math.random()*.35+.08,c:Math.random()<.2?'255,201,77':'0,229,255'});}
  function dr(){x.clearRect(0,0,W,H);p.forEach(function(s){s.y-=s.v;if(s.y<0){s.y=H;s.x=Math.random()*W;}x.beginPath();x.arc(s.x,s.y,s.r,0,6.3);x.fillStyle='rgba('+s.c+','+(.35+s.r/3)+')';x.fill();});requestAnimationFrame(dr);}
  addEventListener('resize',rs);rs();dr();
})();
</script>
</body>
</html>
