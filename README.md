# 📱 Projet App Mobile Hybride – IUT Info 2ème année ALT

Projet réalisé dans le cadre du module **Développement d’application mobile hybride**
Année universitaire **2025-2026 – Semestre 4**

## 🧱 Stack technique

* **Backend** : Laravel (API REST)
* **Frontend** : Nuxt 4 (SPA)
* **Mobile** : Capacitor + Android Studio
* **Base de données** : MySQL
* **Conteneurs** : Docker + WSL
  
  
---

## ⚙️ Installation du projet

### 1. Cloner le repo

```bash
git clone https://github.com/USERNAME/appmobile.git
cd appmobile
```

### 2. Lancer Docker

```bash
docker compose up -d
```

### 3. Installer Laravel

```bash
docker compose run --rm api composer install
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate --seed
```

### 4. Installer le frontend

```bash
docker compose run --rm frontend npm install
```

---

## 🌐 Accès au projet

* Frontend : http://localhost:3000
* API Laravel : http://localhost:8000
* PhpMyAdmin : http://localhost:8080

---

## 📱 Build mobile (Android)

Dans WSL :

```bash
npm run android
```

Puis ouvrir dans Android Studio :

```
c:/Users/USERNAME/appmobile-tp/android
```

Lancer l’émulateur et exécuter l’app.

---

## 👩‍💻 Auteur

Projet réalisé par Anastasiia Nediak, une étudiante de l’IUT Info dans le cadre du TP mobile hybride.
