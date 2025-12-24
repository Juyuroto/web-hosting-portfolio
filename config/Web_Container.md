# Configuration Conteneur Web - Déploiement Portfolio

## Objectif

Configuration complète du conteneur LXC web pour héberger le portfolio PHP avec :
- Stack LAMP (Linux + Apache + MySQL + PHP)
- Sécurisation (UFW, Fail2Ban, SSL)
- Déploiement du code PHP
- Optimisations performances
- Mises à jour automatiques

---

## Sommaire

- [Prérequis](#prérequis)
- [Configuration initiale](#configuration-initiale)
- [Installation stack LAMP](#installation-stack-lamp)
- [Configuration Apache](#configuration-apache)
- [Configuration PHP](#configuration-php)
- [Configuration MySQL](#configuration-mysql)
- [Déploiement du site](#déploiement-du-site)
- [Sécurisation](#sécurisation)
- [SSL avec Let's Encrypt](#ssl-avec-lets-encrypt)
- [Optimisations](#optimisations)
- [Maintenance](#maintenance)

---

## Prérequis

```yaml
Conteneur: LXC 100 (web-portfolio)
OS: Debian 12
IP: 192.168.11.20/24
Gateway: 192.168.11.1
DNS: 1.1.1.1, 8.8.8.8
Accès SSH: Depuis PC Admin (192.168.10.10) uniquement
```

---

## Configuration initiale

### 1. Connexion au conteneur

**Depuis PC Admin** :

```bash
ssh root@192.168.11.20
```

Ou **depuis Proxmox** :

```bash
pct enter 100
```

### 2. Mise à jour du système

```bash
# Mettre à jour la liste des paquets
apt update

# Installer les mises à jour
apt upgrade -y

# Installer outils de base
apt install -y \
    curl \
    wget \
    git \
    vim \
    htop \
    net-tools \
    ca-certificates \
    gnupg \
    lsb-release \
    unattended-upgrades
```

### 3. Configurer le timezone

```bash
# Définir timezone Europe/Paris
timedatectl set-timezone Europe/Paris

# Vérifier
timedatectl
```

### 4. Configurer le hostname (optionnel)

```bash
# Vérifier hostname actuel
hostname

# Modifier si nécessaire
hostnamectl set-hostname web-portfolio

# Mettre à jour /etc/hosts
echo "127.0.0.1 web-portfolio" >> /etc/hosts
```

---

## Installation stack LAMP

### 1. Installer Apache

```bash
# Installer Apache
apt install -y apache2

# Activer et démarrer Apache
systemctl enable apache2
systemctl start apache2

# Vérifier le statut
systemctl status apache2

# Tester localement
curl http://localhost
```

### 2. Installer PHP

```bash
# Installer PHP et modules nécessaires
apt install -y \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-curl \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-intl \
    libapache2-mod-php8.2

# Vérifier l'installation
php -v

# Activer modules Apache pour PHP
a2enmod php8.2
systemctl restart apache2
```

### 3. Installer MySQL

```bash
# Installer MySQL Server
apt install -y mysql-server

# Activer et démarrer MySQL
systemctl enable mysql
systemctl start mysql

# Vérifier le statut
systemctl status mysql
```

### 4. Sécuriser MySQL

```bash
# Lancer l'assistant de sécurisation
mysql_secure_installation
```

Répondre aux questions :

```yaml
Set root password? Y
  → Définir un MOT DE PASSE FORT pour root MySQL

Remove anonymous users? Y
Disallow root login remotely? Y
Remove test database? Y
Reload privilege tables? Y
```

---

## Configuration Apache

### 1. Créer la structure du site

```bash
# Créer le dossier du site
mkdir -p /var/www/portfolio

# Créer un fichier de test
cat > /var/www/portfolio/index.html << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Portfolio - Alain Corazzini</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Portfolio en construction</h1>
    <p>Site web fonctionnel !</p>
</body>
</html>
EOF

# Permissions
chown -R www-data:www-data /var/www/portfolio
chmod -R 755 /var/www/portfolio
```

### 2. Créer le VirtualHost

```bash
# Créer le fichier de configuration
nano /etc/apache2/sites-available/portfolio.conf
```

Contenu :

```apache
<VirtualHost *:80>
    ServerName alain-corazzini.fr
    ServerAlias www.alain-corazzini.fr
    ServerAdmin admin@alain-corazzini.fr
    
    DocumentRoot /var/www/portfolio
    
    <Directory /var/www/portfolio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/portfolio_error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio_access.log combined
    
    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</VirtualHost>
```

### 3. Activer le site et modules

```bash
# Activer modules Apache nécessaires
a2enmod rewrite
a2enmod headers
a2enmod ssl

# Désactiver le site par défaut
a2dissite 000-default.conf

# Activer notre site
a2ensite portfolio.conf

# Vérifier la syntaxe
apache2ctl configtest

# Recharger Apache
systemctl reload apache2
```

### 4. Tester le site

**Depuis PC Admin** :

```bash
# Tester avec curl
curl http://192.168.11.20

# Tester avec navigateur
firefox http://192.168.11.20
```

Vous devriez voir "Portfolio en construction".

---

## Configuration PHP

### 1. Optimiser php.ini

```bash
# Éditer la configuration PHP
nano /etc/php/8.2/apache2/php.ini
```

Modifier les valeurs suivantes :

```ini
; Limites mémoire
memory_limit = 256M
max_execution_time = 60
max_input_time = 60

; Upload
upload_max_filesize = 10M
post_max_size = 12M

; Sécurité
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Sessions
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

; Timezone
date.timezone = Europe/Paris
```

### 2. Créer le dossier de logs PHP

```bash
mkdir -p /var/log/php
chown www-data:www-data /var/log/php
```

### 3. Tester PHP

```bash
# Créer un fichier de test
cat > /var/www/portfolio/info.php << 'EOF'
<?php
phpinfo();
?>
EOF

# Tester
curl http://192.168.11.20/info.php | grep "PHP Version"

# ⚠️ Supprimer après test (sécurité)
rm /var/www/portfolio/info.php
```

### 4. Redémarrer Apache

```bash
systemctl restart apache2
```

---

## Configuration MySQL

### 1. Créer la base de données

```bash
# Se connecter à MySQL
mysql -u root -p
```

Entrer le mot de passe root MySQL défini précédemment.

```sql
-- Créer la base de données
CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Créer l'utilisateur
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT_ICI';

-- Accorder les privilèges
GRANT ALL PRIVILEGES ON portfolio_db.* TO 'portfolio_user'@'localhost';

-- Appliquer les changements
FLUSH PRIVILEGES;

-- Vérifier
SHOW DATABASES;
SELECT User, Host FROM mysql.user;

-- Quitter
EXIT;
```

### 2. Créer les tables (exemple)

```bash
# Se connecter avec le nouvel utilisateur
mysql -u portfolio_user -p portfolio_db
```

Exemple de table pour formulaire de contact :

```sql
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vérifier
SHOW TABLES;
DESCRIBE contacts;

EXIT;
```

### 3. Configuration pour accès distant (si nécessaire)

**⚠️ Uniquement depuis PC Admin pour administration** :

```bash
nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Modifier :

```ini
# Au lieu de
bind-address = 127.0.0.1

# Mettre (pour accepter connexions depuis LAN_ADMIN)
bind-address = 0.0.0.0
```

Créer un utilisateur admin distant :

```sql
mysql -u root -p

CREATE USER 'admin'@'192.168.10.10' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON portfolio_db.* TO 'admin'@'192.168.10.10';
FLUSH PRIVILEGES;
EXIT;
```

Redémarrer MySQL :

```bash
systemctl restart mysql
```

**N'oubliez pas d'autoriser le port 3306 dans OPNsense** (voir OPNsense.md).

---

## Déploiement du site

### 1. Structure recommandée

```bash
/var/www/portfolio/
├── index.php              # Page d'accueil
├── about.php              # Page à propos
├── contact.php            # Formulaire de contact
├── .htaccess              # Règles Apache
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── images/
│       └── logo.png
├── config/
│   └── database.php       # Configuration BDD
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
└── uploads/               # Si besoin d'uploads
```

### 2. Méthode 1 : Copie via SCP (depuis PC Admin)

**Préparer les fichiers sur PC Admin** :

```bash
# Depuis PC Admin
cd ~/projects/portfolio

# Copier vers le serveur
scp -r * root@192.168.11.20:/var/www/portfolio/
```

### 3. Méthode 2 : Git (recommandé)

**Sur le conteneur** :

```bash
# Installer Git si pas déjà fait
apt install -y git

# Cloner le repository
cd /var/www
rm -rf portfolio
git clone https://github.com/votre-username/portfolio.git

# Ou si repository privé
git clone https://votre-token@github.com/votre-username/portfolio.git
```

### 4. Configuration base de données

```bash
# Créer le fichier de configuration
nano /var/www/portfolio/config/database.php
```

Contenu :

```php
<?php
// Configuration base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'portfolio_user');
define('DB_PASS', 'MOT_DE_PASSE_BDD_ICI');

// Connexion PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Erreur de connexion à la base de données.");
}
?>
```

**⚠️ Sécuriser ce fichier** :

```bash
chmod 600 /var/www/portfolio/config/database.php
chown www-data:www-data /var/www/portfolio/config/database.php
```

### 5. Permissions finales

```bash
# Propriétaire : www-data
chown -R www-data:www-data /var/www/portfolio

# Permissions : 755 pour dossiers, 644 pour fichiers
find /var/www/portfolio -type d -exec chmod 755 {} \;
find /var/www/portfolio -type f -exec chmod 644 {} \;

# Dossier uploads (si nécessaire)
mkdir -p /var/www/portfolio/uploads
chmod 775 /var/www/portfolio/uploads
chown www-data:www-data /var/www/portfolio/uploads
```

### 6. Fichier .htaccess (optionnel)

```bash
nano /var/www/portfolio/.htaccess
```

Contenu :

```apache
# Activer la réécriture d'URL
RewriteEngine On

# Rediriger HTTP vers HTTPS (après installation SSL)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protection des fichiers sensibles
<FilesMatch "^(database\.php|\.env|\.git)">
    Require all denied
</FilesMatch>

# Désactiver l'affichage des dossiers
Options -Indexes

# Protection XSS
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header always append X-Frame-Options SAMEORIGIN
    Header set X-Content-Type-Options nosniff
</IfModule>
```

---

## Sécurisation

### 1. Installer et configurer UFW (firewall)

```bash
# Installer UFW
apt install -y ufw

# Autoriser SSH depuis PC Admin uniquement
ufw allow from 192.168.10.10 to any port 22 proto tcp

# Autoriser HTTP (depuis n'importe où)
ufw allow 80/tcp

# Autoriser HTTPS (depuis n'importe où)
ufw allow 443/tcp

# Politique par défaut : bloquer entrées, autoriser sorties
ufw default deny incoming
ufw default allow outgoing

# Activer UFW
ufw enable

# Vérifier
ufw status verbose
```

### 2. Installer et configurer Fail2Ban

```bash
# Installer Fail2Ban
apt install -y fail2ban

# Créer une configuration personnalisée
nano /etc/fail2ban/jail.local
```

Contenu :

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
destemail = admin@alain-corazzini.fr
sendername = Fail2Ban
action = %(action_mwl)s

[sshd]
enabled = true
port = 22
logpath = /var/log/auth.log
maxretry = 3

[apache-auth]
enabled = true
port = http,https
logpath = /var/log/apache2/error.log
maxretry = 5

[apache-badbots]
enabled = true
port = http,https
logpath = /var/log/apache2/access.log
maxretry = 3

[apache-noscript]
enabled = true
port = http,https
logpath = /var/log/apache2/error.log
```

Redémarrer Fail2Ban :

```bash
systemctl enable fail2ban
systemctl restart fail2ban

# Vérifier
fail2ban-client status
fail2ban-client status sshd
```

### 3. Mises à jour automatiques

```bash
# Configurer unattended-upgrades
nano /etc/apt/apt.conf.d/50unattended-upgrades
```

Décommenter :

```
Unattended-Upgrade::Automatic-Reboot "false";
Unattended-Upgrade::Mail "admin@alain-corazzini.fr";
```

Activer :

```bash
dpkg-reconfigure -plow unattended-upgrades
# Sélectionner "Yes"
```

### 4. Désactiver root SSH (optionnel, recommandé)

**Créer un utilisateur admin d'abord** :

```bash
# Créer utilisateur
adduser admin

# Ajouter au groupe sudo
usermod -aG sudo admin

# Tester la connexion depuis PC Admin
ssh admin@192.168.11.20
```

**Puis désactiver root** :

```bash
nano /etc/ssh/sshd_config
```

Modifier :

```
PermitRootLogin no
```

Redémarrer SSH :

```bash
systemctl restart sshd
```

---

## SSL avec Let's Encrypt

### Prérequis

⚠️ **Avant de continuer** :
- Le domaine `alain-corazzini.fr` doit pointer vers votre IP publique
- Les ports 80/443 doivent être ouverts sur OPNsense (NAT configuré)
- Le site doit être accessible depuis Internet

### 1. Installer Certbot

```bash
# Installer Certbot et plugin Apache
apt install -y certbot python3-certbot-apache
```

### 2. Obtenir le certificat

```bash
# Lancer Certbot
certbot --apache -d alain-corazzini.fr -d www.alain-corazzini.fr
```

Répondre aux questions :

```
Email: admin@alain-corazzini.fr
Terms of Service: Agree (A)
Share email: No (N)
Redirect HTTP to HTTPS: Yes (2)
```

Certbot va :
1. Valider que vous contrôlez le domaine
2. Obtenir le certificat
3. Configurer Apache automatiquement
4. Créer un VirtualHost HTTPS

### 3. Vérifier la configuration SSL

```bash
# Voir les VirtualHosts actifs
apache2ctl -S

# Tester le certificat
openssl s_client -connect alain-corazzini.fr:443 -servername alain-corazzini.fr < /dev/null
```

**Tester depuis navigateur** :

```
https://alain-corazzini.fr
```

Vérifier le cadenas vert et le certificat valide.

### 4. Renouvellement automatique

Certbot configure automatiquement un cron job pour renouveler les certificats.

**Vérifier** :

```bash
# Tester le renouvellement (dry-run)
certbot renew --dry-run

# Voir les certificats installés
certbot certificates
```

Le renouvellement se fait automatiquement via :

```bash
systemctl list-timers | grep certbot
```

---

## Optimisations

### 1. Activer la compression Gzip

```bash
# Activer module deflate
a2enmod deflate

# Configurer
nano /etc/apache2/mods-available/deflate.conf
```

Ajouter :

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript
    AddOutputFilterByType DEFLATE application/javascript application/x-javascript
    AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>
```

Redémarrer Apache :

```bash
systemctl restart apache2
```

### 2. Activer le cache navigateur

```bash
# Activer module expires
a2enmod expires

# Configurer dans le VirtualHost
nano /etc/apache2/sites-available/portfolio.conf
```

Ajouter dans le `<VirtualHost>` :

```apache
    # Cache navigateur
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 year"
        ExpiresByType image/jpeg "access plus 1 year"
        ExpiresByType image/png "access plus 1 year"
        ExpiresByType image/gif "access plus 1 year"
        ExpiresByType image/webp "access plus 1 year"
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType application/javascript "access plus 1 month"
        ExpiresByType image/x-icon "access plus 1 year"
    </IfModule>
```

Redémarrer Apache :

```bash
systemctl reload apache2
```

### 3. Optimiser MySQL

```bash
nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Ajouter/modifier :

```ini
[mysqld]
# Performance
max_connections = 50
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2

# Requêtes lentes (debug)
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

Redémarrer MySQL :

```bash
systemctl restart mysql
```

### 4. Limiter les ressources Apache

```bash
nano /etc/apache2/mods-available/mpm_prefork.conf
```

Adapter selon RAM disponible (2 GB ici) :

```apache
<IfModule mpm_prefork_module>
    StartServers 2
    MinSpareServers 2
    MaxSpareServers 5
    MaxRequestWorkers 50
    MaxConnectionsPerChild 1000
</IfModule>
```

Redémarrer Apache :

```bash
systemctl restart apache2
```

---

## Maintenance

### Logs à surveiller

```bash
# Logs Apache
tail -f /var/log/apache2/portfolio_access.log
tail -f /var/log/apache2/portfolio_error.log

# Logs PHP
tail -f /var/log/php/error.log

# Logs MySQL
tail -f /var/log/mysql/error.log

# Logs système
tail -f /var/log/syslog

# Logs Fail2Ban
tail -f /var/log/fail2ban.log
```

### Sauvegardes base de données

```bash
# Créer un script de backup
nano /root/backup-db.sh
```

Contenu :

```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

mysqldump -u portfolio_user -p'MOT_DE_PASSE_BDD' portfolio_db \
    > $BACKUP_DIR/portfolio_db_$DATE.sql

# Garder seulement 7 derniers backups
find $BACKUP_DIR -name "portfolio_db_*.sql" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR/portfolio_db_$DATE.sql"
```

Rendre exécutable :

```bash
chmod +x /root/backup-db.sh
```

Automatiser avec cron :

```bash
crontab -e

# Ajouter (backup quotidien à 3h du matin)
0 3 * * * /root/backup-db.sh >> /var/log/backup-db.log 2>&1
```

### Monitoring ressources

```bash
# CPU et RAM
htop

# Espace disque
df -h

# Processus Apache
ps aux | grep apache2 | wc -l

# Connexions MySQL
mysql -u root -p -e "SHOW PROCESSLIST;"

# Statistiques Apache
apachectl status
```

---

## Checklist finale

- [ ] Stack LAMP installée (Apache + MySQL + PHP)
- [ ] Site accessible localement (http://192.168.11.20)
- [ ] Base de données créée et configurée
- [ ] Code PHP déployé dans /var/www/portfolio
- [ ] Permissions correctes (www-data:www-data)
- [ ] UFW configuré et actif
- [ ] Fail2Ban configuré et actif
- [ ] Mises à jour automatiques activées
- [ ] SSL Let's Encrypt installé et fonctionnel
- [ ] Site accessible depuis Internet (https://alain-corazzini.fr)
- [ ] Headers de sécurité configurés
- [ ] Compression Gzip activée
- [ ] Cache navigateur configuré
- [ ] Backups automatiques configurés
- [ ] Logs consultables et surveillés

---

**Version** : 1.1  
**Dernière mise à jour** : 2025-24-12  
**Auteur** : Alain Corazzini