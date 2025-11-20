# Projet Infrastructure Portfolio

## Résumé du projet

Ce projet consiste à mettre en place une infrastructure réseau complète comprenant :

* un pare-feu **pfSense** avec WAN/LAN/DMZ,
* un switch **Cisco manageable** configuré avec VLANs,
* deux serveurs en **DMZ** (Portfolio + Backup),
* un PC Admin isolé dans le LAN,
* un site Portfolio hébergé localement et accessible via un nom de domaine.

Cette documentation décrit toute la configuration : réseau, sécurité, serveurs, firewall, VLAN, mise en ligne du site.

---

## Installation (Guide rapide)

### 1. Installer pfSense

* Préparer une clé USB pfSense
* Installer pfSense sur la machine dédiée
* Configurer les interfaces :

  * WAN = vers box FAI
  * LAN = vers switch
  * DMZ = vers switch

### 2. Installer les serveurs (Ubuntu Server 22.04 recommandé)

* Installer Ubuntu Server
* Ajouter un utilisateur admin
* Configurer IP fixe (DMZ)
* Installer Apache/PHP ou Docker

### 3. Configurer switch Cisco

* Créer VLAN 10 / 20 / 30
* Port trunk vers pfSense
* Ports Access pour PC/Serveurs

### 4. Déployer le site Portfolio

* Copier fichiers → `/var/www/portfolio/`
* Activer VirtualHost Apache
* Ouvrir ports 80/443 sur pfSense

---

## Table des matières

1. Introduction & Résumé
2. Installation complète
3. Architecture réseau (pfSense + Switch Cisco)
4. VLANs & Sécurité
5. Configuration des serveurs
6. Synchronisation & Backup
7. Docker (optionnel)
8. HAProxy (optionnel)
9. Mise en ligne du site

---

# Infrastructure Complete README

## Infra

```markdown
                   INTERNET
                       │
                       ▼
               ┌─────────────┐
               │   pfSense   │
               ├──────┬──────┤
               │ WAN  │ LAN  │ DMZ
               │(net) │(int) │(ext)
               └──────┴──────┴──────┘
                     │
                     ▼
               ┌──────────┐
               │ Switch   │  (manageable)
               └──────────┘
            VLAN assignés par pfSense
         ┌──────────┬──────────────┬───────────┐
         ▼          ▼              ▼            ▼
   PC Admin    LAN Devices    Serveur1 (DMZ)   Serveur2 (DMZ)
```

#### Serveur 2 pas obligatoire (utilisé pour backup / flux / failover)

---

## Configuration Infra

### WAN (vers Box FAI)

```nginx
pfSense WAN : DHCP
```

---

## LAN (réseau interne + PC admin)

```yamel
LAN interface pfSense : 10.0.0.1/24
PC Admin :               10.0.0.10
Switch manageable :       10.0.0.2
Autres PC :        10.0.0.x
```

---

## DMZ (Serveurs exposés)

```nginx
DMZ interface pfSense : 10.0.10.1/24
Serveur Portfolio :      10.0.10.10
Serveur 2 :              10.0.10.20
```

---

# Règles NAT & Firewall pfSense

## DMZ → Internet

```yaml
Source : DMZ subnet
Destination : ANY (internet)
Action : ALLOW
Ports autorisés : 80, 443, DNS, NTP
```

Ports recommandés uniquement :

* 80 (HTTP)
* 443 (HTTPS)
* 53 (DNS)
* 123 (NTP)

---

## DMZ → LAN (INTERDIT)

```yaml
Source : DMZ subnet
Destination : LAN subnet
Action : BLOCK
```

La DMZ ne doit jamais accéder au LAN.

---

## LAN → DMZ (PC Admin uniquement)

```yaml
Source : LAN subnet
Destination : DMZ servers (10.0.10.10 / 10.0.10.20)
Ports autorisés : 22 (SSH), 80, 443, 3306 si nécessaire
Action : ALLOW
```

Le PC admin peut :

* gérer Apache
* se connecter en SSH
* voir les logs
* déployer des fichiers

---

## WAN → DMZ (Accès Internet au Portfolio)

```yaml
WAN → 10.0.10.10 (Serveur Portfolio)
Ports autorisés : 80, 443
Action : NAT + ALLOW
```

---

# VLAN dans pfSense

| VLAN | Nom  | Interface pfSense |
| ---- | ---- | ----------------- |
| 10   | LAN  | Port LAN          |
| 20   | DMZ  | Port DMZ          |
| 30   | MGMT | Pour switch/admin |

### LAN interface → VLAN 10

```yaml
Interface LAN : 10.0.0.1/24
VLAN ID : 10
```

### DMZ interface → VLAN 20

```yaml
Interface DMZ : 10.0.10.1/24
VLAN ID : 20
```

### MGMT (optionnel) → VLAN 30

```yaml
Interface MGMT : 10.0.30.1/24
VLAN ID : 30
```

---

# Switch Cisco (manageable)

## Topologie

```pgsql
pfSense (LAN+DMZ) ─── trunk ─── Switch Cisco
                           ├── Access VLAN 10 → PC Admin
                           ├── Access VLAN 20 → Serveur Portfolio
                           ├── Access VLAN 20 → Serveur 2
                           └── VLAN 30 (optionnel) → MGMT
```

---

## Création des VLANs

```bash
enable
configure terminal

vlan 10
 name LAN
exit

vlan 20
 name DMZ
exit

vlan 30
 name MGMT
exit
```

## Port trunk vers pfSense

```bash
interface fa0/1
 switchport mode trunk
 switchport trunk allowed vlan 10,20,30
 spanning-tree portfast trunk
exit
```

## Ports machines → ACCESS

### PC Admin = VLAN 10

```bash
interface fa0/2
 switchport mode access
 switchport access vlan 10
 spanning-tree portfast
exit
```

### Serveur Portfolio = VLAN 20

```bash
interface fa0/3
 switchport mode access
 switchport access vlan 20
 spanning-tree portfast
exit
```

### Serveur 2 = VLAN 20

```bash
interface fa0/4
 switchport mode access
 switchport access vlan 20
 spanning-tree portfast
exit
```

### Management du switch = VLAN 30

```bash
interface vlan 30
 ip address 10.0.30.2 255.255.255.0
exit

interface fa0/5
 switchport mode access
 switchport access vlan 30
 spanning-tree portfast
exit
```

---

# Configuration des Serveurs (DMZ)

## Serveur Portfolio (Serveur 1)

### Configuration IP

```yaml
IP : 10.0.10.10
Masque : 255.255.255.0
Passerelle : 10.0.10.1
DNS : 1.1.1.1 / 8.8.8.8
```

### Installation Apache + PHP

```bash
sudo apt update
sudo apt install -y apache2 php php-cli php-mysql
```

### Emplacement du site

```
/var/www/html/
```

### Droits

```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### UFW

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

---

## Serveur 2 (Backup / Assistance)

### Configuration IP

```yaml
IP : 10.0.10.20
Masque : 255.255.255.0
Passerelle : 10.0.10.1
DNS : 1.1.1.1 / 8.8.8.8
```

### Installation web (optionnel ou identique serveur 1)

```bash
sudo apt update
sudo apt install -y apache2 php php-cli
```

Serveur 2 peut :

* servir de backup
* prendre le relais si Serveur 1 tombe
* être utilisé pour load-balancing (pfSense HAProxy)

---

# Synchronisation Serveur 1 → Serveur 2 (optionnel)

### Installer rsync

```bash
sudo apt install -y rsync
```

### Cron pour synchro horaire

```bash
sudo crontab -e
```

Ajouter :

```bash
0 * * * * rsync -az /var/www/html/ admin@10.0.10.20:/var/www/html/
```

---

# HAProxy (optionnel, sur pfSense)

### Backend load-balanced

```
Serveur 1 : 10.0.10.10:80 (poids 10)
Serveur 2 : 10.0.10.20:80 (poids 1)
```

Failover + load balancing.

---

# Installation du service web "Ubuntu" (Docker)

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io

sudo usermod -aG docker $USER
```

## Configuration des serveurs

### Serveur Portfolio (Serveur 1)

**OS recommandé :** Ubuntu Server 22.04 LTS (stable, sécurisé, idéal pour PHP/Apache)

#### Partitionnement conseillé

* `/` : 20–40 Go
* `/var/www` : 20+ Go
* `swap` : 2–4 Go

#### Installation des paquets web

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 php php-mysql php-xml php-gd php-curl php-mbstring php-zip ufw fail2ban
```

#### Déploiement du site

* Ton site sera dans : `/var/www/portfolio/`
* Activation du VirtualHost :

```bash
sudo a2ensite portfolio.conf
sudo systemctl reload apache2
```

### Serveur 2 (Backup / Monitoring / Load future)

**OS recommandé :** Ubuntu Server 22.04 LTS ou Debian 12

#### Rôle du serveur

* sauvegarde du site (via rsync)
* monitoring : Node Exporter / Prometheus
* SSH de secours

#### Paquets essentiels

```bash
sudo apt install -y rsync openssh-server htop net-tools
```

#### Exemple de réplication automatique (rsync)

```bash
rsync -avz -e ssh /var/www/portfolio/ user@10.0.10.10:/var/www/portfolio-backup/
```

### Configuration réseau des serveurs (IP fixes)

#### Serveur Portfolio

```yaml
network:
  version: 2
  ethernets:
    eth0:
      dhcp4: no
      addresses: [10.0.10.10/24]
      gateway4: 10.0.10.1
      nameservers:
        addresses: [1.1.1.1, 8.8.8.8]
```

```bash
sudo netplan apply
```

#### Serveur 2

Même fichier netplan, IP = `10.0.10.20/24`

### Sécurisation des serveurs

#### UFW (firewall local)

```bash
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

#### Fail2Ban

Protège SSH + Apache automatiquement.

