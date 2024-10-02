<?php

namespace NyroDev\UtilityBundle\Utility;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhpSpreadsheetResponse extends StreamedResponse
{
    public function setPhpSpreadsheet(string $filename, Spreadsheet $spreadsheet, string $format = 'Ods'): void
    {
        $this->headers->set('Content-Type', 'application/vnd.oasis.opendocument.spreadsheet');
        $this->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');
        $this->setPrivate();
        $this->headers->addCacheControlDirective('no-cache', true);
        $this->headers->addCacheControlDirective('must-revalidate', true);

        $writer = IOFactory::createWriter($spreadsheet, $format);
        $this->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
    }
}
