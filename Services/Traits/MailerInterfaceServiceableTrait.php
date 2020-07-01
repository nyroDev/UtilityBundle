<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\Mailer\MailerInterface;

trait MailerInterfaceServiceableTrait
{
    protected $mailerInterface;

    /**
     * @Required
     */
    public function setMailerInterface(MailerInterface $mailerInterface)
    {
        $this->mailerInterface = $mailerInterface;
    }

    protected function getMailerInterface(): MailerInterface
    {
        return $this->mailerInterface;
    }
}
