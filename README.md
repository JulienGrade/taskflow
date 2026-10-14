# TaskFlow — version procedural

Une application de gestion de projets et de tâches en PHP sans framework. Interface en français, responsive, conçue avec Tailwind CSS compilé. Le code utilise une syntaxe compatible PHP 8.2 minimum ; l’image Docker fournie exécute PHP 8.4. Base PostgreSQL 17.

## Démarrage rapide

Prérequis : Docker Desktop démarré (ou Docker Engine et le plugin Compose v2), un accès Internet au premier build, et le port 8081 disponible. Aucune installation locale de PHP, Composer, Node ou PostgreSQL n’est nécessaire.

Dans un terminal ouvert dans le dossier parent du projet :

```bash
cd taskflow-procedural
cp .env.example .env
docker compose up -d --build
```

Sous PowerShell, remplacez `cp .env.example .env` par `Copy-Item .env.example .env`.

Ouvrez **http://localhost:8081** une fois le build terminé. La première construction télécharge les images, installe le compilateur Tailwind et compile le CSS. Elle peut prendre quelques minutes.

```bash
docker compose ps
docker compose logs -f app
```

`Ctrl+C` quitte le suivi des journaux sans arrêter les conteneurs. Le service `app` attend que PostgreSQL soit sain, puis exécute `bin/seed.php` avant de démarrer le serveur.

## Comptes et données de démonstration

| Compte | E-mail | Mot de passe |
|---|---|---|
| Julien Grade | julien@taskflow.test | Demo1234! |
| Camille Martin | camille@taskflow.test | Demo1234! |

Chaque base contient **2 comptes, 10 projets et 80 tâches** : 5 projets et 40 tâches par compte. Les titres, descriptions, couleurs, statuts et priorités sont identiques dans les deux versions. Les échéances sont calculées par rapport au jour du remplissage pour conserver une démonstration pertinente. Les empreintes des mots de passe et les horodatages peuvent naturellement différer.

Chaque compte voit et modifie uniquement ses propres projets et tâches, y compris si un identifiant est changé manuellement dans l’URL ou dans un formulaire.

Le schéma est créé par `database/schema.sql` lors de la création du volume PostgreSQL. Le script `bin/seed.php` remplit ensuite la base dans une transaction. Il ne modifie pas une base contenant déjà des utilisateurs et ne duplique donc pas les données à chaque redémarrage.

Pour rejouer le remplissage sans effacer les données :

```bash
docker compose exec app php bin/seed.php
```

Pour **effacer toutes les données de ce projet** et rétablir la démonstration (déconnectez-vous puis reconnectez-vous après cette commande) :

```bash
docker compose exec app php bin/seed.php --reset
```

Pour afficher les nombres d’enregistrements :

```bash
docker compose exec db psql -U taskflow -d taskflow -c "SELECT (SELECT COUNT(*) FROM users) AS comptes, (SELECT COUNT(*) FROM projects) AS projets, (SELECT COUNT(*) FROM tasks) AS taches;"
```

Si vous avez modifié `DB_USER` ou `DB_NAME`, adaptez les valeurs après `-U` et `-d`.

## Fonctionnalités

Connexion et déconnexion, tableau de bord et statistiques, création/modification/suppression des projets, couleur et progression des projets, création/modification/suppression des tâches, description, statut, priorité et date d’échéance, recherche par titre et filtres par projet/statut, repérage des tâches en retard et confirmation explicite avant suppression.

La suppression d’un projet entraîne celle de ses tâches grâce à la clé étrangère PostgreSQL. La recherche utilise `ILIKE` : `%` et `_` ont leur sens de jokers SQL. Les listes restent volontairement sans pagination à l’échelle de la démonstration.

## Architecture et lecture du code

Le point d’entrée `public/index.php` charge explicitement les fichiers avec `require`. Le routage appelle une fonction de contrôleur. Cette fonction délègue au service, qui appelle les fonctions du repository en leur passant PDO. Les vues reçoivent des tableaux et échappent les valeurs avec `e()`.

| Dossier | Responsabilité |
|---|---|
| `app/controllers` | Traiter la requête, appeler le service, rendre la vue ou rediriger |
| `app/services` | Validation, règles métier, authentification et statistiques |
| `app/repositories` | Requêtes SQL préparées et filtrage par propriétaire |
| `app/core` | Connexion PDO et rendu des vues |
| `views` | Gabarits PHP/HTML, partiels et aides de présentation |

Aucune classe applicative n’est définie. Les objets natifs `PDO` et `DateTimeImmutable` restent utilisés : « procédural » décrit ici l’organisation du code applicatif, pas l’interdiction des API natives objet de PHP. Composer est absent, car il n’y a aucune classe applicative à autoloader. Les fonctions ne font pas appel à une connexion globale : PDO est passé explicitement.

Chemin d’une requête : **point d’entrée → contrôleur → service → repository → PostgreSQL**, puis **vue HTML** ou **redirection HTTP 303**. Les règles de validation sont dans les services, le SQL dans les repositories et le HTML dans les vues.

Pour suivre un cas concret, partez de la route `task-new` dans `public/index.php`, puis ouvrez le contrôleur des tâches, le service des tâches, le repository des tâches et `views/task-form.php`.

## Modifier le code

Le code est copié dans l’image au build ; il n’y a pas de montage du code source. Après une modification PHP, HTML ou CSS :

```bash
docker compose up -d --build
```

La base existante est conservée. Les images construites n’ont pas besoin de Node ni de Composer à l’exécution. Le CSS compilé est également fourni dans `public/assets/app.css`.

Pour travailler uniquement sur le CSS avec Node 22 installé localement, dans le dossier du projet :

```bash
npm ci
npm run dev:css
```

`npm run build:css` génère une version minifiée. Pour voir ce CSS dans le conteneur, reconstruisez l’image. Les dépendances npm sont verrouillées dans `package-lock.json`.

## Commandes utiles

```bash
# Arrêter en conservant les données
docker compose down

# Redémarrer
docker compose up -d

# Journaux de la base
docker compose logs db

# Ouvrir une session SQL
docker compose exec db psql -U taskflow -d taskflow

# Vérifier la syntaxe de tous les fichiers PHP
docker compose exec app sh -c 'find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;'
```

La commande suivante **supprime aussi le volume et toutes les données**. Au lancement suivant, le schéma et les données sont recréés :

```bash
docker compose down -v
docker compose up -d
```

Si le schéma SQL a été modifié, il ne sera pas réexécuté sur un volume existant. Pour cette démonstration, utilisez la remise à zéro du volume ; pour une vraie application, mettez en place des migrations versionnées.

## Vérifications fonctionnelles

Le script HTTP fourni nécessite Python 3 uniquement sur la machine qui lance les tests. Démarrez l’application, puis exécutez :

```bash
python3 tests/smoke.py http://localhost:8081
```

Il crée des projets et tâches préfixés `QA-`, vérifie les protections et nettoie ses enregistrements en fin de parcours. Lancez-le sur les données de démonstration, sans changer simultanément les comptes. Il vérifie aussi que le deuxième compte ne peut pas accéder aux données du premier.

## Sécurité et périmètre

Les mots de passe sont hachés avec `password_hash` et contrôlés avec `password_verify`. Les requêtes utilisent PDO avec de vraies préparations, sans concaténer les valeurs utilisateur au SQL. Les opérations d’écriture passent par POST et exigent un jeton CSRF. L’identifiant de session est renouvelé à la connexion ; les cookies sont HttpOnly et SameSite=Lax. Le rendu HTML échappe les valeurs, le routeur utilise une liste fermée et les erreurs internes sont journalisées sans afficher les détails au navigateur.

La limitation des connexions est de cinq échecs par minute et par session. Une nouvelle session peut la contourner : en production, il faudrait une limitation persistante par compte/IP. L’authentification et les comptes de démonstration sont destinés à l’apprentissage ; le projet n’inclut ni inscription, ni récupération de mot de passe, ni rôles d’administration.

L’environnement est un environnement local : serveur PHP intégré, accès lié à `127.0.0.1`, HTTP local et comptes connus. Pour une mise en production, il faudrait notamment un serveur web adapté, HTTPS, une gestion des secrets, des sauvegardes et une politique de sessions persistantes. Les identifiants `.env` de PostgreSQL ne sont appliqués que lors de la création du volume ; les changer ne modifie pas les rôles d’une base déjà initialisée.

## Dépannage

| Symptôme | Action |
|---|---|
| Impossible de contacter Docker | Démarrer Docker Desktop et vérifier `docker info` |
| Port déjà occupé | Modifier `APP_PORT` dans `.env`, puis `docker compose up -d` |
| Application qui s’arrête au premier lancement | Lire `docker compose logs app` et `docker compose logs db` |
| Connexion PostgreSQL refusée après changement du `.env` | Rétablir les anciens identifiants ou recréer le volume si les données peuvent être effacées |
| Styles absents après modification | Reconstruire l’image avec `docker compose up -d --build` |
| Formulaire expiré | Recharger le formulaire ; le jeton change après connexion/déconnexion |

Propriété de Julien Grade.
