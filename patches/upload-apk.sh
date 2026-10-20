#!/bin/bash
# Shelby SSH - libera upload de APK grande no painel (PHP limitava a 2 MB)
G='\033[1;32m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
echo -e "${C}==>${N} Aumentando limite de upload do PHP para 150 MB..."
N_OK=0
for d in /etc/php/*/fpm/conf.d; do
  [ -d "$d" ] || continue
  printf 'upload_max_filesize = 150M\npost_max_size = 160M\nmax_execution_time = 300\nmax_input_time = 300\nmemory_limit = 256M\n' > "$d/99-shelby-upload.ini"
  v=$(echo "$d" | cut -d/ -f4); systemctl restart "php$v-fpm" 2>/dev/null && N_OK=1
done
[ "$N_OK" = 1 ] || fail "Não achei o php-fpm."
echo -e "${C}==>${N} Ajustando nginx..."
CONF=/etc/nginx/sites-available/shelby-sshplus
if [ -f "$CONF" ]; then
  cp "$CONF" "/root/shelby-nginx.bak-$(date +%s)"
  sed -i 's/client_max_body_size [0-9]*[MmKkGg];/client_max_body_size 160M;/' "$CONF"
  grep -q client_max_body_size "$CONF" || sed -i '0,/server_tokens off;/s//server_tokens off;\n    client_max_body_size 160M;/' "$CONF"
  nginx -t >/dev/null 2>&1 && systemctl reload nginx || fail "nginx recusou; me mande a saída de: nginx -t"
fi
echo -e "${C}==>${N} Permissão da pasta dos APKs..."
for D in /var/www/shelby-sshplus/apps/apk; do mkdir -p "$D"; chown -R www-data:www-data "$D"; chmod 775 "$D"; done
echo -e "${G}Pronto!${N} Agora envie o APK de novo no Upload de Apps."
