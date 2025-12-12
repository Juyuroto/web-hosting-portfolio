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

## Sommaire

- [Résumé du projet](#résumé-du-projet)  
- [Architecture code](#archi-code)

## Pages

- [1. Configuration général](README_Config.md)
- [2. Configuration PC Admin](README_PC_Admin.md)
- [3. Configuration Switch](README_Switch.md)


## Archi code

```markdown
mon-projet/
├── docker/
│   ├── apache/
│   │   └── vhost.conf
│   └── php/
│       └── dockerfile
│
├── public/
│   ├── index.php
|   ├── .htaccess
|   ├── cv.pdf
|   └── assets/
|      ├── css/
|      |   ├── me.css
|      |   └── styles.css
|      ├── js/
|      |   ├── me.js
|      |   └── script.js
|      └── photo/
|          └── icons/
├── src/
│   ├── config/
│   │   └── database.php
│   ├── controllers/
│   │   ├── HomeController.php
│   │   └── ContactController.php
│   ├── models/
│   │   └── Contact.php
│   └── views/
│       ├── layouts/
|       |   ├── header.php
│       │   ├── main.php
│       │   └── footer.php
│       ├── home.php
│       └── me.php
│
├── .env
├── .gitignore
├── composer.json
├── docker-compose.yml
├── init.sql
└── README.md
```