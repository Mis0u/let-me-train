<?php

namespace App\Helper\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RedirectUserConnectedService
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function redirectToProfile(UserRepository $userRepository, UserInterface $user): Response
    {
        $user = $userRepository->find($user);

        if ($user !== null) {
            return new RedirectResponse(
                $this->urlGenerator->generate('app_user', ['slug' => $user->getSlug()])
            );
        } else {
            return new RedirectResponse(
                $this->urlGenerator->generate('app_login')
            );
        }
    }
}
