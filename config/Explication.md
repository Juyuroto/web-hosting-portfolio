# Portfolio - Infrastructure et Documentation

## Résumé du Projet

Infrastructure on-premise complète pour héberger un portfolio web PHP, accessible depuis Internet via le domaine `alain-corazzini.fr`.

**Architecture** :
- Pare-feu **OPNsense** (segmentation réseau stricte)
- Serveur **Proxmox VE** (conteneurs LXC prioritaires)
- **PC Admin Debian** (administration isolée, sans accès Internet)
- **Conteneur LXC** Apache + PHP (site web exposé)

**Philosophie** : Simplicité, sécurité, performance et maintenabilité.

---

## Sommaire

- [Accès et connexions](#accès-et-connexions)
- [Architecture réseau](#architecture-réseau)
- [Structure de la documentation](#structure-de-la-documentation)
- [Installation rapide](#installation-rapide)
- [Technologies utilisées](#technologies-utilisées)
- [Sécurité](#sécurité)
- [Maintenance](#maintenance)

---

## Accès et connexions

### Accès Web

**Local (depuis PC Admin)** :
```
https://192.168.11.20      (Conteneur Web)
https://192.168.11.15:8006 (Proxmox WebGUI)
https://192.168.10.1       (OPNsense WebGUI)
```

**Public (depuis Internet)** :
```
http://alain-corazzini.fr
https://alain-corazzini.fr (après installation SSL)
```

### Accès SSH (depuis PC Admin uniquement)

```bash
# Connexion au conteneur web
ssh root@192.168.11.20

# Connexion à Proxmox (si activé)
ssh root@192.168.11.15
```

---

## Architecture réseau

```
                    INTERNET
                        │
                        ▼
                   [Box FAI]
                    DHCP
                        │
                        ▼
              ┌─────────────────┐
              │    OPNsense     │
              ├─────────────────┤
              │ WAN:    DHCP    │
              │ ADMIN:  .10.1   │
              │ SERVERS:.11.1   │
              └────┬────────┬───┘
                   │        │
         ┌─────────┘        └─────────┐
         ▼                            ▼
    [PC Admin]                  [Serveur Dell]
    192.168.10.10               Proxmox VE
    Debian GUI                  192.168.11.15
    ├─ Admin OPNsense           │
    ├─ Admin Proxmox            ├─ LXC 100: Web
    ├─ SSH conteneurs           │  192.168.11.20
    └─ PAS d'Internet           │  Apache + PHP
                                │  Portfolio
                                │
                                └─ LXC 101: Backup (opt.)
                                   192.168.11.30
```

### Segmentation réseau

| Réseau          | CIDR            | Gateway | Usage                |
|-----------------|-----------------|---------|----------------------|
| **WAN**         | DHCP            | Box FAI | Internet             |
| **LAN_ADMIN**   | 192.168.10.0/24 | .10.1   | PC Admin isolé       |
| **LAN_SERVERS** | 192.168.11.0/24 | .11.1   | Proxmox + conteneurs |

### Règles de sécurité

**PC Admin → Serveurs** : Autorisé (SSH, HTTPS, Proxmox)  
**Serveurs → Internet** : Autorisé (mises à jour, DNS, NTP)  
**Serveurs → PC Admin** : **BLOQUÉ**  
**PC Admin → Internet** : **BLOQUÉ**  
**Internet → Conteneur Web** : Autorisé (ports 80/443 uniquement)

---

## Installation rapide

### Prérequis

- Box FAI configurée en mode bridge ou NAT
- OPNsense installé avec 3 interfaces réseau
- Proxmox VE installé sur serveur Dell
- PC Admin avec Debian + interface graphique

### Étapes principales

**1. Configurer OPNsense** (voir `OPNsense.md`)
```bash
# Interfaces : WAN (DHCP), LAN_ADMIN (.10.1), LAN_SERVERS (.11.1)
# Firewall : Règles d'isolation ADMIN ↔ SERVERS
# NAT : Port forwarding 80/443 → 192.168.11.20
```

**2. Configurer Proxmox** (voir `Proxmox.md`)
```bash
# IP fixe : 192.168.11.15/24
# Bridge : vmbr0
# Firewall : Activé avec règles strictes
```

**3. Créer le conteneur web** (voir `Web_Container.md`)
```bash
# LXC Debian 12
# IP : 192.168.11.20/24
# Services : Apache, PHP, MySQL (optionnel)
```

**4. Déployer le site**
```bash
# Copier les fichiers vers /var/www/portfolio/
# Configurer VirtualHost Apache
# Tester localement puis exposer via OPNsense
```

---

## Technologies utilisées

**Infrastructure** :
- **Pare-feu** : OPNsense (FreeBSD)
- **Virtualisation** : Proxmox VE 8.x
- **Conteneurs** : LXC (Debian 12)
- **Administration** : Debian 12 avec GNOME

**Stack Web** :
- **Serveur HTTP** : Apache 2.4
- **Langage** : PHP 8.2
- **Base de données** : MySQL 8.0 (optionnel)
- **SSL/TLS** : Let's Encrypt (Certbot)

**Outils** :
- SSH (administration à distance)
- Git (versioning du code)
- Fail2Ban (protection brute-force)
- UFW (pare-feu local sur conteneurs)

---

## Sécurité

### Principes appliqués

**Principe du moindre privilège** : Chaque composant n'a que les accès nécessaires  
**Défense en profondeur** : Pare-feu OPNsense + Proxmox + UFW conteneurs  
**Segmentation réseau** : Isolation stricte ADMIN / SERVERS  
**Exposition minimale** : Seuls ports 80/443 ouverts depuis Internet  
**Mises à jour régulières** : OS, services, applicatifs

### Mesures de protection

**OPNsense** :
- Règles firewall strictes
- IDS/IPS (Suricata) recommandé
- Logs centralisés

**Proxmox** :
- Firewall datacenter activé
- Accès WebGUI uniquement depuis LAN_ADMIN
- Snapshots réguliers des conteneurs

**Conteneur Web** :
- UFW activé (ports 22, 80, 443 uniquement)
- Fail2Ban contre brute-force SSH
- Headers HTTP de sécurité configurés
- Mises à jour automatiques (unattended-upgrades)

**PC Admin** :
- Aucun accès Internet (pas de risque malware)
- Clés SSH uniquement (pas de mot de passe)
- Logs des connexions conservés

---

## Maintenance

### Mises à jour

**OPNsense** :
```bash
# Via WebGUI : System → Firmware → Updates
# Vérifier mensuellement
```

**Proxmox** :
```bash
# Via WebGUI : Node → Updates
# Ou en ligne de commande
apt update && apt upgrade -y
```

**Conteneurs LXC** :
```bash
# Se connecter au conteneur
ssh root@192.168.11.20

# Mettre à jour
apt update && apt upgrade -y
```

### Sauvegardes

**Sauvegardes automatiques Proxmox** :
```bash
# Datacenter → Backup
# Planifier sauvegardes hebdomadaires des conteneurs
# Stockage : Local ou NFS distant
```

**Sauvegarde manuelle base de données** :
```bash
# Depuis le conteneur web
mysqldump -u portfolio_user -p portfolio_db > backup_$(date +%Y%m%d).sql
```

**Sauvegarde fichiers site** :
```bash
# Depuis PC Admin
scp -r root@192.168.11.20:/var/www/portfolio/ ./backup_portfolio/
```

### Surveillance

**Logs à consulter régulièrement** :

**OPNsense** :
```
System → Log Files → Firewall (connexions bloquées)
System → Log Files → Live View
```

**Proxmox** :
```bash
# Logs système
journalctl -xe

# Logs conteneurs
pct exec 100 -- tail -f /var/log/apache2/error.log
```

**Conteneur Web** :
```bash
# Logs Apache
tail -f /var/log/apache2/access.log
tail -f /var/log/apache2/error.log

# Logs authentification
tail -f /var/log/auth.log
```

### Snapshots avant modifications

**Toujours créer un snapshot avant toute modification majeure** :

```bash
# Via Proxmox WebGUI
CT 100 → Snapshots → Take Snapshot
Name: before_update_YYYYMMDD
Description: Avant mise à jour PHP 8.3
```

---

## Troubleshooting rapide

### Site web inaccessible depuis Internet

```bash
# 1. Vérifier le conteneur
ssh root@192.168.11.20
systemctl status apache2

# 2. Vérifier le NAT OPNsense
Firewall → NAT → Port Forward (règles HTTP/HTTPS actives ?)

# 3. Tester depuis PC Admin
curl http://192.168.11.20
```

### Proxmox inaccessible depuis PC Admin

```bash
# 1. Vérifier connectivité réseau
ping 192.168.11.10

# 2. Vérifier règles firewall OPNsense
Firewall → Rules → LAN_ADMIN (règle vers Proxmox active ?)

# 3. Vérifier firewall Proxmox
Datacenter → Firewall → Security Group (règle SSH/8006 ok ?)
```

### Conteneur sans Internet

```bash
# 1. Vérifier route par défaut
ip route

# 2. Vérifier DNS
cat /etc/resolv.conf
ping 1.1.1.1

# 3. Vérifier règles firewall OPNsense
Firewall → Rules → LAN_SERVERS (règles DNS/HTTP/HTTPS ok ?)
```

---

## Support et ressources

**Documentation officielle** :
- OPNsense : https://docs.opnsense.org/
- Proxmox VE : https://pve.proxmox.com/wiki/
- Debian : https://www.debian.org/doc/

**Communauté** :
- Forum OPNsense : https://forum.opnsense.org/
- Forum Proxmox : https://forum.proxmox.com/
- Reddit : r/homelab, r/selfhosted

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini