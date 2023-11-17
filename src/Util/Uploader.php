<?php

namespace App\Util;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class Uploader
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ContainerBagInterface $containerBag,
    ){

    }

    public function upload(UploadedFile $uploadedImage): string
    {
        if($uploadedImage){
            $originalFilename = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedImage->guessExtension();

            try {
                $uploadedImage->move($this->containerBag->get('upload_directory'), $newFilename);
                return $newFilename;
            } catch(FileException $exception)
            {
                dd($exception);
            }
        }
    }
}