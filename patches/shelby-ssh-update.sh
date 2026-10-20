#!/bin/bash
# Shelby SSH - pacote: troca SSHPLUS por SSH + tela Atualizar App
RAW="https://raw.githubusercontent.com/Kmcbr7/shelby-instalador/main/patches"
[ "$(id -u)" -eq 0 ] || { echo "Rode como root (sudo -i)."; exit 1; }
TS=$(date +%s)
echo -e "\033[1;36m[1/2]\033[0m Trocando SSHPLUS por SSH..."
bash <(curl -fsSL "$RAW/renomear-ssh.sh?$TS") || echo "Aviso: falha na troca de nome."
echo -e "\033[1;36m[2/2]\033[0m Instalando a tela Atualizar App..."
bash <(curl -fsSL "$RAW/atualizar-app.sh?$TS") || echo "Aviso: falha na tela Atualizar App."
echo -e "\033[1;32mTudo pronto!\033[0m Abra o painel: Paineis > ⚡Painel Shelby."
