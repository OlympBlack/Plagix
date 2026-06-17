````markdown
# Plagix Project – Prototype de Moteur Antiplagiat

## Présentation

Plagix est un prototype de moteur d'antiplagiat dont l'objectif est la constitution automatique d'une bibliothèque documentaire de thèses académiques en libre accès.

Cette première version implémente un système complet de collecte documentaire reposant sur :

- le scraping automatisé de sources académiques ;
- le traitement asynchrone via Laravel Queue ;
- la déduplication des documents ;
- le stockage structuré en base de données ;
- la consultation des documents collectés à travers une bibliothèque documentaire.

La source actuellement intégrée est :

- OATD (Open Access Theses and Dissertations) : https://oatd.org

---

# Accès Rapide

### Application en ligne

https://plagix.lavoixdabouloussi.org/

### Vidéo de démonstration

À compléter :

```text
[Lien vers la vidéo de démonstration]
````

### Dépôt Git

À compléter :

```text
[Lien GitHub]
```

---

# Fonctionnalités Implémentées

## Gestion des Sources de Scraping

L'application permet :

* d'afficher les sources documentaires disponibles ;
* de visualiser leur statut ;
* de consulter leur date de dernier passage ;
* de connaître le nombre total de documents collectés.

## Scraping Asynchrone

Le lancement du scraping se fait via AJAX sans rechargement de page.

Lorsqu'un utilisateur clique sur :

```text
Lancer Scraping
```

une tâche Laravel Queue est créée puis exécutée en arrière-plan par un Worker dédié.

Cela garantit :

* une interface fluide ;
* aucune attente côté utilisateur ;
* une architecture scalable.

## Collecte Documentaire

Pour chaque thèse collectée, le système extrait :

* Titre
* Auteur
* Université / Institution
* Date de publication
* URL du document source
* Aperçu / Résumé du document

## Déduplication

Chaque document est identifié grâce à une signature unique (hash).

Avant insertion :

* si le document existe déjà → ignoré ;
* sinon → enregistré.

Cette logique empêche les doublons lors des passages successifs.

## Bibliothèque Documentaire

La bibliothèque permet de consulter :

* le titre ;
* l'auteur ;
* l'université ;
* la date de publication ;
* l'URL source ;
* un aperçu du contenu.

Un système de consultation détaillée via modal permet également d'afficher l'intégralité des informations collectées.

---

# Architecture Technique

Le framework utilisé est Laravel.

## Organisation du projet

### Controllers

* SourceController
* DocumentController

Responsabilités :

* gestion des requêtes HTTP ;
* affichage des vues ;
* réponses AJAX.

### Jobs

* ScrapeSourceJob

Responsabilités :

* exécution du scraping ;
* orchestration du traitement ;
* mise à jour des statistiques ;
* déduplication ;
* journalisation.

### Services

Localisation :

```text
app/Services/Scraping/
```

Composants :

* ScraperInterface
* OatdScraperService

Responsabilités :

* récupération des pages ;
* parsing HTML ;
* extraction des données.

---

# Technologies Utilisées

## Backend

* Laravel
* PHP 8+
* MySQL

## Queue

* Laravel Queue
* Driver Database

## Frontend

* Blade
* TailwindCSS
* Axios

## Parsing

* Symfony DomCrawler

## Requêtes HTTP

* Guzzle HTTP

## Contournement Anti-Bot

* Scrape.do

---

# Pourquoi Scrape.do ?

Le site OATD est protégé par Cloudflare.

Une tentative de scraping classique via Guzzle retourne :

```text
HTTP 403 Forbidden
Just a moment...
```

Cloudflare bloque les robots qui ne disposent pas d'un navigateur réel.

Afin de récupérer le contenu réel des pages, l'application utilise :

https://scrape.do

Scrape.do exécute les requêtes via un navigateur distant capable de contourner les protections Cloudflare et retourne ensuite le HTML exploitable par DomCrawler.

---

# Création du Compte Scrape.do

Créer un compte gratuitement :

https://scrape.do/

Documentation :

https://scrape.do/documentation/

Une fois connecté :

1. Ouvrir le Dashboard.
2. Copier le Token API généré automatiquement.

---

# Configuration de l'Environnement

Créer un fichier :

```bash
.env
```

Configurer au minimum :

```env
APP_NAME=plagix

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plagix
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

SCRAPEDO_TOKEN=VOTRE_TOKEN_ICI
```

---

# Installation

## 1. Cloner le projet

```bash
git clone <repository-url>
```

## 2. Installer les dépendances

```bash
composer install
```

## 3. Générer la clé Laravel

```bash
php artisan key:generate
```

## 4. Lancer les migrations

```bash
php artisan migrate
```

## 5. Alimenter les données initiales

```bash
php artisan db:seed
```

Ou :

```bash
php artisan migrate:fresh --seed
```

Le Seeder crée automatiquement :

```text
OATD (Open Access Theses and Dissertations)
```

dans la table des sources.

---

# Lancement du Projet

## Démarrer Laravel

```bash
php artisan serve
```

## Démarrer le Worker Queue

Dans un second terminal :

```bash
php artisan queue:work
```

Le Worker est indispensable pour exécuter les tâches de scraping en arrière-plan.

---

# Utilisation

## Étape 1

Accéder à :

```text
/sources
```

## Étape 2

Cliquer sur :

```text
Lancer Scraping
```

## Étape 3

La tâche est envoyée dans la file d'attente.

## Étape 4

Le Worker récupère la tâche.

## Étape 5

Les documents collectés apparaissent dans :

```text
Bibliothèque des Documents
```

---

# Journalisation

Les événements sont enregistrés dans :

```text
storage/logs/laravel.log
```

Exemples :

* démarrage du scraping ;
* progression ;
* nombre de documents collectés ;
* doublons détectés ;
* erreurs réseau ;
* erreurs de parsing.

---

# Structure de Base de Données

## scraping_sources

Stocke :

* nom de la source ;
* URL ;
* statut ;
* date du dernier passage ;
* nombre de documents collectés.

## collected_documents

Stocke :

* titre ;
* auteur ;
* université ;
* date de publication ;
* URL source ;
* aperçu ;
* signature unique (hash).

---

# Déploiement

Le projet peut être déployé sur :

* Render
* Railway
* AlwaysData

Une version de démonstration est disponible :

https://plagix.lavoixdabouloussi.org/

---

# Auteur

Projet réalisé dans le cadre du challenge technique LAHALEX – Développeur Full Stack.

```
```
