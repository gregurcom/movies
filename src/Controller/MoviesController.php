<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{
    public function __construct(
        private MovieRepository $movieRepository,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/movies', name: 'movies_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('movies/list.html.twig', ['movies' => $this->movieRepository->findAll()]);
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
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    new Response($e->getMessage());
                }

                $newMovie->setImage('/uploads/' . $newFileName);
            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute('movies_list');
        }

        return $this->renderForm('movies/create.html.twig', ['form' => $form]);
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
                if ($movie->getImage() !== null) {
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . $movie->getImage()
                    )) {
                        $this->GetParameter('kernel.project_dir') . $movie->getImage();
                    }
                    $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                    try {
                        $imagePath->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads',
                            $newFileName
                        );
                    } catch (FileException $e) {
                        return new Response($e->getMessage());
                    }

                    $movie->setImage('/uploads/' . $newFileName);
                    $this->em->flush();

                    return $this->redirectToRoute('movies');
                }
            } else {
                $movie->setTitle($form->get('title')->getData());
                $movie->setRating($form->get('rating')->getData());
                $movie->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('movies_list');
            }
        }

        return $this->renderForm('movies/update.html.twig', [
            'movie' => $movie,
            'form' => $form
        ]);
    }

    #[Route('/movies/{movie}/delete', name: 'movies_delete', methods: ['GET', 'DELETE'])]
    public function delete($movie): Response
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
            return $this->render('movies/show.html.twig', ['movie' => $movie]);
        }

        throw $this->createNotFoundException('The movie does not exist');
    }
}
