<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{

    private UrlGeneratorInterface $urlGenerator;
    private FlashBagInterface $flash;
    private TranslatorInterface $translator;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        FlashBagInterface $flash,
        TranslatorInterface $translator
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->flash        = $flash;
        $this->translator   = $translator;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->flash->add('danger', $this->translator->trans('user.link_expired.password_forgotten'));
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
