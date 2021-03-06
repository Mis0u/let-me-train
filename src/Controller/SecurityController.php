<?php

namespace App\Controller;

use App\Entity\LoginAttempt;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Helper\Service\EmailService;
use App\Helper\Service\IpBlockedService;
use App\Helper\Service\LocalisationService;
use App\Helper\Service\RedirectUserConnectedService;
use App\Notifier\CustomLoginLinkNotification;
use App\Repository\LoginAttemptRepository;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public const REDIRECT_URL_CAPTCHA = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    public const BAD_CREDENTIALS = "Bad credentials.";
    public const WRONG_EMAIL = "The presented password is invalid.";
    public const ATTEMPT_LOGIN = 5;
    public const TEMPLATE_EMAIL = 'email/registration.html.twig';
    public const TEMPLATE_EMAIL_BLOCK = 'email/account_blocked.html.twig';

    /**
     * @Route("/", name="app_login")
     */
    public function login(
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepo,
        RedirectUserConnectedService $redirectUserConnected,
        EntityManagerInterface $manager,
        Request $request,
        LocalisationService $localisationService,
        IpBlockedService $ipBlockedService,
        LoginAttemptRepository $loginAttemptRepository,
        EmailService $emailService,
        MailerInterface $mailer,
        TranslatorInterface $translator
    ): Response {
        if ($this->getUser() && $this->getUser() instanceof User) {
            return $redirectUserConnected->redirectToProfile($userRepo, $this->getUser());
        }

        if ($loginAttemptRepository->countRecentLoginAttempts((string)$request->getClientIp()) >= self::ATTEMPT_LOGIN) {
            return $ipBlockedService->redirect();
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            if ($error->getMessage() === self::BAD_CREDENTIALS) {
                $localisation = $localisationService->getApi();
                $loginAttempt = new LoginAttempt($request->getClientIp(), $localisation['country'], $lastUsername);

                $manager->persist($loginAttempt);
                $manager->flush();
            }

            if ($error->getMessage() === self::WRONG_EMAIL) {
                $user = $userRepo->findBy(['email' => $lastUsername]);

                $user[0]->setLoginAttempt($user[0]->getLoginAttempt() + 1);

                $manager->persist($user[0]);
                $manager->flush();

                if ($user[0]->getLoginAttempt() >= self::ATTEMPT_LOGIN) {
                    $user[0]->setIsBlockedByAttempt(true);

                    $manager->persist($user[0]);
                    $manager->flush();
                }
            }
        }

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
        RedirectUserConnectedService $redirectUserConnected,
        LoginAttemptRepository $loginAttemptRepository,
        IpBlockedService $ipBlockedService,
        MailerInterface $mailer,
        EmailService $emailService
    ): ?Response {

        if ($this->getUser() && $this->getUser() instanceof User) {
            return $redirectUserConnected->redirectToProfile($userRepo, $this->getUser());
        }

        if ($loginAttemptRepository->countRecentLoginAttempts((string)$request->getClientIp()) >= self::ATTEMPT_LOGIN) {
            return $ipBlockedService->redirect();
        }

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $captchaValue = $form['captcha']->getData();
            $plainPassword = $form['plainPassword']->getData();
            $localisation = $localisationService->getApi();

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

            //TODO envoyer en asynchrone
            $emailService->send(
                $mailer,
                (string)$user->getEmail(),
                $translator->trans('email.welcome.subject', [], 'messages'),
                self::TEMPLATE_EMAIL,
                ['pseudo' => $user->getAlias()]
            );


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
     * @Route("/acces-bloque", name="app_ip_block")
     */
    public function ipBlocked(
        UserRepository $userRepo,
        RedirectUserConnectedService $redirectUserConnected,
        LoginAttemptRepository $loginAttemptRepository,
        Request $request
    ): Response {
        if ($this->getUser() && $this->getUser() instanceof User) {
            return $redirectUserConnected->redirectToProfile($userRepo, $this->getUser());
        }

        if ($loginAttemptRepository->countRecentLoginAttempts((string)$request->getClientIp()) < self::ATTEMPT_LOGIN) {
            return new RedirectResponse('/');
        }


        return $this->render('security/ipBlock.html.twig');
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function check(): void
    {
        throw new \LogicException('This code should never be reached');
    }

    /**
    * @Route("/mot-de-passe-oublie", name="app_password_forgotten")
    */
    public function passwordForgotten(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        TranslatorInterface $translator,
        LoginAttemptRepository $loginAttemptRepository,
        IpBlockedService $ipBlockedService,
        EntityManagerInterface $manager,
        RedirectUserConnectedService $redirectUserConnected
    ): Response {
        if ($this->getUser() && $this->getUser() instanceof User) {
            return $redirectUserConnected->redirectToProfile($userRepository, $this->getUser());
        }
        if ($loginAttemptRepository->countRecentLoginAttempts((string)$request->getClientIp()) >= self::ATTEMPT_LOGIN) {
            return $ipBlockedService->redirect();
        }
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user === null) {
                $this->addFlash('danger', $translator->trans('user.email_unknown.password_forgotten'));
                return $this->redirectToRoute('app_password_forgotten');
            }

            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

            // create a notification based on the login link details
            $notification = new CustomLoginLinkNotification(
                $loginLinkDetails,
                $translator,
                $translator->trans('user.email_subject.password_forgotten') // email subject
            );
            // create a recipient for this user
            $recipient = new Recipient((string) $user->getEmail());

            // send the notification to the user
            $notifier->send($notification, $recipient);
            $user->setIsBlockedByAttempt(false);

            $manager->persist($user);
            $manager->flush();

            // render a "Login link is sent!" page
            return $this->render('security/login_link_sent.html.twig', [
                'user_email' => $user->getEmail()
            ]);
        }

        return $this->render('security/login_link_form.html.twig');
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
