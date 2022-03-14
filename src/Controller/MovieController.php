<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    public function __construct(
        private MovieRepository $movieRepository,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/movies', name: 'movies_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('movie/list.html.twig', ['movies' => $this->movieRepository->findAll()]);
    }

    #[Route('/movies/create', name: 'movies_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, MovieService $movieService): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagePath = $form->get('image')->getData();

            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->em->persist($movie);
            $this->em->flush();

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/create.html.twig', ['form' => $form]);
    }

    #[Route('/movies/{id}/update', name: 'movies_update', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Movie $movie, Request $request, MovieService $movieService): Response
    {
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('image')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                $movieService->storeImage($movie, $imagePath);
            }

            $this->em->flush();

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/update.html.twig', [
            'movie' => $movie,
            'form' => $form
        ]);
    }

    #[Route('/movies/{id}/delete', name: 'movies_delete', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Movie $movie): Response
    {
        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/movies/{id}', name: 'movies_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', ['movie' => $movie]);
    }
}
