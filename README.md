# Créez un web service exposant une API (BileMo) - Symfony - P7 OCR

## Informations :

-  Documentation: https://localhost:8000/api/doc
-  Identifiants:

```
"username": "user0@gmail.com"
"password": "123"
```

## Installation :

-  **1.** Cloner le projet

```
git clone https://github.com/Tony-marques/P7_bilemo.git
```

-  **2.** Installer les dépendances back et front `composer install` à la racine du projet

-  **3.** Créer un fichier .env à la racine du projet avec les identifiants de votre base de donnée

-  **4.** Créer la base de donnée

```
symfony console doctrine:database:create
```

-  **5.** Créer les tables dans la base de donnée

```
symfony console doctrine:migrations:migrate
```

-  **6.** Créer les données avec les fixtures symfony

```
symfony console doctrine:fixtures:load
```
