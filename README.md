# Plagix Project - Prototype de Moteur Antiplagiat

Plagix est un prototype de moteur d'antiplagiat dont l'objectif est la collecte automatique de thèses en libre accès.
Cette phase du développement comprend le système de scraping asynchrone pour la récupération des documents depuis des sources académiques en ligne, telles que **OATD**.

## Architecture & Choix Techniques

Le framework utilisé est **Laravel**. Le projet respecte les bonnes pratiques de développement :

- **Séparation des Responsabilités (Clean Architecture) :**
    - **Controllers :** `SourceController` et `DocumentController` (gèrent uniquement les affichages et réponses HTTP).
    - **Jobs :** `ScrapeSourceJob` (orchestre l'appel au scraper, la déduplication par hash et l'enregistrement DB en arrière-plan).
    - **Services :** L'extraction des données se trouve exclusivement dans `app/Services/Scraping/` respectant l'interface `ScraperInterface` (`OatdScraperService`).
- **Composants Tiers :**
    - `GuzzleHttp\Client` pour l'envoi de la requête HTTP.
    - `Symfony\Component\DomCrawler\Crawler` pour le parcours et l'extraction de l'arbre DOM.
- **Asynchrone :** Laravel Queue fonctionnant sur le driver `database` pour gérer le processus coûteux et lent que représente le scraping sans bloquer l'interface.
- **Frontend :** Templates Blade utilisant Tailwind CSS pour le style. Appels AJAX gérés par **Axios**.

## Prérequis

- PHP 8.x
- Composer
- Base de données MySQL (ou équivalent supporté par Laravel)

## Installation et Configuration

1. **Cloner le projet ou naviguer à la racine.**
2. **Installer les dépendances PHP :**
    ```bash
    composer install
    ```
3. **Configurer l'environnement :**
   Assurez-vous que le fichier `.env` est correctement configuré. Paramétrez les identifiants pour votre base de données :

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=votre_db
    DB_USERNAME=root
    DB_PASSWORD=

    QUEUE_CONNECTION=database
    ```

4. **Migrer et Mettre la base de données (Seeding) :**
    ```bash
    php artisan migrate:fresh --seed
    ```
    _Ceci créera les tables de base de données nécessaires et injectera OATD en tant que source de données prête de base._

## Lancer le Projet

1. **Serveur web local :**
    ```bash
    php artisan serve
    ```
2. **Queue Worker (Très important pour le Scraping asynchrone) :**
   Ouvrez un **nouveau terminal**, placez-vous à la racine du projet et exécutez :
    ```bash
    php artisan queue:work
    ```
    _Cette commande lit les requêtes de scraping en attente dans la base de données et exécute effectivement l'action des extracteurs web pour récupérer les données en toute fluidité._

## Utilisation

1. Vous atterrisserez directement sur **`/sources`** via votre navigateur : l'interface qui liste nos cibles de scraping.
2. Cliquez sur le bouton "Lancer Scraping" sur une source listée. Vous verrez un indicateur de chargement AJAX s'activer quelques secondes signifiant que la tâche a bien été transférée dans la Queue.
3. Observez la console exécutant `php artisan queue:work` pour vérifier le bon déroulement du service applicatif asynchrone en temps réel.
4. Finalement, visualisez ou consultez les documents collectés dans la `Bibliothèque` (en naviguant sur l'onglet correspondant).
