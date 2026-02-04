# projectFD

## Commandes à faire pour lancer le projet
composer install

npm install

## Compilation VueJs
npm run build

## Parametrez votre base de données dans le fichier `.env` à la racine créer votre base puis
php bin/console d:m:m

## Lancer l'application
symfony serve

## rendez-vous à l'adresse `https://127.0.0.1:8000/`

### Pour arrêter l'application
symfony server:stop

### Pour un jeu de tests
php bin/console doctrine:fixtures:load --append  