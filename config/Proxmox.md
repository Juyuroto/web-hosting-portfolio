# Configuration Proxmox VE - Hyperviseur Portfolio

## Objectif

Configuration complète de Proxmox VE pour héberger les conteneurs LXC du portfolio, avec :
- Réseau sécurisé (LAN_SERVERS)
- Firewall Proxmox activé et configuré
- Conteneur LXC web (Apache + PHP)
- Snapshots et sauvegardes automatiques
- Accès depuis PC Admin uniquement

---

## Sommaire

- [Prérequis](#prérequis)
- [Installation Proxmox VE](#installation-proxmox-ve)
- [Configuration réseau](#configuration-réseau)
- [Configuration firewall](#configuration-firewall)
- [Création du conteneur web](#création-du-conteneur-web)
- [Gestion des conteneurs](#gestion-des-conteneurs)
- [Sauvegardes](#sauvegardes)
- [Maintenance](#maintenance)

---

## Prérequis

### Matériel

```yaml
Serveur: Dell (modèle à préciser)
CPU: Minimum 2 cores (4+ recommandé)
RAM: Minimum 8 GB (16+ recommandé pour plusieurs conteneurs)
Stockage: 
  - OS: 32 GB minimum (SSD fortement recommandé)
  - Conteneurs: 100 GB+ recommandé
Interface réseau: 1 Gbps Ethernet
```

### Réseau

```yaml
IP Proxmox: 192.168.11.10/24
Gateway: 192.168.11.1 (OPNsense)
DNS: 1.1.1.1, 8.8.8.8
Interface physique: eno1 (ou eth0, selon serveur)
```

---

## Installation Proxmox VE

### 1. Télécharger l'ISO

```bash
# Depuis le site officiel
https://www.proxmox.com/en/downloads

# Version recommandée
proxmox-ve_8.1-1.iso
```

### 2. Créer une clé USB bootable

**Sous Linux** :
```bash
sudo dd if=proxmox-ve_8.1-1.iso of=/dev/sdX bs=4M status=progress
sync
```

**Sous Windows** : Utiliser Rufus ou balenaEtcher

### 3. Installation

1. Démarrer sur la clé USB
2. Sélectionner **Install Proxmox VE (Graphical)**
3. Accepter la licence (EULA)
4. Choisir le disque cible
5. **Configuration réseau** :
   ```yaml
   Hostname: proxmox.local
   IP Address: 192.168.11.10
   Netmask: 255.255.255.0 (/24)
   Gateway: 192.168.11.1
   DNS Server: 1.1.1.1
   ```
6. Définir le mot de passe root : **[MOT DE PASSE FORT]**
7. Confirmer l'installation
8. Redémarrer

### 4. Premier accès

Depuis le PC Admin (192.168.10.10), ouvrir un navigateur :

```
https://192.168.11.10:8006
```

Accepter le certificat auto-signé (ou configurer un certificat valide plus tard).

**Login** :
```
Username: root
Realm: Linux PAM standard authentication
Password: [défini lors de l'installation]
```

---

## Configuration réseau

### 1. Vérifier la configuration actuelle

**Node → System → Network**

Vous devriez voir :

```yaml
# Interface physique
eno1 (ou eth0):
  Type: Network Device
  Active: Yes

# Bridge par défaut
vmbr0:
  Type: Linux Bridge
  IPv4/CIDR: 192.168.11.10/24
  Gateway: 192.168.11.1
  Bridge ports: eno1
  Autostart: Yes
  Active: Yes
```

### 2. Configurer le bridge (si nécessaire)

Si le bridge `vmbr0` n'existe pas, le créer :

**Node → System → Network → Create → Linux Bridge**

```yaml
Name: vmbr0
IPv4/CIDR: 192.168.11.10/24
Gateway: 192.168.11.1
Autostart: Yes
Bridge ports: eno1 (interface physique)
Comment: Bridge principal pour conteneurs
VLAN aware: No
```

**Apply Configuration**

### 3. Tester la connectivité

**Node → Shell**

```bash
# Vérifier l'IP
ip addr show vmbr0

# Tester gateway
ping 192.168.11.1

# Tester Internet
ping 1.1.1.1
ping google.com

# Vérifier DNS
nslookup google.com
```

---

## Configuration firewall

### 1. Activer le firewall Datacenter

**Datacenter → Firewall → Options**

```yaml
Firewall: Yes
Input Policy: DROP (bloquer par défaut)
Output Policy: ACCEPT
Forward Policy: ACCEPT (pour les conteneurs)
```

**Apply**

### 2. Créer un Security Group pour l'administration

**Datacenter → Firewall → Security Group**

Cliquer sur **Create** :

```yaml
Name: admin-access
Comment: Accès depuis PC Admin uniquement
```

**Ajouter des règles au groupe** :

#### Règle 1 : SSH depuis PC Admin

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: tcp
Source: 192.168.10.10/32 (PC Admin)
Dest. port: 22
Comment: SSH from Admin PC
```

#### Règle 2 : Proxmox WebGUI depuis PC Admin

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: tcp
Source: 192.168.10.10/32
Dest. port: 8006
Comment: Proxmox WebGUI from Admin
```

#### Règle 3 : ICMP (Ping) depuis PC Admin

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: icmp
Source: 192.168.10.10/32
ICMP type: (leave empty for all)
Comment: Ping from Admin PC
```

#### Règle 4 : Bloquer tout le reste de LAN_ADMIN

```yaml
Direction: in
Action: DROP
Enable: Yes
Protocol: (leave empty)
Source: 192.168.10.0/24
Comment: Block rest of ADMIN subnet
Log: nolog
```

### 3. Appliquer le Security Group au node

**Node → Firewall → Options**

```yaml
Firewall: Yes (hérite du Datacenter)
Input Policy: DROP
Output Policy: ACCEPT
Enable NDP: No
```

**Node → Firewall → Add Rule** (ou utiliser le Security Group)

Si vous n'utilisez pas le Security Group, recréer les règles individuellement ici.

Sinon :

**Node → Firewall → Insert: Security Group**

```yaml
Security Group: admin-access
Enable: Yes
Comment: Allow admin access
```

### 4. Tester le firewall

```bash
# Depuis PC Admin
ssh root@192.168.11.10
# Doit fonctionner

# Depuis conteneur (une fois créé)
ssh root@192.168.11.10
# Doit être bloqué (timeout)
```

---

## Création du conteneur web

### 1. Télécharger un template LXC

**Local (proxmox) → CT Templates**

Cliquer sur **Templates** :

Chercher et télécharger :
```
debian-12-standard
```

### 2. Créer le conteneur

**Create CT** (bouton en haut à droite)

#### General

```yaml
Node: proxmox
CT ID: 100
Hostname: web-portfolio
Unprivileged container: ✓ (recommandé pour la sécurité)
Resource Pool: (leave empty)
Password: [MOT DE PASSE FORT]
Confirm password: [MOT DE PASSE FORT]
SSH public key: (optionnel, recommandé)
```

#### Template

```yaml
Storage: local
Template: debian-12-standard_12.x_amd64.tar.zst
```

#### Root Disk

```yaml
Storage: local-lvm (ou autre storage disponible)
Disk size (GiB): 8 (suffisant pour site web simple)
```

#### CPU

```yaml
Cores: 2
```

#### Memory

```yaml
Memory (MiB): 2048 (2 GB)
Swap (MiB): 512
```

#### Network

```yaml
Bridge: vmbr0
IPv4: Static
IPv4/CIDR: 192.168.11.20/24
Gateway (IPv4): 192.168.11.1
IPv6: SLAAC (ou None si pas d'IPv6)
```

#### DNS

```yaml
DNS domain: (leave empty)
DNS servers: 1.1.1.1 8.8.8.8
```

#### Confirm

```yaml
Start after created: ✓
```

Cliquer sur **Finish**

Le conteneur va se créer et démarrer automatiquement.

### 3. Vérifier le conteneur

**Node → 100 (web-portfolio) → Summary**

Vérifier :
- Status: **running**
- IP: **192.168.11.20**

### 4. Configurer le firewall du conteneur

**Node → 100 (web-portfolio) → Firewall → Options**

```yaml
Firewall: Yes
Input Policy: DROP
Output Policy: ACCEPT
```

**Node → 100 (web-portfolio) → Firewall → Add Rule**

#### Règle 1 : HTTP depuis Internet

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: tcp
Dest. port: 80
Comment: Allow HTTP from Internet
```

#### Règle 2 : HTTPS depuis Internet

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: tcp
Dest. port: 443
Comment: Allow HTTPS from Internet
```

#### Règle 3 : SSH depuis PC Admin

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: tcp
Source: 192.168.10.10/32
Dest. port: 22
Comment: SSH from Admin PC
```

#### Règle 4 : ICMP depuis PC Admin

```yaml
Direction: in
Action: ACCEPT
Enable: Yes
Protocol: icmp
Source: 192.168.10.10/32
Comment: Ping from Admin PC
```

### 5. Premier accès au conteneur

**Depuis PC Admin** :

```bash
# Test de connectivité
ping 192.168.11.20

# Connexion SSH
ssh root@192.168.11.20
```

Entrer le mot de passe défini lors de la création.

---

## Gestion des conteneurs

### Démarrer / Arrêter

**Via WebGUI** :

```
Node → 100 (web-portfolio) → Start / Stop / Shutdown
```

**Via ligne de commande (sur Proxmox)** :

```bash
# Démarrer
pct start 100

# Arrêter proprement
pct shutdown 100

# Arrêter immédiatement (force)
pct stop 100

# Redémarrer
pct reboot 100

# Voir le statut
pct status 100
```

### Console

**Via WebGUI** :

```
Node → 100 (web-portfolio) → Console
```

Alternative plus fluide :

```
Node → 100 (web-portfolio) → Shell
```

### Statistiques

**Via WebGUI** :

```
Node → 100 (web-portfolio) → Summary
```

Affiche :
- CPU usage
- Memory usage
- Disk usage
- Network traffic

### Entrer dans le conteneur (sans SSH)

**Depuis Proxmox (node)** :

```bash
# Entrer dans le conteneur
pct enter 100

# Exécuter une commande sans entrer
pct exec 100 -- ls -la /var/www
```

### Modifier la configuration

**Via WebGUI** :

```
Node → 100 (web-portfolio) → Options → Edit
```

Ou :

**Via ligne de commande** :

```bash
# Voir la config
pct config 100

# Modifier la RAM
pct set 100 --memory 4096

# Modifier le nombre de cores
pct set 100 --cores 4

# Ajouter un point de montage
pct set 100 --mp0 /mnt/backups,mp=/mnt/backups
```

### Snapshots

**Créer un snapshot avant toute modification majeure** :

**Via WebGUI** :

```
Node → 100 (web-portfolio) → Snapshots → Take Snapshot
```

```yaml
Name: before_php_update
Description: Avant mise à jour PHP 8.2 vers 8.3
Include RAM: No (sauf si nécessaire)
```

**Restaurer un snapshot** :

```
Node → 100 (web-portfolio) → Snapshots → [nom du snapshot] → Rollback
```

**Via ligne de commande** :

```bash
# Créer snapshot
pct snapshot 100 before_php_update --description "Avant mise à jour PHP"

# Lister snapshots
pct listsnapshot 100

# Restaurer
pct rollback 100 before_php_update

# Supprimer snapshot
pct delsnapshot 100 before_php_update
```

---

## Sauvegardes

### 1. Configurer les sauvegardes automatiques

**Datacenter → Backup**

Cliquer sur **Add** :

```yaml
Node: proxmox
Storage: local (ou stockage distant NFS/CIFS si disponible)
Schedule: 
  Day of week: Sunday
  Start Time: 02:00
Selection mode: Include selected VMs
  → Sélectionner 100 (web-portfolio)
Send email to: admin@alain-corazzini.fr (optionnel)
Email: On failure only
Compression: ZSTD (meilleur ratio)
Mode: Snapshot (recommandé)
Enable: Yes
```

**Create**

### 2. Sauvegarde manuelle

**Via WebGUI** :

```
Node → 100 (web-portfolio) → Backup → Backup now
```

```yaml
Storage: local
Mode: Snapshot
Compression: ZSTD
```

**Via ligne de commande** :

```bash
# Backup conteneur 100
vzdump 100 --compress zstd --mode snapshot --storage local

# Backup avec notification
vzdump 100 --compress zstd --mode snapshot --storage local --mailto admin@example.com
```

### 3. Restaurer une sauvegarde

**Via WebGUI** :

```
Node → local → Backups → [sélectionner backup] → Restore
```

```yaml
CT ID: 100 (ou nouveau numéro)
Storage: local-lvm
Unique: Yes (si même CT ID)
```

**Via ligne de commande** :

```bash
# Lister les backups
ls -lh /var/lib/vz/dump/

# Restaurer
pct restore 100 /var/lib/vz/dump/vzdump-lxc-100-2024_12_24-02_00_00.tar.zst --storage local-lvm
```

### 4. Sauvegardes externes (recommandé)

**Monter un stockage NFS** :

**Datacenter → Storage → Add → NFS**

```yaml
ID: backup-nas
Server: 192.168.11.50 (exemple NAS)
Export: /mnt/backups
Content: VZDump backup file
```

Modifier la configuration de backup pour utiliser `backup-nas` au lieu de `local`.

---

## Maintenance

### Mises à jour Proxmox

**Via WebGUI** :

```
Node → Updates → Refresh → Upgrade
```

**Via ligne de commande** :

```bash
# Mettre à jour la liste des paquets
apt update

# Voir les mises à jour disponibles
apt list --upgradable

# Installer les mises à jour
apt upgrade -y

# Mise à jour complète (avec suppression de paquets obsolètes)
apt dist-upgrade -y

# Nettoyer
apt autoremove -y
apt clean
```

### Mises à jour conteneurs

**Depuis le conteneur (SSH)** :

```bash
# Entrer dans le conteneur
ssh root@192.168.11.20

# Mettre à jour
apt update
apt upgrade -y

# Redémarrer si kernel mis à jour
reboot
```

**Ou depuis Proxmox** :

```bash
pct exec 100 -- apt update
pct exec 100 -- apt upgrade -y
```

### Surveillance des ressources

**Via WebGUI** :

```
Node → Summary
```

Affiche :
- CPU usage (load average)
- Memory usage
- Storage usage
- Network traffic

**Via ligne de commande** :

```bash
# CPU et RAM
top
htop (si installé)

# Disque
df -h

# Conteneurs actifs
pct list

# Statistiques conteneur
pct status 100
```

### Logs

**Logs Proxmox** :

```bash
# Logs système
journalctl -xe

# Logs spécifiques Proxmox
tail -f /var/log/pve/tasks.log
tail -f /var/log/daemon.log

# Logs d'un conteneur
pct exec 100 -- tail -f /var/log/syslog
```

---

## Troubleshooting

### Problème : Conteneur ne démarre pas

**Vérifications** :

```bash
# Voir les erreurs
pct start 100

# Consulter les logs
journalctl -xe | grep -i pct

# Vérifier la configuration
pct config 100

# Vérifier le stockage
df -h
pvesm status
```

### Problème : Conteneur sans accès réseau

**Vérifications** :

```bash
# Depuis le conteneur
ip addr
ip route
cat /etc/resolv.conf

# Tester gateway
ping 192.168.11.1

# Tester DNS
ping 1.1.1.1
ping google.com
```

**Corriger si nécessaire** :

```bash
# Entrer dans le conteneur
pct enter 100

# Reconfigurer réseau
nano /etc/network/interfaces
```

Contenu attendu :

```bash
auto lo
iface lo inet loopback

auto eth0
iface eth0 inet static
    address 192.168.11.20/24
    gateway 192.168.11.1
```

Redémarrer réseau :

```bash
systemctl restart networking
```

### Problème : WebGUI Proxmox inaccessible

**Vérifications** :

```bash
# Depuis Proxmox (console physique ou SSH si activé)
systemctl status pveproxy
systemctl status pvedaemon

# Redémarrer services
systemctl restart pveproxy
systemctl restart pvedaemon

# Vérifier port 8006
netstat -tulpn | grep 8006
```

### Problème : Stockage plein

**Libérer de l'espace** :

```bash
# Supprimer vieux backups
rm /var/lib/vz/dump/vzdump-lxc-100-*.tar.zst

# Supprimer snapshots anciens
pct listsnapshot 100
pct delsnapshot 100 [nom_snapshot]

# Nettoyer APT cache
apt clean
apt autoremove -y

# Voir utilisation disque
df -h
du -sh /var/lib/vz/*
```

---

## Checklist finale

- [ ] Proxmox VE installé et accessible (192.168.11.10:8006)
- [ ] Bridge vmbr0 configuré correctement
- [ ] Connectivité Internet fonctionnelle
- [ ] Firewall Datacenter activé
- [ ] Firewall Node configuré avec règles admin
- [ ] Template Debian 12 téléchargé
- [ ] Conteneur LXC 100 créé (192.168.11.20)
- [ ] Firewall conteneur configuré
- [ ] Conteneur accessible en SSH depuis PC Admin
- [ ] Conteneur avec accès Internet (DNS, HTTP/HTTPS)
- [ ] Snapshots configurés
- [ ] Sauvegardes automatiques planifiées
- [ ] Tests de connectivité réussis

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini