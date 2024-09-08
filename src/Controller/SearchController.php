<?php



namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(Request $request, ProductsRepository $productsRepository): Response
    {
        // Récupérer le terme de recherche depuis la requête
        $searchTerm = $request->query->get('q');

        // Rechercher les produits en fonction du terme de recherche
        $products = $searchTerm ? $productsRepository->findBySearchTerm($searchTerm) : [];

        // Rendre la vue avec les produits trouvés
        return $this->render('search/index.html.twig', [
            'products' => $products,
            'searchTerm' => $searchTerm,
        ]);
    }
}
