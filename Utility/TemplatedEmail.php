<?php

namespace NyroDev\UtilityBundle\Utility;

use Symfony\Bridge\Twig\Mime\TemplatedEmail as MimeTemplatedEmail;

class TemplatedEmail extends MimeTemplatedEmail
{
    public function setTemplate(string $template): self
    {
        return $this
            ->textTemplate('emails/'.$template.'.text.php')
            ->htmlTemplate('emails/'.$template.'.html.php')
        ;
    }

    public function getContext(): array
    {
        return array_merge([
            'subject' => $this->getSubject(),
        ], parent::getContext());
    }
}
