#!/bin/bash
# Shelby SSH - Loja de Apps premium (/apps)
# Uso (root): bash <(curl -fsSL https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches/loja-premium.sh)
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches/loja"
G='\033[1;32m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
TS=$(date +%Y%m%d-%H%M%S); TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
echo -e "${C}==>${N} Baixando a loja nova..."
for f in index.php shelby-logo.png shelby-net.png; do
  curl -fsSL "$RAW/$f?$TS" -o "$TMP/$f" || fail "Falha ao baixar $f"
done
OK=0
for D in /var/www/shelby-sshplus/apps /opt/shelby-sshplus/panel/apps; do
  [ -d "$D" ] || continue
  cp "$D/index.php" "$D/index.php.bak-$TS" 2>/dev/null
  cp "$TMP/index.php" "$D/index.php"; mkdir -p "$D/img"
  cp "$TMP/shelby-logo.png" "$TMP/shelby-net.png" "$D/img/"
  OK=1
done
[ "$OK" = 1 ] || fail "Pasta apps do painel não encontrada."
chown -R www-data:www-data /var/www/shelby-sshplus/apps 2>/dev/null
echo -e "${G}Pronto!${N} Abra: ${C}https://painelvpn.drogariatefe24h.com.br/apps/${N}"
echo "Para voltar a antiga: cp /var/www/shelby-sshplus/apps/index.php.bak-$TS /var/www/shelby-sshplus/apps/index.php"
