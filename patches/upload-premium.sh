#!/bin/bash
# Shelby SSH - tela Upload de Apps premium + limite de upload liberado
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches"
G='\033[1;32m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
TS=$(date +%Y%m%d-%H%M%S); TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
echo -e "${C}==>${N} Baixando a tela nova..."
curl -fsSL "$RAW/upapps/apps.php?$TS" -o "$TMP/apps.php" || fail "Falha ao baixar apps.php"
curl -fsSL "$RAW/upapps/recebeUpload.php?$TS" -o "$TMP/recebeUpload.php" || fail "Falha ao baixar recebeUpload.php"
OK=0
for D in /var/www/shelby-sshplus/admin /opt/shelby-sshplus/panel/admin; do
  [ -d "$D/pages" ] || continue
  cp "$D/pages/apps.php" "$D/pages/apps.php.bak-$TS" 2>/dev/null
  cp "$D/recebeUpload.php" "$D/recebeUpload.php.bak-$TS" 2>/dev/null
  cp "$TMP/apps.php" "$D/pages/apps.php"; cp "$TMP/recebeUpload.php" "$D/recebeUpload.php"; OK=1
done
[ "$OK" = 1 ] || fail "Painel não encontrado."
grep -qs "upload_max_filesize = 150M" /etc/php/*/fpm/conf.d/99-shelby-upload.ini || bash <(curl -fsSL "$RAW/upload-apk.sh?$TS")
echo -e "${G}Pronto!${N} Abra o painel admin → Gerenciar Apps."
