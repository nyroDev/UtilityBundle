<?php

namespace NyroDev\UtilityBundle\Utility;

use PHPExcel;
use PHPExcel_IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhpExcelResponse extends StreamedResponse
{
    public function setPhpExcel(string $filename, PHPExcel $phpExcel, string $phpExcelFormat = 'Excel2007'): void
    {
        $this->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');
        $this->setPrivate();
        $this->headers->addCacheControlDirective('no-cache', true);
        $this->headers->addCacheControlDirective('must-revalidate', true);

        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, $phpExcelFormat);
        $this->setCallback(function () use ($objWriter) {
            $objWriter->save('php://output');
        });
    }
}
