<?php

namespace App\Controller;

use App\Form\RegisterType;
use App\Form\VerificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Config\Security\FirewallConfig\LoginLinkConfig;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route(path: '/register', name: 'app_register')]
    public function register(Security $security, MailerInterface $mailer, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager)
    {
        $form = $this->createForm(RegisterType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $user->setRoles(['ROLE_USER']);

                $hashedPasssword = $passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                );

            $user->setPassword($hashedPasssword);
            $code = random_int(100000, 999999);

            $user->setVerificationCode($code);


            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'The registration is successfull');



            $email = (new Email())
                ->from('contact@benjaminruelle.com')
                ->to($user->getEmail())
                ->subject('Vérification de lemail')
                ->text('Sending emails is fun again!')
                ->html('<p>Voici votre code de vérification:' . $code .'</p>');

            $mailer->send($email);

            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_verification');


        }

        return $this->render('security/register.html.twig', [
            'form' => $form
        ]);


    }

    #[Route(path: '/verification', name: 'app_verification')]
    public function verifyCode(Request $request, SessionInterface $session, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(VerificationType::class);

        $form->handleRequest($request);

        $error = null;

        $form = $this->createForm(VerificationType::class);

        // Gérer la requête
        $form->handleRequest($request);

        $error = null;


        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $enteredCode = $data['verification_code'];


            $user = $this->getUser();


            // Récupérer le code stocké en session
            $storedCode = $user->getVerificationCode();

            // Vérifier si le code est correct
            if ($enteredCode == $storedCode) {
                $user->setVerified(true);

                $manager->persist($user);
                $manager->flush();

                // Rediriger vers la route de succès
                return $this->redirectToRoute('app_home');
            } else {
                $error = 'Le code entré est incorrect. Veuillez réessayer.';
            }
        }

        return $this->render('security/verification.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }
}
