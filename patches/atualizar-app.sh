#!/bin/bash
# Shelby SSH - Tela "Atualizar App": colar update.json completo, ajustar versão e publicar
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches"
[ "$(id -u)" -eq 0 ] || { echo "Rode como root (sudo -i)."; exit 1; }
TS=$(date +%Y%m%d-%H%M%S); TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
curl -fsSL "$RAW/payloads.php?$TS" -o "$TMP/payloads.php" || { echo "Falha ao baixar"; exit 1; }
OK=0
for D in /var/www/shelby-sshplus /opt/shelby-sshplus/panel; do
  [ -d "$D/admin/pages" ] || continue; OK=1
  mkdir -p "$D/admin/pages/shelby" "$D/config/payload-backups"
  [ -f "$D/admin/pages/shelby/payloads.php" ] && cp "$D/admin/pages/shelby/payloads.php" "$D/admin/pages/shelby/payloads.php.bak-$TS"
  cp "$TMP/payloads.php" "$D/admin/pages/shelby/payloads.php"
  chown -R www-data:www-data "$D/admin/pages/shelby" "$D/config/payload-backups" 2>/dev/null
  [ -f "$D/update.json" ] && chown www-data:www-data "$D/update.json"
  chown www-data:www-data "$D" 2>/dev/null
done
[ "$OK" = 1 ] || { echo "Painel não encontrado."; exit 1; }
echo -e "\033[1;32mPronto!\033[0m Abra Paineis > ⚡Painel Shelby, cole a payload, ajuste a versão e toque em Publicar."
