#!/bin/bash
# Shelby SSH - Tema premium no painel (menu lateral, topo, cards, tabelas, formulários) + tela inicial nova
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches/tema"
G='\033[1;32m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
TS=$(date +%Y%m%d-%H%M%S); TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
echo -e "${C}==>${N} Baixando o tema..."
for f in shelby-premium.css inicial.php shelby-logo.png; do curl -fsSL "$RAW/$f?$TS" -o "$TMP/$f" || fail "Falha ao baixar $f"; done
OK=0
for B in /var/www/shelby-sshplus /opt/shelby-sshplus/panel; do
  [ -d "$B/admin" ] || continue; OK=1
  mkdir -p "$B/assets/css" "$B/app-assets/images/logo"
  cp "$TMP/shelby-premium.css" "$B/assets/css/shelby-premium.css"
  cp "$TMP/shelby-logo.png" "$B/app-assets/images/logo/shelby-logo.png"
  cp "$B/admin/pages/inicial.php" "$B/admin/pages/inicial.php.bak-$TS" 2>/dev/null
  cp "$TMP/inicial.php" "$B/admin/pages/inicial.php"
  for F in "$B/admin/home.php" "$B/home.php"; do
    [ -f "$F" ] || continue
    cp "$F" "$F.bak-$TS"
    python3 - "$F" "$TS" <<'PY'
import sys,re
p,ts=sys.argv[1],sys.argv[2]; s=open(p,encoding='utf-8',errors='surrogateescape').read()
s=re.sub(r'\s*<link[^>]*shelby-premium\.css[^>]*>','',s)
link='\n    <link rel="stylesheet" type="text/css" href="../assets/css/shelby-premium.css?v='+ts+'">'
if 'assets/css/style.css">' in s: s=s.replace('assets/css/style.css">','assets/css/style.css">'+link,1)
else: s=s.replace('</head>',link+'\n</head>',1)
s=re.sub(r'<h2 class="brand-text">[^<]*SHELBY SSH(PLUS)?[^<]*</h2>','<h2 class="brand-text">⚡SHELBY SSH⚡</h2>',s)
s=s.replace('images/logo/logo.png" alt="avatar" height="35" width="28"','images/logo/shelby-logo.png" alt="Shelby" height="34" width="34"')
open(p,'w',encoding='utf-8',errors='surrogateescape').write(s)
PY
  done
done
[ "$OK" = 1 ] || fail "Painel não encontrado."
chown -R www-data:www-data /var/www/shelby-sshplus/assets /var/www/shelby-sshplus/app-assets/images/logo 2>/dev/null
echo -e "${G}Pronto!${N} Recarregue o painel (se ficar igual, puxe a tela pra baixo pra atualizar)."
echo "Para voltar: os originais ficaram com final .bak-$TS"
