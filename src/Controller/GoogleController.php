<?php

namespace App\Controller;

use App\Entity\User;
use Container196Gl2n\getSecurity_UserPasswordHasherService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    #[Route('/login/google', name: 'app_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {

        return $clientRegistry
            ->getClient('google_main')
            ->redirect([
                'profile', 'email','openid'
            ]);

    }




     #[Route('/connect/google/check', name: 'connect_google_check')]
     public function connectCheck(Request $request, ClientRegistry $clientRegistry, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, Security $security): Response
     {
         /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient $client */
         $client = $clientRegistry->getClient('google_main');

         try {
             // Obtenir le token d'accès
             $accessToken = $client->getAccessToken();

             // Utiliser ce token pour récupérer l'utilisateur
             /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
             $user = $client->fetchUserFromToken($accessToken);

             // Maintenant que l'utilisateur est récupéré, accéder à ses informations
             $email = $user->getEmail();
             $name = $user->getName();
             $avatar = $user->getAvatar();

             $password = (string) random_int(1000000000, 9999999999);
             $hashedpassword = password_hash($password, PASSWORD_DEFAULT);

            $user = new User();

            $user->setEmail($email);
            $user->setVerified(true);
            $user->setImageName($avatar);
            $user->setVerificationCode('123456');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($hashedpassword);

            $manager->persist($user);
            $manager->flush();

             $security->login($user, 'form_login', 'main');

             // Redirection après traitement
             return $this->redirectToRoute('app_home');

         } catch (IdentityProviderException $e) {
             // En cas d'erreur, afficher un message à l'utilisateur et le rediriger
             $this->addFlash('error', 'Erreur lors de la connexion avec Google : ' . $e->getMessage());

             // Redirection en cas d'erreur
             return $this->redirectToRoute('app_login');
         }
     }





}
