<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendPurchaseConfirmation(string $to, string $purchaseDetails)
    {
        $email = (new Email())
            ->from('noreply@minicatory.com')
            ->to($to)
            ->subject('Purchase Confirmation')
            ->text("Thank you for your purchase!\n\nYour purchase details:\n\n$purchaseDetails");

        $this->mailer->send($email);
    }
}
