<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class TranslateController extends AbstractController
{
    #[Route('/translate/{lang}', name: 'translate', defaults: ['lang' => 'en'])]
    public function translate(string $lang, RequestStack $requestStack): RedirectResponse
    {
        if (in_array($lang, $this->getParameter('app.supported_locales'))) {
            $session = $requestStack->getSession();
            $session->set('_locale', $lang);

            return $this->redirectToRoute('movies_list');
        }

        throw new \InvalidArgumentException('No such language found');
    }
}
