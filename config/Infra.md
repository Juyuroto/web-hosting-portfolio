# Architecture Infrastructure Portfolio

## Résumé

Infrastructure on-premise sécurisée pour héberger un portfolio web PHP accessible depuis Internet. Architecture basée sur la **segmentation réseau stricte** et l'utilisation de **conteneurs légers (LXC)** plutôt que de machines virtuelles.

---

## Sommaire

- [Schéma global](#schéma-global)
- [Principes architecturaux](#principes-architecturaux)
- [Plan d'adressage](#plan-dadressage)
- [Flux réseau autorisés](#flux-réseau-autorisés)
- [Composants matériels](#composants-matériels)
- [Logique de sécurité](#logique-de-sécurité)

---

## Schéma global

```
                         INTERNET
                             │
                             ▼
                   ┌─────────────────┐
                   │   Box Bouygues  │
                   │   DHCP (WAN)    │
                   └────────┬────────┘
                            │
                            ▼
              ┌─────────────────────────┐
              │       OPNsense          │
              │      (Pare-feu)         │
              ├─────────────────────────┤
              │ em0: WAN (DHCP)         │
              │ re1: LAN_ADMIN (.10.1)  │
              │ re2: LAN_SERVERS (.11.1)│
              │ re3: (libre)            │
              └────┬──────────────┬─────┘
                   │              │
         ┌─────────┘              └──────────┐
         ▼                                   ▼
    ┌──────────────────┐             ┌──────────────┐
    │  PC Admin        │             │ Serveur Dell │
    │  Debian GUI      │             │  Proxmox VE  │
    ├──────────────────┤             ├──────────────┤
    │ 192.168.10.15/24 │             │ 192.168.11   │
    │                  │             │    .15/24    │
    ├──────────────────┤             ├──────────────┤
    │ • Admin OPNsense │             │ • Hyperviseur│
    │ • Admin Proxmox  │             │ • Conteneurs │
    │ • SSH conteneurs │             │   LXC        │
    │ • Tests locaux   │             ├──────────────┤
    │ PAS Internet     │             │ LXC 100      │
    └──────────────────┘             │ Web Portfolio│
                                     │ .11.20       │
                                     │ Apache + PHP │
                                     ├──────────────┤
                                     │ LXC 101      │
                                     │ Backup (opt.)│
                                     │ .11.30       │
                                     └──────────────┘
```

---

## Principes architecturaux

### 1. Segmentation réseau

**3 réseaux distincts** :
- **WAN** : Connexion Internet (DHCP via Box FAI)
- **LAN_ADMIN** : Zone d'administration isolée (192.168.10.0/24)
- **LAN_SERVERS** : Zone serveurs exposés (192.168.11.0/24)

**Pas de VLANs complexes** : Utilisation d'interfaces physiques séparées sur OPNsense pour une meilleure isolation et simplicité.

### 2. Isolation stricte

```
┌─────────────┐           ┌──────────────┐
│  LAN_ADMIN  │           │  LAN_SERVERS │
│ 192.168.10  │---------->│ 192.168.11   │
│             │  Admin    │              │
│             │ SSH/HTTPS │              │
│             │           │              │
│             │           │              │
│             │<----------│              │
│             │  BLOQUÉ   │              │
└─────────────┘           └──────────────┘
```

**Règle fondamentale** : Les serveurs ne doivent **JAMAIS** pouvoir accéder au réseau d'administration.

### 3. Exposition Internet minimale

**Seul le conteneur web (LXC 100) est exposé** :
- Port 80 (HTTP) → NAT vers 192.168.11.20:80
- Port 443 (HTTPS) → NAT vers 192.168.11.20:443

Tous les autres services restent **inaccessibles depuis Internet**.

### 4. Conteneurs plutôt que VMs

**Pourquoi LXC et pas VMs ?**
- **Performance** : Overhead minimal (pas de virtualisation complète)
- **Ressources** : Serveur Dell avec ressources limitées
- **Rapidité** : Démarrage instantané
- **Densité** : Plus de conteneurs par hôte
- **Simplicité** : Gestion plus facile qu'une VM

**Quand utiliser une VM ?**
- Si besoin d'un OS différent (Windows, BSD)
- Si isolation kernel absolue nécessaire
- Si application non compatible conteneur

---

## Plan d'adressage

### WAN (Internet)

```yaml
Interface: em0
Type: DHCP (fourni par Box FAI)
IP: Attribution automatique
Usage: Connexion Internet sortante + NAT entrant
```

### LAN_ADMIN (Administration)

```yaml
Réseau: 192.168.10.0/24
Gateway: 192.168.10.1 (OPNsense)
DNS: 1.1.1.1, 8.8.8.8
DHCP: Désactivé (IPs fixes uniquement)

Machines:
  - OPNsense em1: 192.168.10.1
  - PC Admin:      192.168.10.15
  - Réservé:       192.168.10.2-9
  - Disponible:    192.168.10.11-254
```

### LAN_SERVERS (Serveurs)

```yaml
Réseau: 192.168.11.0/24
Gateway: 192.168.11.1 (OPNsense)
DNS: 1.1.1.1, 8.8.8.8
DHCP: Désactivé (IPs fixes uniquement)

Machines:
  - OPNsense em2:  192.168.11.1
  - Proxmox:       192.168.11.10
  - LXC 100 (Web): 192.168.11.20
  - LXC 101 (BKP): 192.168.11.30
  - Réservé:       192.168.11.2-9
  - Disponible:    192.168.11.31-254
```

---

## Flux réseau autorisés

### Internet → LAN_SERVERS (via NAT)

```yaml
Source: ANY (Internet)
Destination: 192.168.11.20 (LXC Web)
Ports: 80 (HTTP), 443 (HTTPS)
Action: AUTORISER (NAT + Firewall)
Usage: Accès public au portfolio
```

### LAN_ADMIN → LAN_SERVERS

```yaml
# SSH vers conteneurs
Source: 192.168.10.15 (PC Admin)
Destination: 192.168.11.0/24
Port: 22
Action: AUTORISER

# Proxmox WebGUI
Source: 192.168.10.15
Destination: 192.168.11.10
Port: 8006
Action: AUTORISER

# HTTP/HTTPS (tests)
Source: 192.168.10.15
Destination: 192.168.11.0/24
Ports: 80, 443
Action: AUTORISER
```

### LAN_SERVERS → Internet

```yaml
# DNS
Source: 192.168.11.0/24
Destination: ANY
Port: 53 (UDP)
Action: AUTORISER

# NTP
Source: 192.168.11.0/24
Destination: ANY
Port: 123 (UDP)
Action: AUTORISER

# HTTP/HTTPS (mises à jour)
Source: 192.168.11.0/24
Destination: ANY
Ports: 80, 443 (TCP)
Action: AUTORISER
```

### LAN_SERVERS → LAN_ADMIN

```yaml
Source: 192.168.11.0/24
Destination: 192.168.10.0/24
Ports: ALL
Action: BLOQUER (règle de sécurité critique)
```

### LAN_ADMIN → Internet

```yaml
Source: 192.168.10.0/24
Destination: ANY
Ports: ALL
Action: BLOQUER (PC Admin isolé d'Internet)
```

---

## Composants matériels

### Box FAI (Bouygues)

```yaml
Rôle: Modem/Routeur Internet
Mode: Bridge ou NAT
Connexion: Vers OPNsense em0 (WAN)
Configuration: Mode par défaut
IP WAN publique: Attribution ISP
```

### OPNsense (Pare-feu)

```yaml
Matériel: PC dédié / Mini-PC / VM
OS: OPNsense (FreeBSD)
Interfaces:
  - em0: WAN (vers Box)
  - em1: LAN_ADMIN (vers PC Admin)
  - em2: LAN_SERVERS (vers Serveur Dell)
  - em3: Libre (future extension)
RAM: 4 GB minimum
Stockage: 32 GB minimum
Rôle: Pare-feu, routeur, NAT, DHCP, DNS
```

### Serveur Dell (Proxmox)

```yaml
Matériel: Serveur Dell physique
OS: Proxmox VE 8.x
CPU: [À définir selon modèle]
RAM: [À définir, minimum 16 GB recommandé]
Stockage: [À définir, SSD recommandé]
Interface réseau: 1 Gbps minimum
IP: 192.168.11.10/24
Rôle: Hyperviseur pour conteneurs LXC
```

### PC Admin (Debian)

```yaml
Matériel: PC fixe ou portable
OS: Debian 12 avec GNOME
RAM: 4 GB minimum
Stockage: 128 GB minimum
Interface réseau: 1 Gbps (câble Ethernet recommandé)
IP: 192.168.10.15/24
Rôle: Poste d'administration isolé
```

---

## Logique de sécurité

### Couches de défense (Defense in Depth)

**Niveau 1 : Pare-feu OPNsense**
- Filtrage par règles strictes
- NAT pour exposition Internet limitée
- IDS/IPS (Suricata) optionnel
- Logs centralisés

**Niveau 2 : Pare-feu Proxmox**
- Filtrage au niveau Datacenter
- Règles par conteneur
- Isolation réseau entre conteneurs

**Niveau 3 : Pare-feu conteneurs (UFW)**
- Pare-feu local sur chaque LXC
- Principe du moindre privilège
- Logs locaux

**Niveau 4 : Durcissement applicatif**
- Fail2Ban (protection brute-force)
- Headers HTTP sécurisés
- Permissions fichiers strictes
- Mises à jour automatiques

### Principe du moindre privilège

Chaque composant n'a accès qu'à ce dont il a **strictement besoin** :

```
┌──────────────┐
│  Internet    │  → Accès uniquement au conteneur web (80/443)
└──────────────┘

┌──────────────┐
│  PC Admin    │  → Accès SSH + WebGUI vers serveurs
└──────────────┘     Pas d'accès Internet

┌──────────────┐
│  Conteneurs  │  → Accès Internet (mises à jour)
└──────────────┘     Pas d'accès vers PC Admin
```

### Journalisation et audit

**Logs centralisés** :
- OPNsense : Firewall logs (connexions autorisées/bloquées)
- Proxmox : Logs système + conteneurs
- Conteneurs : Logs Apache, SSH, authentification

**Rétention recommandée** : 30 jours minimum

**Alertes à configurer** :
- Tentatives SSH échouées répétées
- Scans de ports détectés
- Connexions inhabituelles depuis Internet

---

## Évolution possible de l'infrastructure

### Court terme

Conteneur web opérationnel  
Certificat SSL Let's Encrypt  
Sauvegardes automatiques Proxmox  
Monitoring basique (htop, Proxmox stats)

### Moyen terme

Conteneur de backup dédié (LXC 101)  
Conteneur monitoring (Grafana + Prometheus)  
IDS/IPS sur OPNsense (Suricata)  
Automatisation backups vers stockage externe

### Long terme

Conteneur de test (environnement staging)  
Load balancer (HAProxy) si besoin de scalabilité  
VPN pour accès distant sécurisé  
Conteneur mail (si nécessaire)

---

## Documentation associée

| Fichier | Description |
|---------|-------------|
| [README.md](README.md) | Vue d'ensemble et accès rapides |
| [OPNsense.md](OPNsense.md) | Configuration complète du pare-feu |
| [Proxmox.md](Proxmox.md) | Configuration Proxmox + conteneurs |
| [Web_Container.md](Web_Container.md) | Déploiement du site web |
| [PC_Admin.md](PC_Admin.md) | Configuration poste d'administration |

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini