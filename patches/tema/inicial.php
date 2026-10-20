<?php
$procnoticias = "select * FROM noticias where status='ativo'";
$procnoticias = $conn->prepare($procnoticias);
$procnoticias->execute();

$SQLbuscaclientes = $conn->prepare("select * from usuario where tipo='vpn'");
$SQLbuscaclientes->execute();
$totalclientes = $SQLbuscaclientes->rowCount();
$SQLbuscarevendedores = $conn->prepare("select * from usuario where tipo='revenda'");
$SQLbuscarevendedores->execute();
$totalrevendedores = $SQLbuscarevendedores->rowCount();
$SQLbuscaservidores = $conn->prepare("select * from servidor");
$SQLbuscaservidores->execute();
$totalservidores = $SQLbuscaservidores->rowCount();
$nomeAdmin = strtoupper($administrador['nome'] ?? 'ADMINISTRADOR');
?>
<style>
#shelby-dash{--c:#00e5ff;--v:#8b5cf6;--g:#ffc94d;--g2:#ff9f1c;--mut:#8ea0c4}
#shelby-dash .hero{position:relative;overflow:hidden;border-radius:24px;padding:34px 26px;margin-bottom:22px;background:linear-gradient(120deg,rgba(0,229,255,.14),rgba(139,92,246,.16) 55%,rgba(255,201,77,.12));border:1px solid rgba(0,229,255,.22);box-shadow:0 14px 44px rgba(0,0,0,.4)}
#shelby-dash .hero:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(0,229,255,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(0,229,255,.1) 1px,transparent 1px);background-size:40px 40px;mask-image:radial-gradient(ellipse at 20% 0%,#000,transparent 70%);opacity:.5}
#shelby-dash .hero .inner{position:relative;display:flex;align-items:center;gap:20px;flex-wrap:wrap}
#shelby-dash .ava{width:84px;height:84px;border-radius:50%;padding:3px;background:conic-gradient(var(--c),var(--v),var(--g),var(--c));flex:none;box-shadow:0 0 26px rgba(0,229,255,.4)}
#shelby-dash .ava img{width:100%;height:100%;border-radius:50%;background:#071022;object-fit:cover}
#shelby-dash .hi{font-family:'Orbitron',sans-serif;font-weight:800;font-size:clamp(20px,5vw,30px);line-height:1.1;color:#fff;margin:0}
#shelby-dash .hi b{background:linear-gradient(90deg,var(--g),#fff3c4,var(--c));-webkit-background-clip:text;color:transparent}
#shelby-dash .role{display:inline-flex;align-items:center;gap:7px;margin-top:8px;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;color:#06240f;background:linear-gradient(90deg,#22e58b,#7ef5bf);box-shadow:0 0 14px rgba(34,229,139,.4)}
#shelby-dash .sum{display:flex;gap:10px;flex-wrap:wrap;margin-left:auto}
#shelby-dash .sum div{text-align:center;padding:10px 16px;border-radius:14px;background:rgba(6,11,26,.5);border:1px solid rgba(0,229,255,.18)}
#shelby-dash .sum b{display:block;font-family:'Orbitron',sans-serif;font-size:20px;color:var(--c)}
#shelby-dash .sum span{font-size:11px;color:var(--mut)}
#shelby-dash .sec{margin-top:24px!important;font-family:'Orbitron',sans-serif;font-size:15px;letter-spacing:.5px;margin:6px 2px 14px;color:#dfe9ff;display:flex;align-items:center;gap:10px}
#shelby-dash .sec:after{content:"";flex:1;height:1px;background:linear-gradient(90deg,rgba(0,229,255,.3),transparent)}
#shelby-dash .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px}
#shelby-dash a,#shelby-dash a:hover{text-decoration:none!important}
#shelby-dash .tile{position:relative;display:flex;border-radius:18px;padding:1px;background:linear-gradient(140deg,rgba(0,229,255,.4),rgba(139,92,246,.18) 50%,rgba(255,201,77,.4));transition:transform .2s}
#shelby-dash .tile:hover{transform:translateY(-4px)}
#shelby-dash .tile .in{border-radius:17px;background:linear-gradient(180deg,rgba(13,24,52,.95),rgba(7,13,30,.97));padding:18px 14px;text-align:center;flex:1}
#shelby-dash .ic{width:52px;height:52px;margin:0 auto 10px;border-radius:14px;display:grid;place-items:center;background:rgba(0,229,255,.1);border:1px solid rgba(0,229,255,.25)}
#shelby-dash .ic svg{width:24px;height:24px}
#shelby-dash .num{font-family:'Orbitron',sans-serif;font-size:22px;font-weight:800;color:#fff;line-height:1}
#shelby-dash .lbl{font-size:12.5px;color:var(--mut);margin-top:5px}
#shelby-dash .act .in{background:linear-gradient(160deg,rgba(0,229,255,.16),rgba(139,92,246,.14))}
#shelby-dash .t-c svg{stroke:var(--c)}#shelby-dash .t-c .ic{background:rgba(0,229,255,.12)}
#shelby-dash .t-g svg{stroke:var(--g)}#shelby-dash .t-g .ic{background:rgba(255,201,77,.12);border-color:rgba(255,201,77,.3)}
#shelby-dash .t-v svg{stroke:var(--v)}#shelby-dash .t-v .ic{background:rgba(139,92,246,.14);border-color:rgba(139,92,246,.3)}
#shelby-dash .t-grn svg{stroke:#22e58b}#shelby-dash .t-grn .ic{background:rgba(34,229,139,.12);border-color:rgba(34,229,139,.3)}
#shelby-dash .t-r svg{stroke:#ff5d7a}#shelby-dash .t-r .ic{background:rgba(255,93,122,.12);border-color:rgba(255,93,122,.3)}
</style>

<div id="shelby-dash">
<?php if ($procnoticias->rowCount() > 0) { $noticia = $procnoticias->fetch(); ?>
  <div class="alert alert-primary alert-dismissible text-center mb-2" role="alert" style="border-radius:16px">
    <h4 class="mb-1" style="color:var(--g)"><i data-feather='alert-octagon'></i> <?php echo $noticia['titulo']; ?></h4>
    <div><?php echo $noticia['subtitulo']; ?><br><?php echo $noticia['msg']; ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php } ?>

  <div class="hero">
    <div class="inner">
      <div class="ava"><img src="../../../app-assets/images/avatars/avatar6.png" alt="admin"></div>
      <div>
        <h2 class="hi">Bem-vindo,<br><b><?php echo $nomeAdmin; ?></b></h2>
        <span class="role"><i data-feather="shield" style="width:14px;height:14px"></i> Administrador</span>
      </div>
      <div class="sum">
        <div><b><?php echo $total_acesso_ssh_online; ?></b><span>Online agora</span></div>
        <div><b><?php echo $contas_ssh; ?></b><span>Contas SSH</span></div>
        <div><b><?php echo $servidor_qtd; ?></b><span>Servidores</span></div>
      </div>
    </div>
  </div>

  <div class="sec">Ações rápidas</div>
  <div class="grid">
    <a class="tile act t-grn" href="home.php?page=ssh/adicionar"><div class="in"><div class="ic"><i data-feather='user-plus'></i></div><div class="num" style="font-size:16px">Criar</div><div class="lbl">Conta SSH</div></div></a>
    <a class="tile act t-g" href="home.php?page=ssh/add_teste"><div class="in"><div class="ic"><i data-feather='clock'></i></div><div class="num" style="font-size:16px">Criar</div><div class="lbl">Teste SSH</div></div></a>
    <a class="tile act t-v" href="home.php?page=usuario/1-etapa"><div class="in"><div class="ic"><i data-feather='user-check'></i></div><div class="num" style="font-size:16px">Criar</div><div class="lbl">Revenda</div></div></a>
    <a class="tile act t-c" href="home.php?page=apps"><div class="in"><div class="ic"><i data-feather='smartphone'></i></div><div class="num" style="font-size:16px">Gerenciar</div><div class="lbl">Apps</div></div></a>
  </div>

  <div class="sec">Visão geral</div>
  <div class="grid">
    <a class="tile t-grn" href="home.php?page=ssh/online"><div class="in"><div class="ic"><i data-feather='zap'></i></div><div class="num"><?php echo $total_acesso_ssh_online; ?></div><div class="lbl">Online</div></div></a>
    <a class="tile t-c" href="home.php?page=ssh/contas"><div class="in"><div class="ic"><i data-feather='shield'></i></div><div class="num"><?php echo $contas_ssh; ?></div><div class="lbl">Contas SSH</div></div></a>
    <a class="tile t-v" href="home.php?page=usuario/revenda"><div class="in"><div class="ic"><i data-feather='users'></i></div><div class="num"><?php echo $all_usuarios_revenda_qtd; ?></div><div class="lbl">Revendas</div></div></a>
    <a class="tile t-g" href="home.php?page=servidor/listar"><div class="in"><div class="ic"><i data-feather='server'></i></div><div class="num"><?php echo $servidor_qtd; ?></div><div class="lbl">Servidores</div></div></a>
    <a class="tile t-r" href="home.php?page=ssh/suspenso"><div class="in"><div class="ic"><i data-feather='shield-off'></i></div><div class="num"><?php echo $ssh_susp_qtd; ?></div><div class="lbl">SSH Suspensas</div></div></a>
    <a class="tile t-r" href="home.php?page=usuario/revenda"><div class="in"><div class="ic"><i data-feather='user-x'></i></div><div class="num"><?php echo $revenda_qtd_susp; ?></div><div class="lbl">Revenda Susp.</div></div></a>
    <a class="tile t-g" href="home.php?page=chamados/abertas"><div class="in"><div class="ic"><i data-feather='life-buoy'></i></div><div class="num"><?php echo $all_chamados; ?></div><div class="lbl">Tickets</div></div></a>
    <a class="tile t-c" href="home.php?page=notificacoes/notificacoes&ler=all"><div class="in"><div class="ic"><i data-feather='bell'></i></div><div class="num"><?php echo $totalnoti; ?></div><div class="lbl">Notificações</div></div></a>
    <a class="tile t-v" href="home.php?page=download/downloads"><div class="in"><div class="ic"><i data-feather='download'></i></div><div class="num"><?php echo $todosarquivos; ?></div><div class="lbl">Arquivos</div></div></a>
  </div>
</div>
<script>if(window.feather)feather.replace();</script>
