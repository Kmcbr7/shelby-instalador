#!/bin/bash
# Shelby SSH - troca "SSHPLUS" por "SSH" em tudo que aparece (painel, loja, banco e menu do terminal)
[ "$(id -u)" -eq 0 ] || { echo "Rode como root (sudo -i)."; exit 1; }
TS=$(date +%Y%m%d-%H%M%S); BK="/root/backup-renomear-$TS"; mkdir -p "$BK"
N=0
for D in /var/www/shelby-sshplus /opt/shelby-sshplus; do
  [ -d "$D" ] || continue
  while IFS= read -r -d '' F; do
    mkdir -p "$BK$(dirname "$F")"; cp -p "$F" "$BK$F"
    sed -i -e 's/SSHPLUS/SSH/g' -e 's/SSHPlus/SSH/g' -e 's/SshPlus/SSH/g' -e 's/SSH SSH/SSH/g' "$F"
    N=$((N+1))
  done < <(grep -rlIZ --exclude-dir=app-assets --exclude-dir=vendor --exclude-dir=payload-backups -e 'SSHPLUS' -e 'SSHPlus' -e 'SshPlus' "$D" 2>/dev/null)
done
echo "Arquivos alterados: $N"
# banco: título e textos salvos nas configurações
CFG=/var/www/shelby-sshplus/config/database.php
if [ -f "$CFG" ] && command -v php >/dev/null; then
  DB=$(php -r '$c=include "'"$CFG"'"; echo $c["name"] ?? "";' 2>/dev/null)
  if [ -n "$DB" ]; then
    mysqldump "$DB" configs > "$BK/configs.sql" 2>/dev/null
    mysql "$DB" -e "UPDATE configs SET valor=REPLACE(REPLACE(REPLACE(valor,'SSHPLUS','SSH'),'SSHPlus','SSH'),'SshPlus','SSH') WHERE valor LIKE BINARY '%SSHPLUS%' OR valor LIKE BINARY '%SSHPlus%' OR valor LIKE BINARY '%SshPlus%';" 2>/dev/null && echo "Banco atualizado ($DB)."
  fi
fi
systemctl reload php*-fpm 2>/dev/null; systemctl reload nginx 2>/dev/null
echo -e "\033[1;32mPronto!\033[0m Agora aparece SHELBY SSH. Backup em $BK"
