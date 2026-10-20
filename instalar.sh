#!/bin/bash
# =============================================================================
#  SHELBY SSH - instalação COMPLETA com key (script + painel + todos os ajustes)
#  Uso (como root):
#    bash <(curl -fsSL https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/instalar.sh) SUA-KEY
# =============================================================================
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main"
PARTS="c00 c01 c02 c03 c04 c05 c06 c07 c08 c09 c10 c11 c12 c13 c14 c15 c16 c17 c18 c19 c20 c21 c22 c23 c24 c25 c26 c27 c28 c29 c30 c31 c32 c33 c34 c35 c36 c37 c38 c39 c40 c41 c42 c43 c44 c45 c46"
LICENSE_URL="http://13.60.182.58:3000"
KEY="${1:-}"

G='\033[1;32m'; Y='\033[1;33m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
step(){ echo -e "${C}==>${N} $*"; }

[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
clear
echo -e "${Y}═══════════════════════════════════════════════════${N}"
echo -e "${Y}   ⚡ SHELBY SSH • Instalação completa • Shelby Systems ⚡${N}"
echo -e "${Y}═══════════════════════════════════════════════════${N}"
if [ -z "$KEY" ]; then read -rp "Digite sua key: " KEY </dev/tty; fi
KEY=$(echo "$KEY" | tr '[:lower:]' '[:upper:]' | tr -d '[:space:]')
[[ "$KEY" =~ ^SHELBY(-[A-Z0-9]{4}){4}$ ]] || fail "Key em formato inválido."

step "Aguardando o apt liberar (VPS nova às vezes está atualizando)..."
for i in $(seq 1 90); do
  fuser /var/lib/dpkg/lock-frontend /var/lib/apt/lists/lock >/dev/null 2>&1 || break; sleep 2
done
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1
apt-get install -y curl openssl jq rsync tar >/dev/null 2>&1 || fail "Falha ao instalar dependências."

TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
step "Baixando a versão completa..."
: > "$TMP/p.enc"
for f in $PARTS; do
  curl -fsSL --retry 3 "$RAW/completo/$f?$(date +%s)" >> "$TMP/p.enc" || fail "Falha ao baixar a parte $f."
done
openssl enc -d -aes-256-cbc -pbkdf2 -in "$TMP/p.enc" -out "$TMP/pkg.tgz" -pass pass:"$KEY" 2>/dev/null \
  || fail "Key inválida."
mkdir -p "$TMP/src" && tar -xzf "$TMP/pkg.tgz" -C "$TMP/src" --strip-components=1 2>/dev/null || fail "Key inválida."
[ -f "$TMP/src/install.sh" ] || fail "Pacote incompleto."
echo -e "${G}✔ Key válida${N}"

mkdir -p /etc/shelby-sshplus; chmod 711 /etc/shelby-sshplus
printf 'SHELBY_KEY="%s"\nSHELBY_LICENSE_URL="%s"\n' "$KEY" "$LICENSE_URL" > /etc/shelby-sshplus/license.conf
chmod 600 /etc/shelby-sshplus/license.conf

step "Instalando o Shelby SSH..."
SHELBY_LICENSED=1 bash "$TMP/src/install.sh" || fail "Falha na instalação do script."

if [ ! -f /var/www/shelby-sshplus/config/database.php ]; then
  echo
  step "Agora o Painel Web (responda as perguntas; Enter aceita o padrão)"
  SHELBY_DIR=/opt/shelby-sshplus bash /opt/shelby-sshplus/core/menu --instalar-painel </dev/tty
fi

if [ -d /var/www/shelby-sshplus/admin ]; then
  step "Aplicando todos os ajustes do painel..."
  for p in painel-shelby loja-premium upload-apk upload-premium tema-premium shelby-ssh-update email-premium; do
    echo -e "   ${C}•${N} $p"
    bash <(curl -fsSL "$RAW/patches/$p.sh?$(date +%s)") >/dev/null 2>&1 || echo -e "   ${R}aviso:${N} $p falhou"
  done
  if [ ! -s /var/www/shelby-sshplus/update.json ]; then
    curl -fsSL "$RAW/patches/update-v8.json" -o /var/www/shelby-sshplus/update.json \
      && chown www-data:www-data /var/www/shelby-sshplus/update.json && echo -e "   ${C}•${N} payload do app (v8)"
  fi
else
  echo -e "${Y}Painel não instalado.${N} Depois instale pelo menu (opção P) e rode este comando de novo para aplicar os ajustes."
fi

echo
echo -e "${G}═══════════════════════════════════════════════════${N}"
echo -e "${G}  ✔ Shelby SSH instalado e atualizado!${N}"
echo -e "  Digite ${Y}menu${N} para abrir o gerenciador."
echo -e "${G}═══════════════════════════════════════════════════${N}"
