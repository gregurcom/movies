<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class TranslateController extends AbstractController
{
    #[Route('/{_locale}/translate', name: 'translate', requirements: ['_locale' => 'en|fr'])]
    public function translate(Request $request, RequestStack $requestStack): RedirectResponse
    {
        $uri = $request->query->get('uri');
        $session = $requestStack->getSession();
        $session->set('_locale', $request->get('_locale'));

        if (str_contains($uri, 'en')) {
            $uri = str_replace('en', 'fr', $uri);
        } elseif (str_contains($uri, 'fr')) {
            $uri = str_replace('fr', 'en', $uri);
        }

        return $this->redirect($uri);
    }
}
