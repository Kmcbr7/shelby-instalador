#!/bin/bash
# Shelby SSH - atualiza o pacote de teste e o script já instalado na VPS
# Uso (como root):  bash <(curl -fsSL URL_DO_UPDATE) SENHA
BASE="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/update"
NPARTS=55
KEY="$1"
G='\033[1;32m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
step(){ echo -e "${C}==>${N} $*"; }
[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
[ -n "$KEY" ] || fail "Faltou a senha no fim do comando."

TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
step "Baixando a versão nova ($NPARTS partes)..."
: > "$TMP/u.enc"
for i in $(seq 0 $((NPARTS-1))); do
  f=$(printf "u%03d" "$i")
  curl -fsSL --retry 3 "$BASE/$f?$(date +%s)" >> "$TMP/u.enc" || fail "Falha ao baixar a parte $f."
  printf "\r   parte %d/%d" "$((i+1))" "$NPARTS"
done; echo
openssl enc -d -aes-256-cbc -pbkdf2 -in "$TMP/u.enc" -out "$TMP/pkg.tgz" -pass pass:"$KEY" 2>/dev/null || fail "Senha incorreta."
tar -tzf "$TMP/pkg.tgz" >/dev/null 2>&1 || fail "Pacote corrompido."

if [ -d /opt/shelby-data ]; then
  cp "$TMP/pkg.tgz" /opt/shelby-data/package.tar.gz && touch /opt/shelby-data/package.tar.gz.manual
  step "Pacote do Shelby License atualizado."
fi

if [ -f /etc/shelby-sshplus/license.conf ]; then
  step "Atualizando o Shelby SSH instalado..."
  mkdir -p "$TMP/src" && tar -xzf "$TMP/pkg.tgz" -C "$TMP/src" --strip-components=1
  SHELBY_LICENSED=1 bash "$TMP/src/install.sh" --update || fail "Falha ao atualizar."
fi
echo -e "${G}Pronto! Digite  menu  para abrir o Shelby SSH.${N}"
