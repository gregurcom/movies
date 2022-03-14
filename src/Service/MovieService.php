<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Movie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class MovieService
{
    public function __construct(private Container $container, private SluggerInterface $slugger) {}

    public function storeImage(Movie $movie, UploadedFile $imagePath)
    {
        $originalFilename = pathinfo($imagePath->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-'. uniqid() . '.' . $imagePath->guessExtension();

        try {
            $imagePath
                ->move($this->container->getParameter('images_directory'), $fileName);
        } catch (FileException $e) {
            new Response($e->getMessage());
        }

        $movie->setImage($fileName);
    }
}