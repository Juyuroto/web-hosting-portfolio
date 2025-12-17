# Projet Infrastructure Portfolio

## Résumé du projet

Ce projet consiste à mettre en place une infrastructure réseau complète comprenant :

* un pare-feu **OPNsense** avec WAN/LAN/DMZ,
* un switch **Cisco manageable** configuré avec VLANs,
* deux serveurs en **DMZ** (Portfolio + Backup),
* un PC Admin isolé dans le LAN,
* un site Portfolio hébergé localement et accessible via un nom de domaine.

Cette documentation décrit toute la configuration : réseau, sécurité, serveurs, firewall, VLAN, mise en ligne du site.

---

## Sommaire

1. [Présentation du projet](#résumé-du-projet)
2. [Architecture & infrastructure réseau](#infrastructure-complete-readme)
   - [Schéma global](#infra)
   - [Configuration WAN / LAN / DMZ](#configuration-infra)
3. [Réseau & sécurité OPNsense](#règles-nat--firewall-OPNsense)
   - [Règles NAT & Firewall](#règles-nat--firewall-OPNsense)
   - [VLAN dans OPNsense](#vlan-dans-OPNsense)
4. [Pages & documentation associée](#pages)

## Pages

- [0. Site](../README.md)
- [2. Configuration routeur OPNsense](OPNsense.md)
- [3. Configuration PC Admin](PC_Admin.md)
- [4. Configuration Switch](Switch.md)

---

## Installation (Guide rapide)

### 1. Installer OPNsense

* Préparer une clé USB OPNsense
* Installer OPNsense sur la machine dédiée
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
* Port trunk vers OPNsense
* Ports Access pour PC/Serveurs

### 4. Déployer le site Portfolio

* Copier fichiers → `/var/www/portfolio/`
* Activer VirtualHost Apache
* Ouvrir ports 80/443 sur OPNsense

---

# Infrastructure Complete README

## Infra

```markdown
                   INTERNET
                       │
                       ▼
               ┌─────────────┐
               │   OPNsense  │
               ├──────┬──────┤
               │ WAN  │ LAN  │ DMZ
               │(net) │(int) │(ext)
               └──────┴──────┴──────┘
                     │
                     ▼
               ┌──────────┐
               │  Switch  │  (manageable)
               └──────────┘
            VLAN assignés par OPNsense
         ┌──────────┬──────────────┬───────────┐
         ▼          ▼              ▼            ▼
   PC Admin    LAN Devices    Serveur1 (DMZ)   Serveur2 (DMZ)
```

#### Serveur 2 pas obligatoire (utilisé pour backup / flux / failover)

---

## Configuration Infra

### WAN (vers Box FAI)

```nginx
OPNsense WAN : DHCP
```

---

## LAN (réseau interne + PC admin)

```yaml
LAN interface OPNsense : 10.0.0.1/24
PC Admin :               10.0.0.10
Switch manageable :       10.0.0.2
Autres PC :        10.0.0.x
```

---

## DMZ (Serveurs exposés)

```nginx
DMZ interface OPNsense : 10.0.10.1/24
Serveur Portfolio :      10.0.10.10
Serveur 2 :              10.0.10.20
```

---

# Règles NAT & Firewall OPNsense

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

# VLAN dans OPNsense

| VLAN | Nom  | Interface OPNsense|
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