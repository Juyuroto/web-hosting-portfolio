# PC Admin – Configuration complète sous Manjaro GNOME

## Objectif

Ce guide explique comment configurer un PC Admin pour gérer vos serveurs et VMs.  
Le PC Admin sera isolé dans le LAN, et permettra de :

- Gérer les fichiers et transferts (Double Commander / FileZilla)  
- Accéder à Internet et aux interfaces web (Firefox)  
- Superviser les machines virtuelles (Virt-Manager / Cockpit)  
- Lancer automatiquement les applications aux positions définies sur l’écran (style Windows)  

---

## Prérequis matériels et logiciels

- PC Admin avec Manjaro GNOME installé  
- Écran Full HD (1920x1080)  
- Connexion réseau vers LAN  
- Logiciels / packages :  
  - GNOME Desktop  
  - wmctrl  
  - Double Commander  
  - Firefox  
  - FileZilla  
  - Virt-Manager  
  - Cockpit / Glances (optionnel pour monitoring)  
  - GNOME Tweaks (optionnel)

---

## 1 - Installation de Manjaro GNOME

1. Télécharger l’ISO officielle de Manjaro GNOME  
2. Créer une clé USB bootable et démarrer dessus  
3. Installer Manjaro en mode GUI  
4. Vérifier que la connexion réseau fonctionne  
5. Mettre à jour le système :

```bash
sudo pacman -Syu
```

## 2 - Installation des applications essentielles

#### Gestion de fichiers
```bash
sudo pacman -S doublecmd-gtk2 filezilla
```

#### Navigateur Web
```bash
sudo pacman -S firefox
```

#### Machines virtuelles / Proxmox
```bash
sudo pacman -S virt-manager qemu-full
sudo systemctl enable --now libvirtd
```

#### Gestion des fenêtres pour autostart
```bash
sudo pacman -S wmctrl gnome-tweaks
```

## 3 - Création du script d’autostart

#### Créer le dossier ~/bin
```bash
mkdir -p ~/bin
```

#### Créer le script startup-apps.sh
```bash
nano ~/bin/startup-apps.sh
```

#### Contenu du script
```bash
#!/bin/bash
# Script d'autostart pour 4 applications en style Windows

move_window() {
    local WIN_NAME="$1"
    local X="$2"
    local Y="$3"
    local W="$4"
    local H="$5"

    # Attendre que la fenêtre existe avant de la déplacer
    while ! wmctrl -l | grep -q "$WIN_NAME"; do
        sleep 0.5
    done

    # Déplacer et redimensionner la fenêtre
    wmctrl -r "$WIN_NAME" -e 0,$X,$Y,$W,$H
}

# Double Commander → haut gauche
doublecmd --geometry 960x540+0+0 &

# Firefox → haut droite
firefox --geometry 960x540+960+0 &

# FileZilla → bas gauche
filezilla &
move_window "FileZilla" 0 540 960 540

# Virt-Manager → bas droite
virt-manager &
move_window "Virtual Machine Manager" 960 540 960 540
```

#### Rendre le script exécutable
```bash
chmod +x ~/bin/startup-apps.sh
```

## 4 - Lancer le script automatiquement au démarrage

#### Créer un fichier .desktop
```bash
mkdir -p ~/.config/autostart
nano ~/.config/autostart/startup-apps.desktop
```

#### CContenu du fichier
```bash
[Desktop Entry]
Type=Application
Name=Startup Apps
Exec=/home/TON_USER/bin/startup-apps.sh
X-GNOME-Autostart-enabled=true
```
- Remplacer TON_USER par ton nom d’utilisateur.