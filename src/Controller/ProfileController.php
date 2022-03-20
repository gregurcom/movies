<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('{_locale}/profile', name: 'profile', requirements: ['_locale' => 'en|fr'])]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }
}
