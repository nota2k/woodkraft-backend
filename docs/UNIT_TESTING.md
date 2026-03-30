# Tests — woodkraft-backend (Laravel 12 + PHPUnit)

Document **vivant** : l’agent (ou le développeur) exécute les commandes ci-dessous, met à jour les sections « État » et « Inventaire des tests », et complète la file d’attente quand le périmètre change.

## Outils

| Outil | Rôle |
|-------|------|
| [PHPUnit 11](https://phpunit.de/) | Lanceur et assertions (via `php artisan test`) |
| Laravel Testing | `TestCase`, `RefreshDatabase`, `postJson`, `actingAs`, etc. |
| SQLite en mémoire | Configuré dans `phpunit.xml` (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) |

## Commandes (depuis `woodkraft-backend/`)

```bash
# Suite complète (recommandé — nettoie le cache de config puis lance les tests)
composer test
# ou (alias identique au script « test »)
composer run test:unit

# Équivalent direct
php artisan test

# Uniquement les tests unitaires (dossier tests/Unit)
php artisan test --testsuite=Unit

# Uniquement les tests Feature
php artisan test --testsuite=Feature

# Un fichier ou une méthode
php artisan test tests/Feature/Admin/ProductTest.php
php artisan test --filter=test_admin_can_create_product_with_all_fields

# Parallèle (si besoin de vitesse sur une grosse suite)
# php artisan test --parallel
```

## Conventions

- **Unit** (`tests/Unit/`) : pas d’application Laravel complète — `PHPUnit\Framework\TestCase` sauf besoin du conteneur ; privilégier les classes pures du domaine.
- **Feature** (`tests/Feature/`) : `Tests\TestCase` + HTTP, base de données, `RefreshDatabase` quand la DB est utilisée.
- **Nommage** : méthodes `test_*` ou attribut `#[Test]` (préférer les **attributs PHPUnit** aux `@test` en docblock pour éviter les avertissements PHPUnit 12).
- **Factories** : `User::factory()`, `Product::factory()`, etc., alignées sur `database/factories/`.
- **API JSON** : `postJson`, `getJson`, `assertJsonValidationErrors`, codes attendus (`201`, `422`, …).

## Périmètre prioritaire (WoodKraft)

1. **Routes API** sous `routes/api.php` — auth, client, admin (déjà couvert en partie par `ProductTest`).
2. **Modèles** — relations, accesseurs, scopes métiers.
3. **Actions / services** — logique extraite des contrôleurs (facile à tester en unit pur).
4. **Upload / images** — `Storage::fake()`, assertions sur fichiers ou formats.

## État de la suite (à mettre à jour après chaque passage sérieux)

| Champ | Valeur |
|-------------|--------|
| **Dernière exécution** | 2026-03-30 — `composer test` |
| **Résultat** | OK — 3 fichiers, 6 tests, 18 assertions |
| **Notes** | Avertissements PHPUnit : remplacer les `@test` en docblock par `#[Test]` dans `ProductTest` (compatibilité PHPUnit 12). |

## Inventaire des fichiers de test

| Fichier | Type | Couvre |
|---------|------|--------|
| `tests/Unit/ExampleTest.php` | Unit | Exemple minimal |
| `tests/Feature/ExampleTest.php` | Feature | Réponse HTTP `/` |
| `tests/Feature/Admin/ProductTest.php` | Feature | API admin produits (création, validation, dimensions, matériau invalide) |
| _à ajouter_ | | Auth client, panier, catégories, etc. |

## File d’attente / dette

- [ ] Migrer `ProductTest` vers attributs `#[Test]` / `PHPUnit\Framework\Attributes\Test`
- [ ] Tests API : auth register/login, panier `customer/cart`, profil
- [ ] Couverture ciblée : `php artisan test --coverage` (si extension PCOV / Xdebug disponible)

---

## Instructions pour l’agent Cursor

1. **Avant** d’ajouter des endpoints, règles de validation ou logique métier côté API : prévoir ou mettre à jour un test Feature (ou Unit si pur).
2. **Après** avoir ajouté ou modifié des tests : lancer `composer test` depuis `woodkraft-backend/`, mettre à jour le tableau **État de la suite** et **Inventaire** dans ce fichier.
3. **Mettre à jour** la **File d’attente** quand une zone est couverte ou qu’une nouvelle zone sensible apparaît.
4. En cas d’échec : corriger le code ou le test, documenter dans **Notes** si le correctif est partiel ou bloqué par l’environnement.

## Évolution (optionnel)

- **Pest** : non installé pour l’instant ; si le projet migre vers Pest, mettre à jour commandes et conventions ici.
- **CI** : réutiliser `composer test` dans le pipeline avec les mêmes variables que `phpunit.xml`.
