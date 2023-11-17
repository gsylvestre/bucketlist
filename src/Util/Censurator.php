<?php

namespace App\Util;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class Censurator
{
    public function __construct(
        private readonly ContainerBagInterface $containerBag
    ){}

    public function purify(string $text): string
    {
        $directory = $this->containerBag->get('data_directory');

        if (!file_exists($directory."/unwanted_words.txt")){
            return $text;
        }

        $unwantedWords = file($directory."/unwanted_words.txt", FILE_IGNORE_NEW_LINES);

        foreach($unwantedWords as $badWord){
            $replacement = str_repeat("*", mb_strlen($badWord));
            $text = str_ireplace($badWord, $replacement, $text);
        }

        return $text;
    }
}