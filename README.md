# Woodkraft API - E-commerce de mobilier en bois

API REST pour la gestion d'un site e-commerce de mobilier en bois avec Laravel.

## 🚀 Démarrage de l'application

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- MySQL (via MAMP)
- MAMP installé et configuré

### Configuration

1. **Démarrer MAMP**
   - Ouvrez l'application MAMP
   - Cliquez sur "Démarrer les serveurs"
   - Vérifiez que Apache et MySQL sont actifs (icônes vertes)
   - **Important** : MySQL doit être sur le port **8889** (vérifiez dans Préférences > Ports)

2. **Vérifier la base de données**
   - La base de données `woodkraft_db` doit exister
   - Si elle n'existe pas, créez-la via phpMyAdmin : `http://localhost:8888/phpMyAdmin`
   - Ou exécutez : `php artisan migrate:fresh --seed` (créera la base si elle n'existe pas)

3. **Démarrer le serveur Laravel**

   Vous avez deux options :

   **Option 1 : Serveur PHP intégré (Recommandé)**
   ```bash
   cd woodkraft-backend
   php artisan serve --port=8888
   ```

   **Option 2 : Utiliser Apache de MAMP**
   - Configurez votre VirtualHost MAMP pour pointer vers le dossier `public` de votre projet
   - DocumentRoot : `/Users/nellybabillon/Sites/woodkraft-backend/woodkraft-backend/public`

3. **Accéder à l'API**

   Une fois le serveur démarré, vous pouvez accéder à :
   - **API Base URL** : `http://localhost:8888/api/v1`
   - **Documentation Swagger** : `http://localhost:8888/api/documentation`

## 📚 Endpoints API

### Produits
- `GET /api/v1/products` - Liste des produits
- `POST /api/v1/products` - Créer un produit
- `GET /api/v1/products/{id}` - Détails d'un produit
- `PUT /api/v1/products/{id}` - Mettre à jour un produit
- `DELETE /api/v1/products/{id}` - Supprimer un produit

### Catégories
- `GET /api/v1/categories` - Liste des catégories
- `POST /api/v1/categories` - Créer une catégorie
- `GET /api/v1/categories/{id}` - Détails d'une catégorie
- `PUT /api/v1/categories/{id}` - Mettre à jour une catégorie
- `DELETE /api/v1/categories/{id}` - Supprimer une catégorie

## 🗄️ Base de données (MAMP)

### Configuration MAMP

La base de données est gérée par **MAMP** avec les paramètres suivants :
- **Hôte** : `127.0.0.1`
- **Port MySQL** : `8889` (port par défaut de MAMP)
- **Utilisateur** : `root`
- **Mot de passe** : `root`
- **Base de données** : `woodkraft_db`

### Accéder à phpMyAdmin (MAMP)

1. Démarrez **MAMP** (Apache et MySQL doivent être actifs)
2. Ouvrez votre navigateur et allez à : `http://localhost:8888/phpMyAdmin`
3. Connectez-vous avec :
   - Utilisateur : `root`
   - Mot de passe : `root`

### Gestion de la base de données

**Vérifier que la base de données existe :**
```bash
# Via phpMyAdmin : http://localhost:8888/phpMyAdmin
# Ou via la ligne de commande :
php artisan db:show
```

**Réinitialiser la base de données avec les données de test :**
```bash
php artisan migrate:fresh --seed
```

**Créer manuellement la base de données (si nécessaire) :**
1. Ouvrez phpMyAdmin : `http://localhost:8888/phpMyAdmin`
2. Cliquez sur "Nouvelle base de données"
3. Nom : `woodkraft_db`
4. Interclassement : `utf8mb4_unicode_ci`
5. Cliquez sur "Créer"

**Important :** Assurez-vous que MAMP est démarré avant de lancer les commandes Laravel qui utilisent la base de données.

## 📖 Documentation Swagger

La documentation interactive de l'API est disponible à :
`http://localhost:8888/api/documentation`

Vous pouvez tester tous les endpoints directement depuis l'interface Swagger.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
