<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Movie;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class MovieService
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $projectDir
    ) {}

    public function storeImage(Movie $movie, UploadedFile $imagePath)
    {
        $originalFilename = pathinfo($imagePath->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-'. uniqid() . '.' . $imagePath->guessExtension();

        try {
            $imagePath
                ->move($this->projectDir, $fileName);
        } catch (FileException $e) {
            new Response($e->getMessage());
        }

        $movie->setImage($fileName);
    }

    public function getMovieFields(): array
    {
        return [
            'title' => null,
            'category' => null,
            'rating' => null,
            'description' => null,
            'image' => null
        ];
    }
}