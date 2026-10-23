<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * AboutController - Affiche une page statique avec des informations sur le site.
 */
final class AboutController extends AbstractController
{
    /**
     * Affiche la page "À propos".
     * @return Response Page "À propos" rendue avec Twig
     */
    #[Route('/about', name: 'app_about')]
    public function index(): Response
    {
        return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }
}
