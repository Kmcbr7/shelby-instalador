#!/bin/bash
# Shelby - teste completo numa VPS (Shelby License + pacote SSHPLUS)
# Uso (como root):  bash <(curl -fsSL URL_DO_SETUP) SENHA
BASE="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/shelby"
NPARTS=56
KEY="$1"

G='\033[1;32m'; Y='\033[1;33m'; R='\033[1;31m'; C='\033[1;36m'; N='\033[0m'
fail(){ echo -e "${R}[ERRO]${N} $*"; exit 1; }
step(){ echo -e "${C}==>${N} $*"; }

[ "$(id -u)" -eq 0 ] || fail "Rode como root: digite  sudo -i  e rode o comando de novo."
[ -n "$KEY" ] || fail "Faltou a senha no fim do comando."

step "Instalando Docker e dependencias..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1
apt-get install -y docker.io curl ca-certificates openssl >/dev/null 2>&1 || fail "Falha ao instalar o Docker."
systemctl enable --now docker >/dev/null 2>&1

TMP=$(mktemp -d)
step "Baixando os arquivos ($NPARTS partes)..."
: > "$TMP/b.enc"
for i in $(seq 0 $((NPARTS-1))); do
  f=$(printf "p%03d" "$i")
  curl -fsSL --retry 3 "$BASE/$f" >> "$TMP/b.enc" || fail "Falha ao baixar a parte $f."
  printf "\r   parte %d/%d" "$((i+1))" "$NPARTS"
done; echo
openssl enc -d -aes-256-cbc -pbkdf2 -in "$TMP/b.enc" -out "$TMP/b.tar" -pass pass:"$KEY" 2>/dev/null || fail "Senha incorreta."
tar -xf "$TMP/b.tar" -C "$TMP" || fail "Arquivo corrompido."
rm -rf /opt/shelby-license && mkdir -p /opt /opt/shelby-data
tar -xzf "$TMP/license.tgz" -C /opt || fail "Falha ao extrair o License."
cp "$TMP/sshplus.tgz" /opt/shelby-data/package.tar.gz
tar -tzf /opt/shelby-data/package.tar.gz >/dev/null 2>&1 || fail "Pacote SSHPLUS invalido."
touch /opt/shelby-data/package.tar.gz.manual

step "Montando o container (leva 1 a 2 minutos)..."
cd /opt/shelby-license && docker build -q -t shelby-license . >/dev/null || fail "Falha no build do Docker."

IP=$(curl -fsSL -4 --max-time 8 ifconfig.me 2>/dev/null || curl -fsSL -4 --max-time 8 icanhazip.com 2>/dev/null || hostname -I | awk '{print $1}')
IP=$(echo "$IP" | tr -d '[:space:]')
if [ -f /root/shelby-admin.txt ]; then PASS=$(grep '^senha=' /root/shelby-admin.txt | cut -d= -f2); fi
[ -n "$PASS" ] || PASS=$(tr -dc 'A-Za-z0-9' </dev/urandom | head -c 14)
printf "usuario=admin\nsenha=%s\npainel=http://%s:3000\n" "$PASS" "$IP" > /root/shelby-admin.txt
chmod 600 /root/shelby-admin.txt

docker rm -f shelby-license >/dev/null 2>&1
docker run -d --name shelby-license --restart always \
  -p 3000:3000 -v /opt/shelby-data:/data \
  -e ADMIN_USER=admin -e ADMIN_PASS="$PASS" \
  -e PUBLIC_URL="http://$IP:3000" \
  shelby-license >/dev/null || fail "Falha ao iniciar o container."

step "Aguardando o servidor subir..."
for i in $(seq 1 30); do curl -fsS http://127.0.0.1:3000/health >/dev/null 2>&1 && OK=1 && break; sleep 2; done
rm -rf "$TMP"
[ -n "$OK" ] || fail "O servidor nao respondeu. Veja:  docker logs shelby-license"

echo
echo -e "${G}=====================================================${N}"
echo -e "${G}  Shelby License no ar!${N}"
echo -e "  Painel:  ${Y}http://$IP:3000${N}"
echo -e "  Usuario: ${Y}admin${N}"
echo -e "  Senha:   ${Y}$PASS${N}   (salva em /root/shelby-admin.txt)"
echo
echo -e "  1) Abra o painel no navegador e gere uma key"
echo -e "  2) Depois rode aqui:"
echo -e "     ${Y}bash <(curl -fsSL http://$IP:3000/install)${N}"
echo -e "${G}=====================================================${N}"
echo -e "  Se o painel nao abrir, libere a porta 3000 no grupo de seguranca da AWS."
