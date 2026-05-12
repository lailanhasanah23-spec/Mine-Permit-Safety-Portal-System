<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SimperSubmissionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, array{path: string, as: string, mime: string}>  $attachments
     */
    public function __construct(
        private readonly string $mailSubject,
        private readonly string $htmlBody,
        private readonly array $attachments = []
    ) {}

    public function build(): self
    {
        $message = $this->subject($this->mailSubject)
            ->from(
                (string) config('admin_email_workflow.from_address', (string) config('mail.from.address')),
                (string) config('admin_email_workflow.from_name', (string) config('mail.from.name'))
            )
            ->html($this->htmlBody);

        foreach ($this->attachments as $attachment) {
            $path = (string) ($attachment['path'] ?? '');
            if ($path === '' || ! is_file($path)) {
                continue;
            }

            $message->attach($path, [
                'as' => (string) ($attachment['as'] ?? basename($path)),
                'mime' => (string) ($attachment['mime'] ?? 'application/octet-stream'),
            ]);
        }

        return $message;
    }
}
