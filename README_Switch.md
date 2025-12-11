# README – Configuration Complète du Switch Cisco (Architecture pfSense + VLAN)

## Shéma réseau - VLAN & Architecture

```markdoown
                 INTERNET
                     │
                     ▼
              ┌────────────┐
              │  pfSense   │
              ├────────────┤
              │ WAN        │
              │ LAN (VLAN10)───┐
              │ ADMIN (VLAN15) │
              │ DMZ (VLAN20)   │
              │ MGMT (VLAN30)  │
              └──────┬─────────┘
                     │
                     ▼
              ┌──────────────┐   (Trunk : VLAN 10/15/20/30)
              │ Switch Cisco │════════════════════════════╗
              └──────────────┘                            ║
                 │       │       │       │                ║
                 │       │       │       │                ║
                 ▼       ▼       ▼       ▼                ║
        PC Admin     Serveur1   Serveur2   Port MGMT      ║
      (VLAN 15)     (VLAN 20)  (VLAN 20)   (VLAN 30)      ║
       10.0.15.x    10.0.10.x  10.0.10.x   10.0.30.2      ║
                                                          ║
                           Réseau LAN (VLAN 10)─────────══╝
                          10.0.0.x (PC domestiques)
```

---

## 1. Objectif

Ce document fournit la configuration complète d’un switch **Cisco IOS** intégré dans une architecture avec :

- pfSense (LAN / DMZ / ADMIN / MGMT)
- Serveur Portfolio
- Serveur Backup
- PC Admin isolé (pas d’accès Internet)
- VLANs segmentés et sécurisés

Aucune autre information n’est requise pour appliquer la configuration.

---

## 2. VLAN à créer

VLAN 10 : LAN
VLAN 20 : DMZ
VLAN 15 : ADMIN (PC Admin isolé)
VLAN 30 : MGMT (management du switch)

---

## 3. Plan d’adressage recommandé

LAN       : 10.0.0.0/24
DMZ       : 10.0.10.0/24
ADMIN     : 10.0.15.0/24
MGMT      : 10.0.30.0/24

Switch (VLAN 30)   : 10.0.30.2
Gateway MGMT (pfSense) : 10.0.30.1

---

## 4. Rôle des ports du switch

```yaml
Fa0/1  : TRUNK vers pfSense (tous les VLANs)
Fa0/2  : ACCESS VLAN 15 (PC Admin – isolé d’Internet)
Fa0/3  : ACCESS VLAN 20 (Serveur Portfolio)
Fa0/4  : ACCESS VLAN 20 (Serveur Backup)
Fa0/5  : ACCESS VLAN 30 (Management switch)
Fa0/6–24 : Ports désactivés
```

---

## 5. Création des VLANs

```bash
enable
configure terminal

vlan 10
 name LAN
exit

vlan 20
 name DMZ
exit

vlan 15
 name ADMIN
exit

vlan 30
 name MGMT
exit
```

---

## 6. Configuration du port TRUNK (vers pfSense)

```bash
interface fa0/1
 switchport mode trunk
 switchport trunk allowed vlan 10,15,20,30
 spanning-tree portfast trunk
exit
```

---

## 7. Configuration des ports ACCESS

### Port Fa0/2 – PC Admin (VLAN 15)

```bash
interface fa0/2
 switchport mode access
 switchport access vlan 15
 spanning-tree portfast
exit
```

### Port Fa0/3 – Serveur Portfolio (DMZ – VLAN 20)

```bash
interface fa0/3
 switchport mode access
 switchport access vlan 20
 spanning-tree portfast
exit
```

### Port Fa0/4 – Serveur Backup (DMZ – VLAN 20)

```bash
interface fa0/4
 switchport mode access
 switchport access vlan 20
 spanning-tree portfast
exit
```

### Port Fa0/5 – Management Switch (VLAN 30)

```bash
interface fa0/5
 switchport mode access
 switchport access vlan 30
 spanning-tree portfast
exit
```

---

## 8. Configuration du management (VLAN 30)

```bash
interface vlan 30
 ip address 10.0.30.2 255.255.255.0
 no shutdown
exit

ip default-gateway 10.0.30.1
```

---

## 9. Sécurisation du switch

### Désactivation des ports inutilisés

```bash
interface range fa0/6 - 24
 shutdown
exit
```

### Activer PortFast globalement

```bash
spanning-tree portfast default
```

### Port Security – PC Admin (optionnel)

```bash
interface fa0/2
 switchport port-security
 switchport port-security maximum 1
 switchport port-security violation restrict
 switchport port-security mac-address sticky
exit
```

### Sécurisation de l’accès management (recommandé)

```bash
line vty 0 4
 transport input ssh
 login local
exit

ip domain-name local
crypto key generate rsa

username admin privilege 15 secret MOTDEPASSE
```

---

## 10. Commandes de vérification

### Vérifier VLANs

```bash
show vlan brief
```

### Vérifier le trunk

```bash
show interfaces fa0/1 switchport
```

### Vérifier les IP du switch

```bash
show ip interface brief
```

### Vérifier les ports actifs

```bash
show interfaces status

```
