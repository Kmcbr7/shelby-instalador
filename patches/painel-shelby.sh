#!/bin/bash
# Shelby SSH - Painel Shelby: update.json público + página de payloads + /admin sem barra
# Uso (root):  bash <(curl -fsSL https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches/painel-shelby.sh)
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches"
WEB=/var/www/shelby-sshplus
SRC=/opt/shelby-sshplus/panel
CONF=/etc/nginx/sites-available/shelby-sshplus
G='\033[1;32m'; R='\033[1;31m'; C='\033[1;36m'; Y='\033[1;33m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
step(){ echo -e "${C}==>${N} $*"; }
[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
[ -d "$WEB" ] || fail "Painel não encontrado em $WEB."
[ -f "$CONF" ] || fail "Config do nginx não encontrada em $CONF."
TS=$(date +%Y%m%d-%H%M%S)

step "Baixando a página Painel Shelby..."
TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
curl -fsSL "$RAW/payloads.php?$TS" -o "$TMP/payloads.php" || fail "Falha ao baixar payloads.php"
for D in "$WEB" "$SRC"; do
  [ -d "$D/admin/pages" ] || continue
  mkdir -p "$D/admin/pages/shelby"
  cp "$TMP/payloads.php" "$D/admin/pages/shelby/payloads.php"
done

step "Adicionando ⚡Painel Shelby no menu Paineis (GLMod, Atlantus e DTunnel continuam)..."
for F in "$WEB/admin/home.php" "$SRC/admin/home.php"; do
  [ -f "$F" ] || continue
  grep -q 'page=shelby/payloads' "$F" && continue
  cp "$F" "$F.bak-$TS"
  python3 - "$F" <<'PY'
import sys
p=sys.argv[1]; s=open(p,encoding='utf-8',errors='surrogateescape').read()
anchor='Painel DTunnel</span></a></li>'
add='\n                                <li class="active"><a class="d-flex align-items-center" href="?page=shelby/payloads"><i data-feather="zap"></i><span class="menu-title text-truncate" data-i18n="Painel Shelby">⚡Painel Shelby</span></a></li>'
if anchor in s:
    s=s.replace(anchor,anchor+add,1)
else:
    s=s.replace('<span>⚙️ Paineis ⚙️</span>','<span>⚙️ Paineis ⚙️</span></li>'+add+'<li class="d-none">',1)
open(p,'w',encoding='utf-8',errors='surrogateescape').write(s)
PY
done

step "Publicando o update.json..."
if [ ! -s "$WEB/update.json" ]; then
  ACHADO=$(find /var/www /root /home -maxdepth 4 -name update.json -size +1k 2>/dev/null | grep -v "^$WEB/update.json$" | head -1)
  if [ -n "$ACHADO" ] && python3 -c "import json,sys;json.load(open(sys.argv[1]))" "$ACHADO" 2>/dev/null; then
    cp "$ACHADO" "$WEB/update.json"; echo "   usei o arquivo que já estava em $ACHADO"
  else
    curl -fsSL "$RAW/update.json?$TS" -o "$WEB/update.json" || fail "Falha ao baixar update.json"
    echo "   usei a versão 7 (45 redes)"
  fi
fi
mkdir -p "$WEB/config/payload-backups"
chown www-data:www-data "$WEB/update.json" "$WEB/config/payload-backups"
chmod 664 "$WEB/update.json"; chmod 750 "$WEB/config/payload-backups"
# o PHP precisa poder gravar o update.json (troca atômica) na raiz
chgrp www-data "$WEB" && chmod g+w "$WEB"

step "Ajustando nginx (update.json público + /admin sem barra)..."
cp "$CONF" "/root/shelby-nginx.bak-$TS"
python3 - "$CONF" <<'PY'
import sys,re
p=sys.argv[1]; s=open(p).read()
blk = "\n    absolute_redirect off;" if "absolute_redirect off" not in s else ""
if "location = /update.json" not in s:
    blk += '\n\n    # update.json do app SHELBY NET (publico)\n    location = /update.json { default_type application/json; add_header Cache-Control "no-cache" always; add_header Access-Control-Allow-Origin "*" always; try_files $uri =404; }'
if blk:
    s = re.sub(r"(listen\s+[0-9]+[^;]*;)", lambda m: m.group(1)+blk, s, count=1)
open(p,"w").write(s)
PY
if nginx -t >/dev/null 2>&1; then
  systemctl reload nginx
else
  cp "/root/shelby-nginx.bak-$TS" "$CONF"; systemctl reload nginx
  fail "nginx recusou a mudança; voltei a config anterior. Me mande a saída de: nginx -t"
fi

step "Testando..."
PORT=$(grep -m1 -oP 'listen\s+\K[0-9]+' "$CONF"); PORT=${PORT:-8181}
CT=$(curl -s -o /dev/null -w '%{content_type}' "http://127.0.0.1:$PORT/update.json")
VER=$(curl -s "http://127.0.0.1:$PORT/update.json" | python3 -c "import json,sys;d=json.load(sys.stdin);print(d.get('Version'),'/',len(d.get('Networks',[])),'redes')" 2>/dev/null)
if [[ "$CT" == application/json* ]] && [ -n "$VER" ]; then
  echo -e "${G}OK!${N} update.json servido como JSON — versão $VER"
else
  echo -e "${Y}Atenção:${N} teste local devolveu '$CT'. Me mande essa linha."
fi
echo -e "${G}Pronto!${N} No painel admin: menu ${Y}⚙️ Paineis → ⚡Painel Shelby${N}"
echo -e "Link do app: ${C}https://painelvpn.drogariatefe24h.com.br/update.json${N}"
