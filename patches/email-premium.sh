#!/bin/bash
# Shelby SSH - Email premium: imagem clicável, botões App/WhatsApp, prévia
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches/email"
[ "$(id -u)" -eq 0 ] || { echo "Rode como root (sudo -i)."; exit 1; }
TS=$(date +%Y%m%d-%H%M%S); TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
for f in shelby_email_tpl.php enviaremail.php enviandoemail.php salvartemplate.php; do
  curl -fsSL "$RAW/$f?$TS" -o "$TMP/$f" || { echo "Falha ao baixar $f"; exit 1; }
done
OK=0
for D in /var/www/shelby-sshplus /opt/shelby-sshplus/panel; do
  E="$D/admin/pages/email"; [ -d "$E" ] || continue; OK=1
  mkdir -p "/root/backup-email-$TS"; cp -a "$E"/*.php "/root/backup-email-$TS/" 2>/dev/null
  for f in shelby_email_tpl.php enviaremail.php enviandoemail.php salvartemplate.php; do cp "$TMP/$f" "$E/$f"; done
  mkdir -p "$D/config" "$D/apps/email"
  chown www-data:www-data "$D/config" "$D/apps/email"; chmod 775 "$D/apps/email"
  [ -f "$D/config/email-template.json" ] && chown www-data:www-data "$D/config/email-template.json"
  chown www-data:www-data "$E"/*.php
done
[ "$OK" = 1 ] || { echo "Painel não encontrado."; exit 1; }
echo -e "\033[1;32mPronto!\033[0m Abra Enviar Email: configure o Visual do email e toque em Salvar."
echo "Backup dos arquivos antigos: /root/backup-email-$TS"
