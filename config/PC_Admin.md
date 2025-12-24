# Configuration PC Admin - Debian GUI

## Objectif

Configuration complète du PC Admin sous Debian avec interface graphique pour administrer l'infrastructure portfolio :
- Réseau isolé (LAN_ADMIN, sans accès Internet)
- Outils d'administration (SSH, navigateurs, gestionnaires de fichiers)
- Accès sécurisé aux services (OPNsense, Proxmox, conteneurs)
- Environnement de travail efficace

---

## Sommaire

- [Prérequis](#prérequis)
- [Installation Debian](#installation-debian)
- [Configuration réseau](#configuration-réseau)
- [Installation des outils](#installation-des-outils)
- [Configuration SSH](#configuration-ssh)
- [Accès aux services](#accès-aux-services)
- [Environnement de travail](#environnement-de-travail)
- [Sécurité](#sécurité)
- [Maintenance](#maintenance)

---

## Prérequis

### Matériel

```yaml
Type: PC fixe ou portable
CPU: 2 cores minimum
RAM: 4 GB minimum
Stockage: 128 GB minimum
Réseau: Interface Ethernet 1 Gbps
```

### Réseau

```yaml
IP: 192.168.10.10/24
Gateway: 192.168.10.1 (OPNsense)
DNS: 1.1.1.1, 8.8.8.8
Interface: Vers port em1 d'OPNsense (LAN_ADMIN)
```

**⚠️ Important** : Ce PC n'aura **PAS** d'accès Internet (par sécurité).

---

## Installation Debian

### 1. Télécharger l'ISO

**Depuis un autre PC avec Internet** :

```
https://www.debian.org/download

Version recommandée : Debian 12 (Bookworm) avec environnement graphique
ISO : debian-12.x.x-amd64-netinst.iso
```

### 2. Créer une clé USB bootable

**Sous Linux** :

```bash
sudo dd if=debian-12.x.x-amd64-netinst.iso of=/dev/sdX bs=4M status=progress
sync
```

**Sous Windows** : Utiliser Rufus ou balenaEtcher

### 3. Installation

1. Démarrer sur la clé USB
2. Sélectionner **Graphical Install**
3. Langue : **French**
4. Pays : **France**
5. Disposition clavier : **French**

**Configuration réseau** :

⚠️ **Désactiver la configuration réseau automatique** lors de l'installation si elle échoue (car pas d'Internet). Nous configurerons manuellement après.

```yaml
Hostname: pc-admin
Domain: (laisser vide)
```

**Partitionnement** :

```yaml
Méthode: Assisté - utiliser un disque entier
Schéma: Tout dans une seule partition (simple)
```

**Utilisateurs** :

```yaml
Nom complet: Administrator
Nom d'utilisateur: admin
Mot de passe: [MOT DE PASSE FORT]
```

**Logiciels à installer** :

```
[X] Environnement de bureau Debian
[X] GNOME (ou XFCE si PC limité en ressources)
[X] SSH server
[X] Utilitaires usuels du système
```

Terminer l'installation et redémarrer.

---

## Configuration réseau

### Méthode 1 : Via NetworkManager (GUI)

**Settings → Network**

1. Sélectionner l'interface Ethernet
2. Cliquer sur l'icône engrenage (paramètres)
3. **IPv4** :
   ```yaml
   Method: Manual
   Address: 192.168.10.10
   Netmask: 255.255.255.0 (ou /24)
   Gateway: 192.168.10.1
   DNS: 1.1.1.1, 8.8.8.8
   ```
4. **IPv6** : Désactiver
5. Appliquer

### Méthode 2 : Via fichier de configuration

```bash
# Éditer la configuration
sudo nano /etc/network/interfaces
```

Contenu :

```bash
# Interface loopback
auto lo
iface lo inet loopback

# Interface Ethernet (adapter le nom)
auto eno1
iface eno1 inet static
    address 192.168.10.10/24
    gateway 192.168.10.1
    dns-nameservers 1.1.1.1 8.8.8.8
```

Redémarrer réseau :

```bash
sudo systemctl restart networking
```

### Vérification

```bash
# Vérifier IP
ip addr show

# Tester gateway (OPNsense)
ping 192.168.10.1

# Tester Proxmox
ping 192.168.11.10

# Tester conteneur web
ping 192.168.11.20

# Vérifier que l'accès Internet est BLOQUÉ
ping 8.8.8.8
# Doit échouer (timeout)
```

---

## Installation des outils

⚠️ **Problème** : Pas d'accès Internet pour installer des paquets.

**Solutions** :

### Solution 1 : Installer depuis ISO complète

Télécharger l'**ISO complète Debian avec tous les paquets** (DVD) au lieu de netinst, puis installer.

### Solution 2 : Dépôt local sur clé USB

**Sur un PC avec Internet** :

```bash
# Télécharger les paquets nécessaires
mkdir debian-packages
cd debian-packages

apt download \
    firefox-esr \
    chromium \
    filezilla \
    vim \
    git \
    curl \
    wget \
    htop \
    net-tools \
    openssh-client \
    sshpass \
    nmap \
    tcpdump \
    wireshark

# Copier sur clé USB
```

**Sur le PC Admin** :

```bash
# Monter la clé USB
sudo mount /dev/sdX1 /mnt/usb

# Installer les paquets
cd /mnt/usb
sudo dpkg -i *.deb

# Résoudre dépendances manquantes
sudo apt --fix-broken install
```

### Solution 3 : Autoriser temporairement l'accès Internet

**Sur OPNsense** : Ajouter temporairement une règle firewall pour autoriser PC Admin → Internet.

**Firewall → Rules → LAN_ADMIN → Add**

```yaml
Action: Pass
Protocol: any
Source: 192.168.10.10
Destination: any
Description: TEMP - Allow Admin PC Internet for updates
```

**Sur PC Admin** :

```bash
# Mettre à jour
sudo apt update
sudo apt upgrade -y

# Installer outils
sudo apt install -y \
    firefox-esr \
    filezilla \
    vim \
    git \
    curl \
    wget \
    htop \
    net-tools \
    nmap \
    tcpdump
```

**⚠️ SUPPRIMER la règle temporaire sur OPNsense après installation.**

---

## Configuration SSH

### 1. Générer une paire de clés SSH

```bash
# Générer clé RSA 4096 bits
ssh-keygen -t rsa -b 4096 -C "admin@pc-admin"

# Accepter l'emplacement par défaut : /home/admin/.ssh/id_rsa
# Définir une passphrase (optionnel mais recommandé)
```

### 2. Copier la clé vers les serveurs

**Vers Proxmox** :

```bash
ssh-copy-id root@192.168.11.10
# Entrer le mot de passe root Proxmox
```

**Vers conteneur web** :

```bash
ssh-copy-id root@192.168.11.20
# Entrer le mot de passe root conteneur
```

### 3. Tester les connexions

```bash
# Connexion à Proxmox (sans mot de passe maintenant)
ssh root@192.168.11.10

# Connexion au conteneur
ssh root@192.168.11.20
```

### 4. Configuration ~/.ssh/config (optionnel)

```bash
nano ~/.ssh/config
```

Contenu :

```
# OPNsense
Host opnsense
    HostName 192.168.10.1
    User root
    Port 22

# Proxmox
Host proxmox
    HostName 192.168.11.10
    User root
    Port 22

# Conteneur Web
Host web
    HostName 192.168.11.20
    User root
    Port 22
```

Permissions :

```bash
chmod 600 ~/.ssh/config
```

Utilisation :

```bash
# Connexion simplifiée
ssh proxmox
ssh web
```

---

## Accès aux services

### 1. OPNsense WebGUI

**Navigateur** : Firefox

```
https://192.168.10.1
```

Login : `root` / `[mot de passe OPNsense]`

**⚠️ Certificat auto-signé** : Accepter l'exception de sécurité.

**Créer un marque-page** pour accès rapide.

### 2. Proxmox WebGUI

```
https://192.168.11.10:8006
```

Login :
- Username : `root`
- Realm : `Linux PAM standard authentication`
- Password : `[mot de passe Proxmox]`

**Créer un marque-page**.

### 3. Site web (conteneur)

**Test local** :

```
http://192.168.11.20
```

**Site public** (via NAT) :

```
https://alain-corazzini.fr
```

---

## Environnement de travail

### 1. Créer des raccourcis bureau

**GNOME** : Créer des lanceurs personnalisés.

**Fichier** : `~/.local/share/applications/opnsense.desktop`

```ini
[Desktop Entry]
Name=OPNsense Admin
Comment=OPNsense Firewall WebGUI
Exec=firefox https://192.168.10.1
Icon=security-high
Terminal=false
Type=Application
Categories=Network;Security;
```

**Fichier** : `~/.local/share/applications/proxmox.desktop`

```ini
[Desktop Entry]
Name=Proxmox Admin
Comment=Proxmox VE WebGUI
Exec=firefox https://192.168.11.10:8006
Icon=server
Terminal=false
Type=Application
Categories=System;
```

**Fichier** : `~/.local/share/applications/ssh-web.desktop`

```ini
[Desktop Entry]
Name=SSH Web Container
Comment=SSH to web container
Exec=gnome-terminal -- ssh root@192.168.11.20
Icon=terminal
Terminal=false
Type=Application
Categories=System;
```

### 2. Gestionnaire de fichiers

**Pour transférer des fichiers vers les serveurs** :

**Utiliser FileZilla** :

1. Ouvrir FileZilla
2. **Site Manager** :
   ```yaml
   Host: 192.168.11.20
   Protocol: SFTP
   Logon Type: Key file
   User: root
   Key file: /home/admin/.ssh/id_rsa
   ```
3. Se connecter
4. Naviguer vers `/var/www/portfolio`
5. Uploader/télécharger des fichiers

### 3. Éditeur de texte

**Options** :
- **Vim** (terminal) : Déjà installé
- **Gedit** (GUI) : Préinstallé avec GNOME
- **VS Code** : Pas disponible sans Internet (utiliser Vim ou Gedit)

### 4. Terminal

**Raccourcis utiles** :

```bash
# Ouvrir plusieurs onglets dans le terminal
Ctrl+Shift+T

# Créer des alias dans ~/.bashrc
nano ~/.bashrc
```

Ajouter :

```bash
# Alias SSH
alias ssh-proxmox='ssh root@192.168.11.10'
alias ssh-web='ssh root@192.168.11.20'

# Alias SCP
alias scp-to-web='scp -r ~/projects/portfolio/* root@192.168.11.20:/var/www/portfolio/'

# Alias ping
alias ping-opn='ping 192.168.10.1'
alias ping-prox='ping 192.168.11.10'
alias ping-web='ping 192.168.11.20'
```

Recharger :

```bash
source ~/.bashrc
```

---

## Sécurité

### 1. Verrouillage automatique

**Settings → Privacy → Screen Lock**

```yaml
Automatic Screen Lock: ON
Lock screen after: 5 minutes
Show Notifications: OFF
```

### 2. Pare-feu local (UFW)

```bash
# Installer UFW
sudo apt install -y ufw

# Autoriser SSH sortant
sudo ufw allow out 22/tcp

# Autoriser HTTPS sortant (pour WebGUI)
sudo ufw allow out 443/tcp
sudo ufw allow out 8006/tcp

# Bloquer tout le reste
sudo ufw default deny incoming
sudo ufw default deny outgoing

# Activer
sudo ufw enable

# Vérifier
sudo ufw status verbose
```

⚠️ **Attention** : Cela bloque TOUT sauf SSH et HTTPS. Adapter selon besoins.

**Alternative plus simple** : Ne pas activer UFW sur PC Admin (déjà protégé par OPNsense).

### 3. Mises à jour

**Impossible sans Internet**.

**Solutions** :
1. Autoriser temporairement Internet sur OPNsense pour faire les mises à jour
2. Télécharger les paquets sur clé USB depuis un autre PC
3. Accepter que le PC Admin ne soit pas à jour (moins critique car isolé)

---

## Maintenance

### Vérifications régulières

```bash
# Connectivité réseau
ping 192.168.10.1
ping 192.168.11.10
ping 192.168.11.20

# Espace disque
df -h

# Processus actifs
htop

# Logs système
journalctl -xe
```

### Sauvegardes

**Sauvegarder les fichiers de travail** :

```bash
# Créer un script de backup
nano ~/backup.sh
```

Contenu :

```bash
#!/bin/bash
BACKUP_DIR="/media/usb-backup"
DATE=$(date +%Y%m%d)

# Vérifier que la clé USB est montée
if [ ! -d "$BACKUP_DIR" ]; then
    echo "USB backup not mounted!"
    exit 1
fi

# Backup documents et projets
rsync -av --delete ~/Documents/ $BACKUP_DIR/Documents/
rsync -av --delete ~/projects/ $BACKUP_DIR/projects/
rsync -av --delete ~/.ssh/ $BACKUP_DIR/ssh/

echo "Backup completed: $DATE"
```

Rendre exécutable :

```bash
chmod +x ~/backup.sh
```

Exécuter manuellement :

```bash
# Monter clé USB
sudo mount /dev/sdX1 /media/usb-backup

# Lancer backup
~/backup.sh

# Démonter
sudo umount /media/usb-backup
```

---

## Troubleshooting

### Problème : Impossible d'accéder à OPNsense WebGUI

**Vérifications** :

```bash
# Connectivité
ping 192.168.10.1

# Règles firewall OPNsense
# Vérifier dans OPNsense → Firewall → Rules → LAN_ADMIN
# Règle "Access OPNsense WebGUI" doit être active
```

### Problème : Impossible d'accéder à Proxmox

```bash
# Connectivité
ping 192.168.11.10

# Test port 8006
telnet 192.168.11.10 8006
# Ou
nmap -p 8006 192.168.11.10

# Règles firewall OPNsense
# Vérifier règle "PC Admin to Proxmox WebGUI"
```

### Problème : SSH ne fonctionne pas

```bash
# Tester avec mot de passe
ssh -o PubkeyAuthentication=no root@192.168.11.20

# Vérifier permissions clés
ls -la ~/.ssh/
chmod 700 ~/.ssh
chmod 600 ~/.ssh/id_rsa
chmod 644 ~/.ssh/id_rsa.pub

# Debug SSH
ssh -vvv root@192.168.11.20
```

### Problème : Réseau ne fonctionne plus

```bash
# Vérifier interface
ip addr show

# Vérifier route
ip route

# Redémarrer NetworkManager
sudo systemctl restart NetworkManager

# Ou redémarrer réseau
sudo systemctl restart networking
```

---

## Checklist finale

- [ ] Debian 12 installé avec GNOME
- [ ] IP fixe configurée (192.168.10.10/24)
- [ ] Gateway vers OPNsense (192.168.10.1)
- [ ] DNS configurés (1.1.1.1, 8.8.8.8)
- [ ] Connectivité vers OPNsense vérifiée
- [ ] Connectivité vers Proxmox vérifiée
- [ ] Connectivité vers conteneur web vérifiée
- [ ] Accès Internet BLOQUÉ (testé)
- [ ] Paire de clés SSH générée
- [ ] Clés SSH copiées vers serveurs
- [ ] Connexions SSH fonctionnelles (sans mot de passe)
- [ ] Firefox installé et configuré
- [ ] FileZilla installé et configuré
- [ ] Marque-pages créés (OPNsense, Proxmox)
- [ ] Alias SSH configurés dans ~/.bashrc
- [ ] Verrouillage automatique activé
- [ ] Script de backup créé
- [ ] Documentation accessible

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini