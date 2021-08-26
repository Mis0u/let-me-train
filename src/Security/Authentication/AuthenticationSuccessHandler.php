<?php

namespace App\Security\Authentication;

use App\Entity\User;
use App\Helper\Service\RedirectUserConnectedService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    private EntityManagerInterface $manager;
    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;
    private RedirectUserConnectedService $redirectUserConnectedService;

    public function __construct(
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        RedirectUserConnectedService $redirectUserConnectedService
    ) {
        $this->manager                      = $manager;
        $this->urlGenerator                 = $urlGenerator;
        $this->userRepository               = $userRepository;
        $this->redirectUserConnectedService = $redirectUserConnectedService;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        if ($token->getUser() instanceof User) {
            $user = $this->userRepository->find($token->getUser());

            if (null !== $user) {
                $user->setLoginAttempt(0);
                $user->setLastConnection(
                    new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'))
                );

                $this->manager->persist($user);
                $this->manager->flush();
            }
            return $this->redirectUserConnectedService->redirectToProfile($this->userRepository, $token->getUser());
        }
        return new RedirectResponse($this->urlGenerator->generate('app_logout'));
    }
}
