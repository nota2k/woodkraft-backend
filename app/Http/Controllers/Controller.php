<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Woodkraft API',
    description: 'API pour le site e-commerce de mobilier en bois. Gestion des produits, catégories, variations et images.',
    contact: new OA\Contact(
        email: 'contact@woodkraft.com'
    )
)]
#[OA\Server(
    url: 'http://localhost:8888',
    description: 'Serveur de développement MAMP'
)]
#[OA\Tag(name: 'Products', description: 'Gestion des produits')]
#[OA\Tag(name: 'Categories', description: 'Gestion des catégories')]
class Controller
{
    //
}
