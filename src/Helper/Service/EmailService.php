<?php

namespace App\Helper\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    /**
     * @param array<string> $context
     */
    public function send(
        MailerInterface $mailer,
        string $userEmail,
        string $subject,
        string $template,
        array $context
    ): void {
        $email = (new TemplatedEmail())
            ->from('letmetrain@gmail.com')
            ->to(new Address($userEmail))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $mailer->send($email);
    }
}
