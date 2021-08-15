<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Helper\Service\LocalisationService;
use App\Helper\Service\RedirectUserConnectedService;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public const REDIRECT_URL_CAPTCHA = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    public const LOCALISATION = 'http://ip-api.com/json';

    /**
     * @Route("/", name="app_login")
     */
    public function login(
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepo,
        RedirectUserConnectedService $redirectUserConnected
    ): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            return $redirectUserConnected->redirectToProfile($userRepo, $this->getUser());
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
    * @Route("/inscription", name="app_register")
    */
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $passwordHasher,
        LocalisationService $localisationService,
        UserAuthenticatorInterface $authenticator,
        LoginFormAuthenticator $loginFormAuthenticator,
        UserRepository $userRepo,
        RedirectUserConnectedService $redirectUserConnected
    ): ?Response {

        if ($this->getUser() && $this->getUser() instanceof User) {
            return $redirectUserConnected->redirectToProfile($userRepo, $this->getUser());
        }
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $captchaValue = $form['captcha']->getData();
            $plainPassword = $form['plainPassword']->getData();
            $localisation = $localisationService->getApi(self::LOCALISATION);

            if (isset($captchaValue)) {
                return $this->redirect(self::REDIRECT_URL_CAPTCHA);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword))
                 ->setCountry($localisation['country'])
                 ->setLastConnection(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
                ;
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', $translator->trans('user.registration.welcome', [], 'messages'));

            return $authenticator->authenticateUser(
                $user,
                $loginFormAuthenticator,
                $request
            );
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }
}
