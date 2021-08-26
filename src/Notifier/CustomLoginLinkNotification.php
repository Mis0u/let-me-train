<?php

namespace App\Notifier;

use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomLoginLinkNotification extends \Symfony\Component\Security\Http\LoginLink\LoginLinkNotification
{
    private LoginLinkDetails $loginLinkDetails;
    private TranslatorInterface $translator;

    /**
     * @param array<string> $channels
     */
    public function __construct(
        LoginLinkDetails $loginLinkDetails,
        TranslatorInterface $translator,
        string $subject,
        array $channels = []
    ) {
        parent::__construct($loginLinkDetails, $subject, $channels);

        $this->loginLinkDetails = $loginLinkDetails;
        $this->translator       = $translator;
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $emailMessage = parent::asEmailMessage($recipient, $transport);

        // get the NotificationEmail object and override the template
        if (null !== $emailMessage) {
            /** @var NotificationEmail $email */
            $email = $emailMessage->getMessage();
            $email->from('admin@example.com');
            $email->content($this->getLeftTime());
            $email->htmlTemplate('email/custom_login_link_email.html.twig');
        }

        return $emailMessage;
    }

    public function getLeftTime(): string
    {
        $duration = $this->loginLinkDetails->getExpiresAt()->getTimestamp() - time();
        $durationString =
            floor($duration / 60) . ' minute' . ($duration > 60 ? 's' : '');
        if (($hours = $duration / 3600) >= 1) {
            $durationString =
                floor($hours) . ' ' . $this->translator->trans('hour.login_link.email') . ($hours >= 2 ? 's' : '');
        }

        return $durationString;
    }
}
