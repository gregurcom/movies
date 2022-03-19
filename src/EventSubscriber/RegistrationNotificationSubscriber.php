<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\RegistrationEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class RegistrationNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer, private string $sender) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RegistrationEvent::class => 'onRegistration',
        ];
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onRegistration(RegistrationEvent $event): void
    {
        $user = $event->getUser();

        $email = (new TemplatedEmail())
            ->from($this->sender)
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Welcome to symfony-app!')
            ->htmlTemplate('email/welcome.html.twig');

        // In config/packages/dev/mailer.yaml the delivery of messages is disabled.
        // That's why in the development environment you won't actually receive any email.
        // However, you can inspect the contents of those unsent emails using the debug toolbar.
        $this->mailer->send($email);
    }
}
