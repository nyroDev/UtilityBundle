<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait MailerInterfaceServiceableTrait
{
    protected ?MailerInterface $mailerInterface = null;

    #[Required]
    public function setMailerInterface(MailerInterface $mailerInterface): void
    {
        $this->mailerInterface = $mailerInterface;
    }

    protected function getMailerInterface(): ?MailerInterface
    {
        return $this->mailerInterface;
    }
}
