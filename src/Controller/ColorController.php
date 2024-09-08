<?php

namespace App\Controller;

use App\Repository\ColorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ColorController extends AbstractController
{
    #[Route('/color', name: 'app_color')]
    public function index(ColorRepository $colorRepository): Response
    {
        $colors =$colorRepository->findAll();
        return $this->render('color/index.html.twig',
         [
            "colors" => $colors
        ] );
    }
}
