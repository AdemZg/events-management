<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
        #[Route('/register', name: 'register')]
        public function register(Request $request,  UserPasswordEncoderInterface $passwordEncoder)
        {
                $user = new User;
                $form = $this->createForm(UserType::class, $user);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('login');
                }
                return $this->render('security/register.html.twig',
                array('form' => $form->createView())
                );
        }

        #[Route('/login', name: 'login')]
        public function login(AuthenticationUtils $authenticationUtils)
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
        #[Route('/logout', name: 'logout')]
        public function logoutAction(): void
        {
                throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        }
}
