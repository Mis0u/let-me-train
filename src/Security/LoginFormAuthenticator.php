<?php

namespace App\Security;

use App\Entity\User;
use App\Helper\Service\LocalisationService;
use App\Helper\Service\RedirectUserConnectedService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;
    private RedirectUserConnectedService $redirectUserConnectedService;
    private EntityManagerInterface $manager;
    private LocalisationService $localisationService;
    private TranslatorInterface $translator;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        RedirectUserConnectedService $redirectUserConnectedService,
        EntityManagerInterface $manager,
        LocalisationService $localisationService,
        TranslatorInterface $translator
    ) {
        $this->urlGenerator                 = $urlGenerator;
        $this->userRepository               = $userRepository;
        $this->redirectUserConnectedService = $redirectUserConnectedService;
        $this->manager                      = $manager;
        $this->localisationService          = $localisationService;
        $this->translator                   = $translator;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge((string) $email),
            new PasswordCredentials((string) $request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        if ($token->getUser() instanceof User) {
            $user = $this->userRepository->find($token->getUser());

            if (null !== $user) {
                $user->setLoginAttempt(0);
                $user->setLastConnection(
                    new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'))
                );

                $this->manager->persist($user);
                $this->manager->flush();
                return $this->redirectUserConnectedService->redirectToProfile($this->userRepository, $token->getUser());
            }
        }

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }


    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
