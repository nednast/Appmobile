# AppMobile

> Application mobile hybride — Réseau social & Petites annonces géolocalisées
> IUT BUT2 Informatique | Semestre 4 | 2025–2026

---

## Stack technique

| Couche | Technologie | Version |
|---|---|---|
| Framework frontend | Nuxt | 4.3.1 |
| UI | Vue | 3.5.28 |
| CSS | Tailwind CSS | 4.0.0 |
| Bridge mobile | Capacitor | 8.1.0 |
| Plateforme mobile | Android (Gradle) | 8.9.1 |
| Backend | Laravel | 12.0 |
| Auth API | Laravel Sanctum | 4.3 |
| Base de données | MySQL | 8 |
| Runtime PHP | PHP | 8.3 |
| Conteneurs | Docker Compose | — |
| Cartographie | Google Maps API | — |

---

## Architecture

```
appmobile/
├── api/              # Backend Laravel (REST API)
├── frontend/         # Frontend Nuxt 4 + projet Android (Capacitor)
└── docker/           # Dockerfiles et config serveur
```

---

## Prérequis

| Outil | Version minimale | Usage |
|---|---|---|
| Docker + Docker Compose | 24+ | Orchestration API + BDD |
| Node.js | 20+ | Frontend Nuxt |
| npm | 10+ | Gestion des packages JS |
| Android Studio | Ladybug+ | Build APK / émulation |
| Java JDK | 17 | Compilation Android |
| PHP | 8.2+ | Dev local sans Docker |
| Composer | 2+ | Dépendances PHP |

---

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-repo>
cd appmobile
```

### 2. Variables d'environnement

**Backend** — copier et adapter :
```bash
cp api/.env.example api/.env
```

Valeurs clés à vérifier dans `api/.env` :
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=app
DB_USERNAME=user
DB_PASSWORD=password
```

**Frontend** — créer `frontend/.env` :
```env
APP_NAME=AppMobile

# Pour le navigateur web
APP_ENV=web
WEBAPI_URL=http://localhost:8000

# Pour l'émulateur Android (bridge vers host)
APP_ENV=mobile
APPAPI_URL=http://10.0.2.2:8000

GOOGLE_MAPS_API_KEY=<votre-clé>
```

> **Important :** basculer `APP_ENV` entre `web` (navigateur) et `mobile` (émulateur) selon la cible de build.

---

## Lancement — Mode développement (Docker)

### Démarrer tous les services

```bash
docker compose up -d
```

| Service | URL | Description |
|---|---|---|
| API Laravel | http://localhost:8000 | REST API |
| Frontend Nuxt | http://localhost:3000 | Interface web |
| phpMyAdmin | http://localhost:8080 | Gestion BDD |
| MySQL | port 3306 | Base de données |

### Initialiser le backend (première fois uniquement)

```bash
# Installer les dépendances PHP
docker compose exec api composer install

# Générer la clé d'application
docker compose exec api php artisan key:generate

# Exécuter les migrations
docker compose exec api php artisan migrate

# (Optionnel) Peupler la BDD avec des données de test
docker compose exec api php artisan db:seed
```

### Initialiser le frontend (première fois uniquement)

```bash
docker compose exec frontend npm install
```

### Arrêter les services

```bash
docker compose down
```

Pour supprimer aussi les volumes (reset BDD) :
```bash
docker compose down -v
```

---

## Lancement — Mode développement (local, sans Docker)

### Backend

```bash
cd api
composer install
cp .env.example .env          # Adapter DB_HOST=127.0.0.1 et credentials MySQL locaux
php artisan key:generate
php artisan migrate
php artisan db:seed           # Optionnel
composer dev                  # Lance PHP server + queue + logs en parallèle
```

### Frontend

```bash
cd frontend
npm install
npm run dev                   # Serveur dev sur http://localhost:3000
```

---

## Build Android

### Vue d'ensemble du flux

```
nuxt build  →  cap sync android  →  rsync vers Windows  →  Android Studio
```

### Étapes

**1. Construire le frontend et synchroniser Capacitor**

```bash
cd frontend
npm run android
```

Ce script effectue :
- `nuxt build` — génère le bundle statique dans `.output/public/`
- `npx cap sync android` — copie les assets web dans le projet Android
- `rsync` — synchronise les fichiers vers le répertoire Android Studio sur Windows

**2. Ouvrir dans Android Studio**

Ouvrir le dossier :
```
C:\Users\<user>\appmobile-tp\android\
```

**3. Lancer sur émulateur ou appareil**

- Émulateur : utiliser `APP_ENV=mobile` + `APPAPI_URL=http://10.0.2.2:8000`
- Appareil physique : utiliser l'IP locale de la machine (ex. `192.168.x.x:8000`)

> **Attention cleartext :** `capacitor.config.json` autorise les connexions HTTP non-chiffrées (`allowMixedContent: true`) pour le développement local. Ne pas utiliser en production.

### Générer un APK de debug

Dans Android Studio : **Build → Build Bundle(s) / APK(s) → Build APK(s)**

---

## Fonctionnalités

### Social
- Inscription / Connexion (tokens Sanctum)
- Gestion du profil (avatar, nom, email, mot de passe)
- Publications (CRUD avec images)
- Commentaires sur les publications
- Likes / Unlikes
- Notifications locales push (Capacitor)
- Partage de publications

### Petites annonces
- Création et gestion d'annonces géolocalisées
- Recherche par distance (fonction MySQL personnalisée)
- Affichage sur carte Google Maps

### Mobile natif (Capacitor)
- Accès caméra (`@capacitor/camera`)
- Géolocalisation GPS (`@capacitor/geolocation`)
- Notifications locales (`@capacitor/local-notifications`)
- Partage natif (`@capacitor/share`)
- Google Maps natif (`@capacitor/google-maps`)

---

## API — Endpoints principaux

### Publics

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/ping` | Health check |
| POST | `/api/login` | Connexion |
| GET | `/api/posts` | Liste des publications |
| GET | `/api/posts/{id}` | Détail d'une publication |
| GET | `/api/posts/{id}/comments` | Commentaires |
| GET | `/api/ads` | Liste des annonces |

### Protégés (Bearer token Sanctum)

| Méthode | Route | Description |
|---|---|---|
| POST | `/api/logout` | Déconnexion |
| GET/PUT | `/api/user` | Profil utilisateur |
| POST | `/api/user/avatar` | Upload avatar |
| POST | `/api/user/posts` | Créer une publication |
| DELETE | `/api/user/posts/{id}` | Supprimer une publication |
| POST | `/api/posts/{id}/like` | Liker / Unliker |
| POST | `/api/posts/{id}/comments` | Ajouter un commentaire |
| POST | `/api/user/ads` | Créer une annonce |

---

## Schéma base de données

```
users
  └─ posts        (has many)
       ├─ likes   (has many)
       └─ comments (has many)
  └─ ads          (has many, avec lat/lng)
```

Migrations dans `api/database/migrations/` — incluent une **fonction SQL `distance()`** pour les requêtes géospatiales.

---

## Permissions Android

Déclarées dans `AndroidManifest.xml` :

```xml
INTERNET
ACCESS_FINE_LOCATION
ACCESS_COARSE_LOCATION
CAMERA
READ_EXTERNAL_STORAGE
WRITE_EXTERNAL_STORAGE
```

---

## Notes importantes

| Point | Détail |
|---|---|
| **API URL mobile** | L'émulateur Android accède au host via `10.0.2.2` et non `localhost` |
| **APP_ENV** | Basculer entre `web` et `mobile` avant chaque build selon la cible |
| **Google Maps** | La clé API est requise dans `frontend/.env` ET dans `AndroidManifest.xml` |
| **Rsync Windows** | Le script `npm run android` suppose un path Windows fixe — adapter si nécessaire |
| **Cleartext HTTP** | Activé uniquement pour le dev local dans `capacitor.config.json` |
| **Sanctum CORS** | Configurer `api/config/cors.php` si le domaine frontend change |

---

## Simulation des notifications (correction)

Les notifications locales sont déclenchées automatiquement par des actions utilisateur. Pour les tester :

### Sur navigateur web (développement)

Le navigateur demande la permission au premier déclenchement. Accepter l'invite.

| Action | Notification déclenchée |
|---|---|
| Liker un post | « Vous avez aimé le post » |
| Commenter un post d'un autre utilisateur | « \<Prénom Nom\> a commenté votre post » |

### Sur Android (émulateur ou appareil)

Les permissions `POST_NOTIFICATIONS` sont demandées automatiquement au premier appel. Si elles ne s'affichent pas :

1. Ouvrir **Paramètres → Applications → AppMobile → Notifications**
2. Activer les notifications
3. Revenir dans l'app et effectuer une action (like ou commentaire)

### Vérification rapide

```
1. Se connecter avec un compte A
2. Aller sur un post publié par un autre utilisateur
3. Liker le post → notification « like » apparaît
4. Écrire un commentaire → notification « commentaire » apparaît (si post d'un autre user)
```

> Les notifications sont déclenchées côté client uniquement (Capacitor Local Notifications). Elles simulent une notification push sans serveur FCM.

---

## Checklist fonctionnelle

| # | Fonctionnalité | Statut | Notes |
|---|---|---|---|
| — | Accès GitHub | ✅ | Dépôt accessible |
| — | Récupération de l'App | ✅ | `git clone` |
| — | Installation en local | ✅ | Docker Compose |
| **1** | **Authentification / Compte** | | |
| 1.1 | Inscription | ✅ | `POST /api/register` + page `/register` |
| 1.2 | Connexion | ✅ | `POST /api/login` + Sanctum |
| 1.3 | MAJ Profil | ✅ | Nom, prénom, email |
| 1.4 | MAJ Mot de passe | ✅ | Avec vérification actuel |
| 1.5 | Upload Avatar => Capacitor | ✅ | `Camera.getPhoto()` sur mobile |
| **2** | **Gestion des Posts** | | |
| 2.1 | Listing des posts avec pagination | ✅ | 10 par page, contrôles prev/next |
| 2.2 | Détail d'un post | ✅ | Page `/posts/[id]` |
| 2.3 | Partage d'un post => Capacitor | ✅ | `Share.share()` |
| 2.4 | Mon compte — voir mes posts | ✅ | Page `/account/posts` |
| 2.5 | Ajout d'un post avec photo => Capacitor | ✅ | `Camera.getPhoto()` sur mobile |
| **3** | **Gestion des Annonces** | | |
| 3.1 | Listing annonces géolocalisées => Capacitor | ✅ | `Geolocation.getCurrentPosition()` |
| 3.2 | Détail d'une annonce | ✅ | Page `/ads/[id]` |
| 3.3 | Mon compte — voir mes annonces | ✅ | Page `/account/ads` |
| 3.4 | Ajout d'une annonce avec géoloc | ✅ | `Geolocation` + formulaire complet |
| **4** | **Map des Annonces** | | |
| 4.1 | Map centrée Utilisateur => Capacitor | ✅ | Géoloc native + fallback web |
| 4.2 | Affichage marqueurs (User + Annonces) | ✅ | Marqueur doré user + marqueurs annonces |
| 4.3 | Interaction marqueurs Annonces | ✅ | Click → panel titre/distance/durée |
| 4.4 | Itinéraire intégré | ✅ | Google Maps Directions + polyline |
| **5** | **Notifications** | | |
| 5.1 | Gestion des commentaires | ✅ | Notification au commentaire |
| 5.2 | Gestion des likes | ✅ | Notification au like |
| 5.3 | Déclenchement => Capacitor | ✅ | `LocalNotifications.schedule()` |
| **6** | **README** | | |
| 6.1 | Procédure d'installation | ✅ | Ce fichier |
| 6.2 | Méthode simulation des notifications | ✅ | Section ci-dessus |
| 6.3 | Infos nécessaires pour la correction | ✅ | Notes importantes + checklist |

---

## Auteur

**Anastasiia Nediak** — IUT Informatique, 2025–2026
