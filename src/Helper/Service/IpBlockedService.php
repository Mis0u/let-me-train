<?php

namespace App\Helper\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IpBlockedService
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function redirect(): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_ip_block'));
    }
}
