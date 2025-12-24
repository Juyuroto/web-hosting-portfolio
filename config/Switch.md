# Configuration Switch - Infrastructure Portfolio

## Avertissement

⚠️ **Ce fichier est OPTIONNEL** dans votre architecture actuelle.

Avec **3 interfaces physiques sur OPNsense** (WAN, LAN_ADMIN, LAN_SERVERS), vous n'avez **pas besoin** d'un switch manageable avec VLANs.

**Ce document est fourni uniquement si** :
- Vous souhaitez connecter plusieurs machines dans LAN_ADMIN
- Vous souhaitez connecter plusieurs serveurs dans LAN_SERVERS
- Vous avez un switch manageable disponible et souhaitez l'utiliser

---

## Sommaire

- [Cas d'usage](#cas-dusage)
- [Architecture sans switch](#architecture-sans-switch)
- [Architecture avec switch](#architecture-avec-switch)
- [Configuration switch manageable](#configuration-switch-manageable)
- [Configuration switch non-manageable](#configuration-switch-non-manageable)

---

## Cas d'usage

### Vous N'AVEZ PAS besoin d'un switch si :

✅ 1 PC Admin uniquement dans LAN_ADMIN  
✅ 1 serveur Proxmox uniquement dans LAN_SERVERS  
✅ OPNsense dispose de 3 ports physiques séparés

**Architecture simple (recommandée)** :

```
[Box FAI] ──── em0 (WAN)
                  │
            [OPNsense]
                  │
    ┌─────────────┼─────────────┐
    │             │             │
  em1           em2           em3
(ADMIN)      (SERVERS)      (libre)
    │             │
[PC Admin]   [Proxmox]
```

### Vous AVEZ besoin d'un switch si :

🔹 Plusieurs PC dans LAN_ADMIN (PC Admin + PC Backup par exemple)  
🔹 Plusieurs serveurs physiques dans LAN_SERVERS  
🔹 OPNsense n'a que 2 ports disponibles (WAN + 1 LAN)

---

## Architecture sans switch

### Topologie directe (actuelle)

```
                    INTERNET
                        │
                        ▼
                   [Box FAI]
                        │
                        ▼
              ┌─────────────────┐
              │    OPNsense     │
              ├─────────────────┤
              │ em0: WAN        │
              │ em1: LAN_ADMIN  │ ──→ [PC Admin]
              │ em2: LAN_SERVERS│ ──→ [Proxmox]
              └─────────────────┘
```

**Avantages** :
- ✅ Simple à configurer
- ✅ Pas de matériel supplémentaire
- ✅ Isolation physique maximale
- ✅ Aucun risque de misconfiguration VLAN

**Inconvénients** :
- ❌ 1 seul appareil par réseau
- ❌ Pas d'évolutivité

---

## Architecture avec switch

### Cas 1 : Switch NON-manageable (simple)

```
                    INTERNET
                        │
                        ▼
                   [Box FAI]
                        │
                        ▼
              ┌─────────────────┐
              │    OPNsense     │
              ├─────────────────┤
              │ em0: WAN        │
              │ em1: LAN_ADMIN  │──┐
              │ em2: LAN_SERVERS│──┼──┐
              └─────────────────┘  │  │
                                   ▼  ▼
                           [Switch 1] [Switch 2]
                           (ADMIN)    (SERVERS)
                              │          │
                    ┌─────────┼────┐    ├──────┐
                    ▼         ▼    ▼    ▼      ▼
                [PC Admin] [PC2] [PC3] [Prox1][Prox2]
```

**Utilisation** :
- Switch simple 8 ports sur em1 → Tous en LAN_ADMIN
- Switch simple 8 ports sur em2 → Tous en LAN_SERVERS
- Pas de configuration nécessaire sur le switch

### Cas 2 : Switch manageable avec VLANs

```
                    INTERNET
                        │
                        ▼
                   [Box FAI]
                        │
                        ▼
              ┌─────────────────┐
              │    OPNsense     │
              ├─────────────────┤
              │ em0: WAN        │
              │ em1: TRUNK      │──→ [Switch manageable]
              │   (VLAN 10+20)  │       │
              └─────────────────┘       │
                                    VLAN 10 VLAN 20
                                       │      │
                                    [Admin] [Servers]
```

**Utilisation** :
- OPNsense em1 en mode TRUNK (plusieurs VLANs sur 1 câble)
- Switch manageable avec VLANs 10 (ADMIN) et 20 (SERVERS)
- Plus complexe mais plus flexible

---

## Configuration switch manageable

⚠️ **Seulement si vous avez un switch manageable Cisco, HP, Netgear, etc.**

### Prérequis

```yaml
Switch: Manageable (Cisco, HP, Netgear)
OPNsense: 1 port libre (em1) configuré en TRUNK
VLANs: 10 (ADMIN), 20 (SERVERS)
```

### Étape 1 : Créer les VLANs dans OPNsense

**Interfaces → Other Types → VLAN**

#### VLAN 10 - ADMIN

```yaml
Parent interface: em1
VLAN tag: 10
VLAN priority: 0
Description: VLAN_ADMIN
```

**Save**

#### VLAN 20 - SERVERS

```yaml
Parent interface: em1
VLAN tag: 20
VLAN priority: 0
Description: VLAN_SERVERS
```

**Save**

### Étape 2 : Assigner les VLANs aux interfaces

**Interfaces → Assignments**

Cliquer sur **+** pour ajouter :
- VLAN 10 (em1) → Renommer en **LAN_ADMIN**
- VLAN 20 (em1) → Renommer en **LAN_SERVERS**

**Configurer chaque interface** :

**LAN_ADMIN (VLAN 10)** :
```yaml
Enable: ✓
IPv4 Configuration: Static
IPv4 address: 192.168.10.1/24
Description: LAN Admin - VLAN 10
```

**LAN_SERVERS (VLAN 20)** :
```yaml
Enable: ✓
IPv4 Configuration: Static
IPv4 address: 192.168.11.1/24
Description: LAN Servers - VLAN 20
```

### Étape 3 : Configurer le switch

#### Exemple : Switch Cisco

**Connexion au switch** :

```bash
# Via console série ou Telnet/SSH
telnet 192.168.10.2
```

**Configuration** :

```cisco
enable
configure terminal

! Créer les VLANs
vlan 10
 name ADMIN
exit

vlan 20
 name SERVERS
exit

! Port TRUNK vers OPNsense (exemple: port 1)
interface GigabitEthernet0/1
 description TRUNK to OPNsense
 switchport mode trunk
 switchport trunk allowed vlan 10,20
 spanning-tree portfast trunk
exit

! Ports ACCESS VLAN 10 (PC Admin)
interface range GigabitEthernet0/2-10
 description LAN_ADMIN
 switchport mode access
 switchport access vlan 10
 spanning-tree portfast
exit

! Ports ACCESS VLAN 20 (Serveurs)
interface range GigabitEthernet0/11-20
 description LAN_SERVERS
 switchport mode access
 switchport access vlan 20
 spanning-tree portfast
exit

! Désactiver ports inutilisés
interface range GigabitEthernet0/21-24
 shutdown
exit

! Configuration management (VLAN 10)
interface vlan 10
 ip address 192.168.10.2 255.255.255.0
 no shutdown
exit

ip default-gateway 192.168.10.1

! Sauvegarder
end
write memory
```

#### Exemple : Switch HP/Aruba

```
# Via WebGUI
- VLANs → Add VLAN 10 "ADMIN"
- VLANs → Add VLAN 20 "SERVERS"
- Ports → Port 1 → Mode: Trunk, Allowed VLANs: 10,20
- Ports → Ports 2-10 → Mode: Access, VLAN: 10
- Ports → Ports 11-20 → Mode: Access, VLAN: 20
- Network → Management → IP: 192.168.10.2/24, Gateway: 192.168.10.1
```

### Étape 4 : Vérifier la configuration

**Depuis le switch** :

```cisco
show vlan brief
show interfaces trunk
show running-config
```

**Depuis OPNsense** :

**Interfaces → Overview** → Vérifier que les VLANs sont UP

**Depuis PC Admin** :

```bash
# Doit être dans VLAN 10
ping 192.168.10.1  # OPNsense
ping 192.168.10.2  # Switch

# Ne doit PAS pouvoir atteindre VLAN 20 directement
ping 192.168.11.10  # Doit passer par OPNsense (firewall)
```

---

## Configuration switch non-manageable

### Switch simple (plug & play)

**Si vous utilisez un switch NON-manageable** :

1. **Brancher le switch sur OPNsense em1 (LAN_ADMIN)**
2. Brancher les PC Admin sur le switch
3. Tous les PC seront dans **192.168.10.0/24**
4. Configuration automatique, rien à faire

**Ou** :

1. **Brancher le switch sur OPNsense em2 (LAN_SERVERS)**
2. Brancher les serveurs Proxmox sur le switch
3. Tous les serveurs seront dans **192.168.11.0/24**
4. Configuration automatique

**⚠️ Attention** : Avec un switch non-manageable, vous ne pouvez pas mélanger les VLANs sur un même switch.

---

## Schéma récapitulatif

### Architecture actuelle (SANS switch, recommandée)

```
              [OPNsense]
                  │
    ┌─────────────┼─────────────┐
    │             │             │
  em0           em1           em2
 (WAN)        (ADMIN)      (SERVERS)
    │             │             │
[Internet]   [PC Admin]    [Proxmox]
```

**✅ Configuration terminée**

### Architecture avec switch simple

```
              [OPNsense]
                  │
    ┌─────────────┼─────────────┐
    │             │             │
  em0           em1           em2
 (WAN)        (ADMIN)      (SERVERS)
    │             │             │
[Internet]   [Switch]       [Switch]
              │   │           │   │
           [PC1][PC2]     [Srv1][Srv2]
```

**Aucune configuration switch nécessaire**

### Architecture avec switch manageable + VLANs

```
              [OPNsense]
                  │
         em0      │em1 (TRUNK)    em2
        (WAN) ────┴──────┐       (libre)
           │             │
      [Internet]    [Switch manageable]
                    VLAN 10 │ VLAN 20
                       │    │    │
                    [PC1] [PC2] [Srv1]
```

**Configuration VLANs requise**

---

## Checklist switch

### Sans switch (architecture actuelle)

- [ ] OPNsense em0 → Box FAI (WAN)
- [ ] OPNsense em1 → PC Admin direct (LAN_ADMIN)
- [ ] OPNsense em2 → Proxmox direct (LAN_SERVERS)
- [ ] Tests de connectivité réussis
- [ ] ✅ **Aucune autre action nécessaire**

### Avec switch NON-manageable

- [ ] Switch acheté (8-16 ports suffisant)
- [ ] Switch branché sur OPNsense em1 ou em2
- [ ] Appareils branchés sur le switch
- [ ] IPs fixes configurées sur chaque appareil
- [ ] Tests de connectivité réussis

### Avec switch manageable + VLANs

- [ ] Switch manageable disponible
- [ ] VLANs créés dans OPNsense (10, 20)
- [ ] VLANs assignés aux interfaces OPNsense
- [ ] Port TRUNK configuré sur le switch
- [ ] Ports ACCESS configurés (VLAN 10 / 20)
- [ ] Management IP configurée sur le switch
- [ ] Tests de connectivité réussis
- [ ] Isolation VLAN vérifiée

---

## Recommandation finale

**Pour votre infrastructure actuelle** :

🎯 **N'utilisez PAS de switch** tant que vous avez :
- 1 seul PC Admin
- 1 seul serveur Proxmox
- OPNsense avec 3 ports disponibles

**C'est la solution la plus simple, sûre et efficace.**

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini