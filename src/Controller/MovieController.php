<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function create(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMovie = $form->getData();
            $imagePath = $form->get('image')->getData();

            if ($imagePath) {
                $this->storeImage($newMovie, $imagePath);
            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/create.html.twig', ['form' => $form]);
    }

    #[Route('/movies/{movie}/update', name: 'movies_update', requirements: ['movie' => '\d+'])]
    public function update(int $movie, Request $request): Response
    {
        $movie = $this->movieRepository->find($movie);

        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('image')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                $this->storeImage($movie, $imagePath);
            }
            $movie->setTitle($form->get('title')->getData());
            $movie->setRating($form->get('rating')->getData());
            $movie->setDescription($form->get('description')->getData());

            $this->em->flush();

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movie/update.html.twig', [
            'movie' => $movie,
            'form' => $form
        ]);
    }

    #[Route('/movies/{movie}/delete', name: 'movies_delete', methods: ['GET', 'DELETE'])]
    public function delete(int $movie): Response
    {
        $movie = $this->movieRepository->find($movie);
        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies_list');
    }

    #[Route('/movies/{movie}', name: 'movies_show', requirements: ['movie' => '\d+'], methods: ['GET'])]
    public function show(int $movie): Response
    {
        $movie = $this->movieRepository->find($movie);

        if ($movie) {
            return $this->render('movie/show.html.twig', ['movie' => $movie]);
        }

        throw $this->createNotFoundException('The movie does not exist');
    }

    private function storeImage(Movie $movie, UploadedFile $imagePath)
    {
        $newFileName = uniqid() . '.' . $imagePath->guessExtension();

        try {
            $imagePath
                ->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFileName);
        } catch (FileException $e) {
            new Response($e->getMessage());
        }

        $movie->setImage('/uploads/' . $newFileName);
    }
}
