<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Movie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class MovieService
{
    public function __construct(private Container $container) {}

    public function storeImage(Movie $movie, UploadedFile $imagePath)
    {
        $newFileName = uniqid() . '.' . $imagePath->guessExtension();

        try {
            $imagePath
                ->move($this->container->getParameter('kernel.project_dir') . '/public/uploads', $newFileName);
        } catch (FileException $e) {
            new Response($e->getMessage());
        }

        $movie->setImage('/uploads/' . $newFileName);
    }
}