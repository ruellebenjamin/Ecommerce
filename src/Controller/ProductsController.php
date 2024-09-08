<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductType;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductsController extends AbstractController
{


    #[Route('/product/create', name: 'app_products_create')]
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_home');
        }


        $product = new Products(); // Initialiser l'entité

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($product);
            $manager->flush();

            return $this->redirectToRoute('app_products_create');
        }

        return $this->render('products/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}', name: 'app_products_show')]
    public function show(int $id, ProductsRepository $productsRepository)
    {
       $products =  $productsRepository->find($id);



        return $this->render('products/index.html.twig',
            [
                'product' => $products
            ]);
    }

}
