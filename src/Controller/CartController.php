<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Symfony\Component\String\u;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        /** 
         * @var User
         * */

        $user =  $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
       
       $cart = $user->getCart();
        

        return $this->render('cart/index.html.twig', [
            'cart' => $cart
        ]);
    }
    
    #[Route('/cart/add/{id}', name:'app_cart_add')]
    public function add(int $id, ProductsRepository $productsRepository, EntityManagerInterface $manager)
{
    $product = $productsRepository->find($id);
    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    /** 
     * @var User 
     */
    $user = $this->getUser();
    $cart = $user->getCart();

    if ($cart === null) {
        $cart = new Cart();
        $cart->setUser($user);
        $manager->persist($cart);
    }

    $cartItemFound = false;
    foreach ($cart->getCartItem() as $cartItem) {
        if ($cartItem->getProduct() === $product) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
            $cartItemFound = true;
            break;
        }
    }
    
    if (!$cartItemFound) {
        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity(1);
        $cartItem->setCart($cart);
        $cart->addCartItem($cartItem);
        $manager->persist($cartItem);
    }

    $manager->flush();

    return $this->redirectToRoute('app_cart');
}

}
