<?php

namespace App\Mail\Transport;

use App\Services\GoogleMailService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class GmailTransport extends AbstractTransport
{
    protected $googleMailService;

    public function __construct(GoogleMailService $googleMailService)
    {
        parent::__construct();
        $this->googleMailService = $googleMailService;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $this->googleMailService->sendRawEmail($email->toString());
    }

    public function __toString(): string
    {
        return 'gmail';
    }
}
