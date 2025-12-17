# Configuration OPNsense

## Sommaire

- [Installation OPNsense](#installation-opnsense)
- [Configuration des Interfaces](#configuration-des-interfaces)
- [Configuration des VLANs](#configuration-des-vlans)
- [Règles Firewall](#règles-firewall)
- [Configuration NAT](#configuration-nat)
- [Services Additionnels](#services-additionnels)

## Pages

- [0. Site](../README.md)
- [1. Configuration général](Infra.md)
- [3. Configuration PC Admin](PC_Admin.md)
- [4. Configuration Switch](Switch.md)

---

## Installation OPNsense

### Prérequis

- Clé USB bootable avec l'image OPNsense
- Machine avec 3 interfaces réseau minimum (WAN, LAN, DMZ)
- 4 GB RAM minimum
- 40 GB de stockage minimum

### Étapes d'installation

1. Démarrer sur la clé USB OPNsense
2. Suivre l'installation standard
3. Configurer le login root
4. Redémarrer la machine

### Premier accès

- Interface web : `https://10.0.0.1`
- Login par défaut : `root` / `opnsense`
- **Changer immédiatement le mot de passe**

---

## Configuration des Interfaces

### WAN (Interface vers Internet)

**Interfaces → Assignments**

- Assigner l'interface physique (ex: `em0`)
- **Interfaces → WAN**
  - Enable interface : ✓
  - IPv4 Configuration Type : `DHCP`
  - Block private networks : ✓
  - Block bogon networks : ✓

### LAN (Réseau interne)

**Interfaces → LAN**

- Enable interface : ✓
- IPv4 Configuration Type : `Static IPv4`
- IPv4 address : `10.0.0.1/24`
- Description : `LAN - Réseau interne`

### DMZ (Zone démilitarisée)

**Interfaces → Assignments**

1. Cliquer sur `+` pour ajouter une nouvelle interface
2. Assigner l'interface physique (ex: `em2`)

**Interfaces → DMZ**

- Enable interface : ✓
- IPv4 Configuration Type : `Static IPv4`
- IPv4 address : `10.0.10.1/24`
- Description : `DMZ - Serveurs exposés`

---

## Configuration des VLANs

**Interfaces → Other Types → VLAN**

### Créer les VLANs

Cliquer sur `+` pour chaque VLAN :

#### VLAN 10 - LAN

- Parent interface : Interface connectée au switch (ex: `em1`)
- VLAN tag : `10`
- VLAN priority : `0`
- Description : `VLAN_LAN`

#### VLAN 20 - DMZ

- Parent interface : Interface connectée au switch (ex: `em1`)
- VLAN tag : `20`
- VLAN priority : `0`
- Description : `VLAN_DMZ`

#### VLAN 30 - MGMT (Optionnel)

- Parent interface : Interface connectée au switch (ex: `em1`)
- VLAN tag : `30`
- VLAN priority : `0`
- Description : `VLAN_MGMT`

### Assigner les VLANs aux interfaces

**Interfaces → Assignments**

1. Dans le menu déroulant, sélectionner `VLAN 10 (em1)` → Cliquer sur `+`
2. Renommer en `LAN` si pas déjà fait
3. Répéter pour `VLAN 20` → Renommer en `DMZ`
4. Répéter pour `VLAN 30` → Renommer en `MGMT`

**Configurer chaque interface VLAN**

- **LAN (VLAN 10)** : `10.0.0.1/24`
- **DMZ (VLAN 20)** : `10.0.10.1/24`
- **MGMT (VLAN 30)** : `10.0.30.1/24`

---

## Règles Firewall

**Firewall → Rules**

### WAN (Interface Internet)

**Par défaut : Tout bloqué sauf ce qui est explicitement autorisé**

#### Règle 1 : Autoriser HTTP vers Serveur Portfolio

- Action : `Pass`
- Interface : `WAN`
- Protocol : `TCP`
- Source : `any`
- Destination : `10.0.10.10` (Serveur Portfolio)
- Destination port : `80 (HTTP)`
- Description : `Allow HTTP to Portfolio`

#### Règle 2 : Autoriser HTTPS vers Serveur Portfolio

- Action : `Pass`
- Interface : `WAN`
- Protocol : `TCP`
- Source : `any`
- Destination : `10.0.10.10`
- Destination port : `443 (HTTPS)`
- Description : `Allow HTTPS to Portfolio`

#### Règle 3 : Bloquer tout le reste (implicite)

---

### LAN (Réseau interne)

#### Règle 1 : Autoriser tout vers Internet

- Action : `Pass`
- Interface : `LAN`
- Protocol : `any`
- Source : `LAN net`
- Destination : `any`
- Description : `LAN to Internet`

#### Règle 2 : Autoriser LAN → DMZ (SSH, HTTP, HTTPS)

- Action : `Pass`
- Interface : `LAN`
- Protocol : `TCP`
- Source : `LAN net`
- Destination : `DMZ net`
- Destination port : `22, 80, 443`
- Description : `LAN admin to DMZ servers`

#### Règle 3 (Optionnel) : Autoriser MySQL depuis PC Admin

- Action : `Pass`
- Interface : `LAN`
- Protocol : `TCP`
- Source : `10.0.0.10` (PC Admin)
- Destination : `10.0.10.10` ou `10.0.10.20`
- Destination port : `3306`
- Description : `PC Admin to MySQL`

---

### DMZ (Zone démilitarisée)

#### Règle 1 : Autoriser DNS

- Action : `Pass`
- Interface : `DMZ`
- Protocol : `UDP`
- Source : `DMZ net`
- Destination : `any`
- Destination port : `53 (DNS)`
- Description : `DMZ DNS queries`

#### Règle 2 : Autoriser NTP

- Action : `Pass`
- Interface : `DMZ`
- Protocol : `UDP`
- Source : `DMZ net`
- Destination : `any`
- Destination port : `123 (NTP)`
- Description : `DMZ NTP sync`

#### Règle 3 : Autoriser HTTP sortant

- Action : `Pass`
- Interface : `DMZ`
- Protocol : `TCP`
- Source : `DMZ net`
- Destination : `any`
- Destination port : `80 (HTTP)`
- Description : `DMZ HTTP out (updates)`

#### Règle 4 : Autoriser HTTPS sortant

- Action : `Pass`
- Interface : `DMZ`
- Protocol : `TCP`
- Source : `DMZ net`
- Destination : `any`
- Destination port : `443 (HTTPS)`
- Description : `DMZ HTTPS out (updates)`

#### Règle 5 : **BLOQUER DMZ → LAN**

- Action : `Block`
- Interface : `DMZ`
- Protocol : `any`
- Source : `DMZ net`
- Destination : `LAN net`
- Description : `Block DMZ to LAN`
- **Placer cette règle en HAUT de la liste DMZ**

---

## Configuration NAT

**Firewall → NAT → Port Forward**

### Redirection HTTP vers Serveur Portfolio

Cliquer sur `+` pour ajouter une règle :

- Interface : `WAN`
- Protocol : `TCP`
- Destination : `WAN address`
- Destination port : `80 (HTTP)`
- Redirect target IP : `10.0.10.10`
- Redirect target port : `80 (HTTP)`
- Description : `NAT HTTP to Portfolio`
- Filter rule association : `Add associated filter rule`

### Redirection HTTPS vers Serveur Portfolio

- Interface : `WAN`
- Protocol : `TCP`
- Destination : `WAN address`
- Destination port : `443 (HTTPS)`
- Redirect target IP : `10.0.10.10`
- Redirect target port : `443 (HTTPS)`
- Description : `NAT HTTPS to Portfolio`
- Filter rule association : `Add associated filter rule`

### Redirection SSH vers Serveur Portfolio (Optionnel - Non recommandé)

Si vraiment nécessaire, changer le port :

- Interface : `WAN`
- Protocol : `TCP`
- Destination : `WAN address`
- Destination port : `2222` (port externe)
- Redirect target IP : `10.0.10.10`
- Redirect target port : `22` (port SSH)
- Description : `NAT SSH to Portfolio (port 2222)`
- ⚠️ **Sécurité : Activer uniquement si nécessaire**

---

## Services Additionnels

### DHCP Server

**Services → DHCPv4 → [LAN]**

- Enable DHCP server on LAN interface : ✓
- Range : `10.0.0.50` to `10.0.0.200`
- DNS servers : `1.1.1.1`, `8.8.8.8`
- Gateway : `10.0.0.1`

**Services → DHCPv4 → [DMZ]**

- **Désactiver** (les serveurs DMZ ont des IPs fixes)

### DNS Resolver

**Services → Unbound DNS → General**

- Enable Unbound : ✓
- Listen Port : `53`
- Network Interfaces : `LAN`, `DMZ`
- Outgoing Network Interfaces : `WAN`
- DNSSEC : ✓

### NTP Server

**Services → Network Time → General**

- Enable NTP server : ✓
- Interface : `LAN`, `DMZ`
- NTP Servers :
  - `0.opnsense.pool.ntp.org`
  - `1.opnsense.pool.ntp.org`

---

## Sécurité Additionnelle

### IDS/IPS (Suricata)

**Services → Intrusion Detection → Administration**

- Enable IDS : ✓
- IPS mode : ✓
- Interfaces : `WAN`, `DMZ`
- Pattern matcher : `Hyperscan`

**Download Rules :**

- Ruleset : `ET Open` (gratuit)
- Update rules daily

### Fail2Ban (Plugin requis)

**System → Firmware → Plugins**

- Installer `os-fail2ban`

**Services → Fail2Ban → Settings**

- Enable : ✓
- Jails à activer :
  - `sshd`
  - `nginx-http-auth`
  - `nginx-limit-req`

### Logs & Monitoring

**System → Settings → Logging**

- Enable : ✓
- Log level : `Informational`
- Log rotation : `7 days`

**System → Settings → Administration**

- Protocol : `HTTPS`
- SSL Certificate : Générer un certificat auto-signé ou Let's Encrypt
- Login Protection : ✓
- Block connections from : `WAN` (n'autoriser l'accès webGUI que depuis LAN)

---

## Vérification de la Configuration

### Tests à effectuer

#### Test 1 : Connectivité Internet depuis LAN
```bash
# Depuis PC Admin (10.0.0.10)
ping 8.8.8.8
ping google.com
```

#### Test 2 : Accès SSH depuis LAN vers DMZ
```bash
# Depuis PC Admin
ssh admin@10.0.10.10
```

#### Test 3 : Blocage DMZ → LAN
```bash
# Depuis Serveur DMZ (10.0.10.10)
ping 10.0.0.10  # Doit échouer
```

#### Test 4 : Accès Internet depuis DMZ
```bash
# Depuis Serveur DMZ
ping 8.8.8.8
curl https://google.com
```

#### Test 5 : Accès externe au Portfolio
```bash
# Depuis un appareil externe (4G par exemple)
curl http://[votre_IP_publique]
```

### Commandes de diagnostic OPNsense

**Diagnostics → Ping**
- Test de connectivité vers différentes cibles

**Diagnostics → Packet Capture**
- Analyser le trafic en temps réel

**Firewall → Log Files → Live View**
- Voir les connexions bloquées/autorisées en temps réel

---

## Sauvegarde de la Configuration

**System → Configuration → Backups**

1. Cliquer sur `Download configuration`
2. Sauvegarder le fichier XML
3. Automatiser les backups :
   - **System → Configuration → Backups → Automatic backup**
   - Enable : ✓
   - Backup Count : `30`

**Restauration**

**System → Configuration → Backups**

1. Cliquer sur `Restore configuration`
2. Sélectionner le fichier XML
3. Redémarrer OPNsense

---

## Mises à jour

**System → Firmware → Updates**

- Vérifier les mises à jour régulièrement
- Appliquer les patches de sécurité
- Redémarrer si nécessaire

**System → Firmware → Plugins**

- Plugins recommandés :
  - `os-acme-client` (Let's Encrypt)
  - `os-fail2ban`
  - `os-haproxy` (si load balancing)
  - `os-net-snmp` (monitoring SNMP)

---

## Résumé de la Configuration

| Élément | Valeur |
|---------|--------|
| **WAN** | DHCP (depuis Box FAI) |
| **LAN** | 10.0.0.1/24 (VLAN 10) |
| **DMZ** | 10.0.10.1/24 (VLAN 20) |
| **MGMT** | 10.0.30.1/24 (VLAN 30) |
| **Serveur Portfolio** | 10.0.10.10 |
| **Serveur Backup** | 10.0.10.20 |
| **PC Admin** | 10.0.0.10 |
| **Switch** | 10.0.0.2 |
| **Ports ouverts WAN** | 80, 443 → 10.0.10.10 |
| **DNS** | 1.1.1.1, 8.8.8.8 |