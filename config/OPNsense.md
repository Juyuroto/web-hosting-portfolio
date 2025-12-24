# Configuration OPNsense - Pare-feu Portfolio

## Objectif

Configuration complète d'OPNsense pour sécuriser l'infrastructure portfolio avec :
- Segmentation réseau stricte (ADMIN / SERVERS)
- Isolation du PC Admin (pas d'accès Internet)
- Exposition sécurisée du site web (ports 80/443 uniquement)
- Règles de firewall restrictives

---

## Sommaire

- [Prérequis](#prérequis)
- [Installation OPNsense](#installation-opnsense)
- [Configuration des interfaces](#configuration-des-interfaces)
- [Services réseau](#services-réseau)
- [Règles Firewall](#règles-firewall)
- [NAT et Port Forwarding](#nat-et-port-forwarding)
- [Sécurité avancée](#sécurité-avancée)
- [Tests de validation](#tests-de-validation)
- [Maintenance](#maintenance)

---

## Prérequis

### Matériel

```yaml
CPU: 2 cores minimum
RAM: 4 GB minimum
Stockage: 32 GB minimum (SSD recommandé)
Interfaces réseau: 3 minimum (WAN + 2 LAN)
```

### Topologie

```
[Box FAI] ─── em0 (WAN)
                │
            [OPNsense]
                │
        ┌───────┴───────┐
        │               │
      re0 (ADMIN)    re0 (SERVERS)
        │               │
   [PC Admin]     [Proxmox + LXC]
```

---

## Installation OPNsense

### 1. Télécharger l'image

```bash
# Depuis le site officiel
https://opnsense.org/download/

# Image recommandée : DVD (amd64)
opnsense-24.7-dvd-amd64.iso
```

### 2. Créer une clé USB bootable

**Sous Linux** :
```bash
sudo dd if=opnsense-24.7-dvd-amd64.iso of=/dev/sdX bs=4M status=progress
sync
```

**Sous Windows** : Utiliser Rufus ou balenaEtcher

### 3. Installation

1. Démarrer sur la clé USB
2. Sélectionner **Install (UFS)**
3. Choisir le clavier : **French**
4. Partitionnement : **Auto (UFS) - Guided Disk Setup**
5. Confirmer l'installation
6. Définir le mot de passe root : **[MOT DE PASSE FORT]**
7. Redémarrer

### 4. Configuration initiale console

Après le démarrage :

```bash
# Interface WAN
Select interface: em0
Configure IPv4 address WAN interface via DHCP? y
Configure IPv6 address WAN interface via DHCP6? n

# Interface LAN (sera renommée en ADMIN)
Select interface: re0
Enter IPv4 address: 192.168.10.1
Enter subnet bit count: 24
Configure IPv4 address LAN interface via DHCP? n
Configure IPv6 address LAN interface? n

# Enable DHCP server on LAN? n (IPs fixes uniquement)

# Interface OPT1 (sera renommée en SERVERS)
Select interface: re1
Enter IPv4 address: 192.168.11.1
Enter subnet bit count: 24
Configure IPv6 address? n
```

---

## Configuration des interfaces

### Accès WebGUI

Depuis le PC Admin :
```
https://192.168.10.1
Login: root
Password: [défini lors de l'installation]
```

### 1. Renommer les interfaces

**Interfaces → Assignments**

| Interface physique | Nom actuel | Nouveau nom | Description |
|-------------------|------------|-------------|-------------|
| em0 | WAN | WAN | Internet |
| em1 | LAN | LAN_ADMIN | Administration |
| em2 | OPT1 | LAN_SERVERS | Serveurs |

Cliquer sur chaque interface et modifier le champ **Description**.

### 2. Configuration WAN

**Interfaces → WAN**

```yaml
Enable: ✓
IPv4 Configuration Type: DHCP
IPv6 Configuration Type: None
Block private networks: ✓
Block bogon networks: ✓
```

**Save** → **Apply Changes**

### 3. Configuration LAN_ADMIN

**Interfaces → LAN_ADMIN**

```yaml
Enable: ✓
Description: LAN_ADMIN
IPv4 Configuration Type: Static IPv4
IPv4 address: 192.168.10.1/24
IPv6 Configuration Type: None
```

**Save** → **Apply Changes**

### 4. Configuration LAN_SERVERS

**Interfaces → LAN_SERVERS**

```yaml
Enable: ✓
Description: LAN_SERVERS
IPv4 Configuration Type: Static IPv4
IPv4 address: 192.168.11.1/24
IPv6 Configuration Type: None
```

**Save** → **Apply Changes**

---

## Services réseau

### DNS Resolver (Unbound)

**Services → Unbound DNS → General**

```yaml
Enable: ✓
Listen Port: 53
Network Interfaces: LAN_ADMIN, LAN_SERVERS
Outgoing Network Interfaces: WAN
DNSSEC: ✓
```

**Services → Unbound DNS → Advanced**

```yaml
Hide Identity: ✓
Hide Version: ✓
Prefetch Support: ✓
```

**Forwarders** : Laisser vide (résolution directe)

### NTP (Synchronisation temps)

**Services → Network Time → General**

```yaml
Enable: ✓
Interface: WAN, LAN_ADMIN, LAN_SERVERS
NTP Servers:
  - 0.opnsense.pool.ntp.org
  - 1.opnsense.pool.ntp.org
  - 2.opnsense.pool.ntp.org
```

### DHCP (Désactivé)

**Services → DHCPv4 → [LAN_ADMIN]**

```yaml
Enable DHCP server: ✗ (toutes les machines ont des IPs fixes)
```

**Services → DHCPv4 → [LAN_SERVERS]**

```yaml
Enable DHCP server: ✗
```

---

## Règles Firewall

### Philosophie

- **Default Deny** : Tout est bloqué par défaut
- **Explicit Allow** : Seuls les flux nécessaires sont autorisés
- **Logging** : Toutes les règles de blocage sont loggées

### WAN (Interface Internet)

**Firewall → Rules → WAN**

Par défaut, tout est bloqué sauf les connexions NAT définies.

#### Règle 1 : Autoriser HTTP vers conteneur web

```yaml
Action: Pass
Interface: WAN
Protocol: TCP
Source: any
Destination: Single host or Network = 192.168.11.20
Destination port: 80 (HTTP)
Description: Allow HTTP to Web Container
Log: ✓
```

#### Règle 2 : Autoriser HTTPS vers conteneur web

```yaml
Action: Pass
Interface: WAN
Protocol: TCP
Source: any
Destination: Single host or Network = 192.168.11.20
Destination port: 443 (HTTPS)
Description: Allow HTTPS to Web Container
Log: ✓
```

**⚠️ Important** : Ces règles sont créées automatiquement si vous utilisez la fonctionnalité **Filter rule association** lors de la configuration NAT (recommandé).

### LAN_ADMIN (Réseau administration)

**Firewall → Rules → LAN_ADMIN**

**Ordre des règles** (du haut vers le bas) :

#### Règle 1 : Admin → OPNsense WebGUI

```yaml
Action: Pass
Interface: LAN_ADMIN
Protocol: TCP
Source: LAN_ADMIN net
Destination: LAN_ADMIN address (192.168.10.1)
Destination port: 443 (HTTPS)
Description: Access OPNsense WebGUI
```

#### Règle 2 : Admin → Proxmox WebGUI

```yaml
Action: Pass
Interface: LAN_ADMIN
Protocol: TCP
Source: Single host = 192.168.10.10
Destination: Single host = 192.168.11.10
Destination port: 8006
Description: PC Admin to Proxmox WebGUI
```

#### Règle 3 : Admin → SSH conteneurs

```yaml
Action: Pass
Interface: LAN_ADMIN
Protocol: TCP
Source: Single host = 192.168.10.10
Destination: LAN_SERVERS net (192.168.11.0/24)
Destination port: 22 (SSH)
Description: PC Admin SSH to containers
```

#### Règle 4 : Admin → HTTP/HTTPS conteneurs (tests)

```yaml
Action: Pass
Interface: LAN_ADMIN
Protocol: TCP
Source: Single host = 192.168.10.10
Destination: LAN_SERVERS net (192.168.11.0/24)
Destination port: 80, 443 (HTTP-HTTPS)
Description: PC Admin web access for testing
```

#### Règle 5 : Admin → MySQL (optionnel)

```yaml
Action: Pass
Interface: LAN_ADMIN
Protocol: TCP
Source: Single host = 192.168.10.10
Destination: Single host = 192.168.11.20
Destination port: 3306 (MySQL)
Description: PC Admin to MySQL (if needed)
```

⚠️ **Activez cette règle uniquement si nécessaire pour administrer la base de données.**

#### Règle 6 : BLOQUER tout le reste depuis ADMIN

```yaml
Action: Block
Interface: LAN_ADMIN
Protocol: any
Source: LAN_ADMIN net
Destination: any
Description: Block all other traffic from ADMIN
Log: ✓
```

**⚠️ Cette règle bloque l'accès Internet pour le PC Admin.**

### LAN_SERVERS (Réseau serveurs)

**Firewall → Rules → LAN_SERVERS**

**⚠️ RÈGLE CRITIQUE EN PREMIÈRE POSITION** :

#### Règle 1 : BLOQUER SERVERS → ADMIN

```yaml
Action: Block
Interface: LAN_SERVERS
Protocol: any
Source: LAN_SERVERS net (192.168.11.0/24)
Destination: LAN_ADMIN net (192.168.10.0/24)
Description: ⛔ BLOCK SERVERS to ADMIN
Log: ✓
```

**Cette règle DOIT être en premier pour éviter toute compromission.**

#### Règle 2 : Autoriser DNS

```yaml
Action: Pass
Interface: LAN_SERVERS
Protocol: UDP
Source: LAN_SERVERS net
Destination: any
Destination port: 53 (DNS)
Description: Allow DNS queries
```

#### Règle 3 : Autoriser NTP

```yaml
Action: Pass
Interface: LAN_SERVERS
Protocol: UDP
Source: LAN_SERVERS net
Destination: any
Destination port: 123 (NTP)
Description: Allow NTP sync
```

#### Règle 4 : Autoriser HTTP sortant

```yaml
Action: Pass
Interface: LAN_SERVERS
Protocol: TCP
Source: LAN_SERVERS net
Destination: any
Destination port: 80 (HTTP)
Description: Allow HTTP updates
```

#### Règle 5 : Autoriser HTTPS sortant

```yaml
Action: Pass
Interface: LAN_SERVERS
Protocol: TCP
Source: LAN_SERVERS net
Destination: any
Destination port: 443 (HTTPS)
Description: Allow HTTPS updates
```

#### Règle 6 : Autoriser ICMP (ping)

```yaml
Action: Pass
Interface: LAN_SERVERS
Protocol: ICMP
Source: LAN_SERVERS net
Destination: any
ICMP type: any
Description: Allow ping for diagnostics
```

---

## NAT et Port Forwarding

**Firewall → NAT → Port Forward**

### Redirection HTTP

Cliquer sur **+ Add** :

```yaml
Interface: WAN
Protocol: TCP
Destination: WAN address
Destination port: 80 (HTTP)
Redirect target IP: 192.168.11.20
Redirect target port: 80 (HTTP)
Description: NAT HTTP to Web Container
Filter rule association: Add associated filter rule
```

**Save**

### Redirection HTTPS

Cliquer sur **+ Add** :

```yaml
Interface: WAN
Protocol: TCP
Destination: WAN address
Destination port: 443 (HTTPS)
Redirect target IP: 192.168.11.20
Redirect target port: 443 (HTTPS)
Description: NAT HTTPS to Web Container
Filter rule association: Add associated filter rule
```

**Save** → **Apply Changes**

### Vérification NAT

Les règles créées doivent apparaître dans :
- **Firewall → NAT → Port Forward** (règles NAT)
- **Firewall → Rules → WAN** (règles firewall associées)

---

## Sécurité avancée

### 1. Durcissement WebGUI

**System → Settings → Administration**

```yaml
Protocol: HTTPS
SSL Certificate: Générer ou importer un certificat
TCP port: 443
Web GUI listen interfaces: LAN_ADMIN only
Login Protection: ✓ (activer la protection brute-force)
Maximum login attempts: 5
Block for: 15 minutes
```

### 2. Fail2Ban (Plugin)

**System → Firmware → Plugins**

Rechercher et installer : **os-fail2ban**

**Services → Fail2Ban → Settings**

```yaml
Enable: ✓
Jails à activer:
  - sshd
  - opnsense-gui
Ban Time: 3600 seconds
Find Time: 600 seconds
Max Retry: 5
```

### 3. IDS/IPS (Suricata)

**System → Firmware → Plugins**

Installer : **os-suricata**

**Services → Intrusion Detection → Administration**

```yaml
Enable: ✓
IPS mode: ✓ (bloque le trafic malveillant)
Interfaces: WAN, LAN_SERVERS
Pattern matcher: Hyperscan
```

**Services → Intrusion Detection → Download**

```yaml
Ruleset: ET Open (gratuit)
Enable: ✓
Update Interval: Daily
```

Cliquer sur **Download & Update Rules**

### 4. Logs et audit

**System → Settings → Logging**

```yaml
Enable: ✓
Log level: Informational
Preserve logs: 52 (weeks)
```

**Firewall → Log Files → Settings**

```yaml
Log firewall default blocks: ✓
Log packets matched from default pass rules: ✗
Maximum Log File Entries: 25000
```

### 5. Sauvegardes automatiques

**System → Configuration → Backups**

```yaml
Automatic backup:
  Enable: ✓
  Backup Count: 30
```

Télécharger manuellement une sauvegarde :
**System → Configuration → Backups → Download configuration**

---

## Tests de validation

### Test 1 : Connectivité PC Admin → Proxmox

```bash
# Depuis PC Admin (192.168.10.10)
ping 192.168.11.10
# Doit répondre

# Tester WebGUI Proxmox
firefox https://192.168.11.10:8006
# Doit afficher l'interface Proxmox
```

### Test 2 : Connectivité PC Admin → Conteneur Web

```bash
# Depuis PC Admin
ssh root@192.168.11.20
# Doit se connecter

curl http://192.168.11.20
# Doit afficher la page web
```

### Test 3 : PC Admin SANS accès Internet

```bash
# Depuis PC Admin
ping 8.8.8.8
# Doit échouer (timeout)

curl http://google.com
# Doit échouer
```

### Test 4 : Serveurs SANS accès vers PC Admin

```bash
# Depuis conteneur web (192.168.11.20)
ping 192.168.10.10
# Doit échouer (timeout ou "Destination Host Unreachable")

ssh 192.168.10.10
# Doit échouer
```

### Test 5 : Conteneurs avec accès Internet

```bash
# Depuis conteneur web
ping 8.8.8.8
# Doit répondre

curl https://www.google.com
# Doit afficher le HTML de Google

apt update
# Doit se connecter aux dépôts Debian
```

### Test 6 : Accès externe au site web

**Depuis un appareil externe (4G/5G, autre réseau)** :

```bash
curl http://[VOTRE_IP_PUBLIQUE]
# Doit afficher la page du portfolio

# Vérifier avec un navigateur
firefox http://alain-corazzini.fr
```

### Commandes de diagnostic OPNsense

**Diagnostics → Ping** : Tester la connectivité vers différentes cibles

**Diagnostics → States** : Voir les connexions actives

**Firewall → Log Files → Live View** : Voir les connexions en temps réel

**Diagnostics → Packet Capture** : Capturer le trafic réseau (debug avancé)

---

## Maintenance

### Mises à jour

**System → Firmware → Updates**

```yaml
Vérifier les mises à jour: Hebdomadaire
Business Edition: Non (sauf si souscription)
```

Processus de mise à jour :
1. **Sauvegarder la configuration** (System → Configuration → Backups → Download)
2. Vérifier les mises à jour disponibles
3. Appliquer les updates
4. Redémarrer si nécessaire
5. Vérifier que tout fonctionne après redémarrage

### Surveillance des logs

**Firewall → Log Files → Live View**

Vérifier régulièrement :
- Tentatives de connexion bloquées depuis WAN
- Scans de ports
- Règles qui matchent fréquemment

**System → Log Files → General**

Surveiller les erreurs système, les crashs de services, etc.

### Sauvegarde régulière

**Automatique** : Configurée dans System → Configuration → Backups

**Manuelle** (avant toute modification majeure) :
1. System → Configuration → Backups
2. Download configuration
3. Sauvegarder le fichier XML en lieu sûr

### Rotation des logs

Les logs sont automatiquement archivés selon la configuration définie dans **System → Settings → Logging**.

Pour consulter les anciens logs :
```bash
# Via SSH sur OPNsense (si activé)
ls -lh /var/log/
```

---

## Troubleshooting

### Problème : Site web inaccessible depuis Internet

**Vérifications** :

1. **NAT configuré ?**
   ```
   Firewall → NAT → Port Forward
   Règles HTTP/HTTPS présentes ?
   ```

2. **Règles firewall WAN actives ?**
   ```
   Firewall → Rules → WAN
   Règles associées au NAT présentes ?
   ```

3. **Tester depuis PC Admin** :
   ```bash
   curl http://192.168.11.20
   ```

4. **Vérifier l'IP publique** :
   ```bash
   curl ifconfig.me
   ```

5. **DNS configuré ?**
   ```
   Le domaine alain-corazzini.fr pointe-t-il vers l'IP publique ?
   ```

### Problème : PC Admin ne peut pas accéder à Proxmox

**Vérifications** :

1. **Connectivité réseau** :
   ```bash
   ping 192.168.11.10
   ```

2. **Règle firewall présente ?**
   ```
   Firewall → Rules → LAN_ADMIN
   Règle "PC Admin to Proxmox WebGUI" active ?
   ```

3. **Proxmox écoute sur le bon port ?**
   ```bash
   # Depuis Proxmox
   netstat -tulpn | grep 8006
   ```

### Problème : Conteneurs sans Internet

**Vérifications** :

1. **DNS fonctionne ?**
   ```bash
   # Depuis conteneur
   ping 1.1.1.1
   nslookup google.com
   ```

2. **Règles firewall LAN_SERVERS ?**
   ```
   Firewall → Rules → LAN_SERVERS
   Règles DNS/NTP/HTTP/HTTPS présentes ?
   ```

3. **Gateway correcte ?**
   ```bash
   # Depuis conteneur
   ip route
   # Default via 192.168.11.1 ?
   ```

### Problème : Serveurs peuvent accéder au PC Admin (GRAVE)

**Actions immédiates** :

1. **Vérifier règle de blocage** :
   ```
   Firewall → Rules → LAN_SERVERS
   Règle "BLOCK SERVERS to ADMIN" en PREMIÈRE position ?
   ```

2. **Tester depuis conteneur** :
   ```bash
   ping 192.168.10.10
   # Doit échouer
   ```

3. **Consulter les logs** :
   ```
   Firewall → Log Files → Live View
   La règle de blocage doit logger les tentatives
   ```

---

## Checklist finale

- [ ] Interfaces WAN, LAN_ADMIN, LAN_SERVERS configurées
- [ ] DNS Resolver activé et fonctionnel
- [ ] NTP configuré
- [ ] DHCP désactivé (IPs fixes uniquement)
- [ ] Règles firewall LAN_ADMIN configurées
- [ ] Règles firewall LAN_SERVERS configurées
- [ ] Règle de blocage SERVERS → ADMIN en première position
- [ ] NAT HTTP/HTTPS vers conteneur web configuré
- [ ] PC Admin peut accéder à OPNsense WebGUI
- [ ] PC Admin peut accéder à Proxmox
- [ ] PC Admin peut SSH vers conteneurs
- [ ] PC Admin SANS accès Internet
- [ ] Conteneurs SANS accès vers PC Admin
- [ ] Conteneurs avec accès Internet (DNS, NTP, HTTP/HTTPS)
- [ ] Site web accessible depuis Internet (ports 80/443)
- [ ] Fail2Ban installé et configuré
- [ ] Logs activés et consultables
- [ ] Sauvegarde configuration téléchargée

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini