<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * OpenApiFactoryDecorator - Décorateur pour personnaliser la documentation OpenAPI/Swagger.
 * Étend la documentation pour ajouter schémas (Credentials, Token), sécurité JWT et endpoint /api/login_check.
 */
#[AsDecorator('api_platform.openapi.factory')]
class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    /**
     * Constructeur - Injecte la factory OpenAPI à décorer.
     * @param OpenApiFactoryInterface $decorated Factory OpenAPI originale d'API Platform
     */
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {}

    /**
     * Génère et personnalise la spécification OpenAPI.
     * Ajoute schémas (Credentials, Token), sécurité JWT et endpoint /api/login_check.
     * @param array $context Contexte pour la génération (optionnel)
     * @return OpenApi Spécification OpenAPI personnalisée
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        
        $schemas = $openApi->getComponents()->getSchemas();

        // Credentials schema
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'example' => 'aymerick@diamond.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'password',
                ],
            ],
        ]);

        // Token schema
        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);

        // Add Security Scheme for JWT
        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?? new \ArrayObject();
        $securitySchemes['JWT'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'JWT',
            description: 'Entrez votre token JWT sous la forme : Bearer <token>'
        );

        // Define the login check path item
        $pathItem = new PathItem(
            ref: 'JWT Token Generation',
            post: new Operation(
                operationId: 'postCredentialsItem',
                tags: ['Auth'],
                responses: [
                    '200' => [
                        'description' => 'JWT Token généré avec succès',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Identifiants invalides',
                    ],
                ],
                summary: 'Génère un token JWT pour s\'authentifier sur l\'API.',
                requestBody: new RequestBody(
                    description: 'Identifiants de connexion',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials',
                            ],
                        ],
                    ]),
                ),
                security: []
            ),
        );

        $openApi->getPaths()->addPath('/api/login_check', $pathItem);

        // Apply JWT security globally to all other routes
        $openApi = $openApi->withSecurity([['JWT' => []]]);

        return $openApi;
    }
}

