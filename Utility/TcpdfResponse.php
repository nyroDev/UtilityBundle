<?php

namespace NyroDev\UtilityBundle\Utility;

use Symfony\Component\HttpFoundation\Response;
use TCPDF;

class TcpdfResponse extends Response
{
    public function setTcpdf(TCPDF $tcpdf, bool $fileDownload = false): void
    {
        $this->headers->set('Content-Type', 'application/pdf');
        $this->setContent($tcpdf->Output('export.pdf', 'S'));

        if ($fileDownload) {
            $this->headers->set('Content-Disposition', 'attachment;filename="'.$fileDownload.'"');
            $this->setPrivate();
            $this->headers->addCacheControlDirective('no-cache', true);
            $this->headers->addCacheControlDirective('must-revalidate', true);
        }
    }
}
