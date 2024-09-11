<?php

namespace App\EventSubscriber;

use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private $cartRepository;

    private $security;

    private $twig;
    /**
     * @param $cartRepository
     */
    public function __construct(CartItemRepository $cartItemRepository, Security $security, Environment $twig)
    {
        $this->cartRepository = $cartItemRepository;
        $this->security = $security;
        $this->twig = $twig;
    }


    public function onKernelController(ControllerEvent $event)
    {
        $user = $this->security->getUser();

        if (!$user) {
            $this->twig->addGlobal('count', 0);
            return;
        }
        $cart = $user->getCart();

        if (!$cart) {
            $this->twig->addGlobal('count', 0);
            return;
        }

        $count = $this->cartRepository->getCartNumber($cart);
        $this->twig->addGlobal('count', $count);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}