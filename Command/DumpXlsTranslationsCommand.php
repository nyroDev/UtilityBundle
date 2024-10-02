<?php

namespace NyroDev\UtilityBundle\Command;

use NyroDev\UtilityBundle\Services\NyrodevService;
use PhpExcel_Cell;
use PHPExcel_IOFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

/**
 * Symfony2 command to update confidentielles tags.
 */
class DumpXlsTranslationsCommand extends Command
{
    protected $nyrodev;

    public function __construct(NyrodevService $nyrodev)
    {
        $this->nyrodev = $nyrodev;

        parent::__construct();
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('nyrodev:dumpXlsTranslations')
            ->setDescription('Dump XLS file into YAML translations')
            ->addArgument('file', InputArgument::REQUIRED, 'XLS file')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Directory to save in', '.');
    }

    protected $locales = [];

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->nyrodev->increasePhpLimits();
        $file = $input->getArgument('file');
        $dir = $input->getArgument('dir');

        $output->writeln('Open XLS file');
        $fileType = PHPExcel_IOFactory::identify($file);
        $objReader = PHPExcel_IOFactory::createReader($fileType);

        $phpExcel = $objReader->load($file);
        $sheet = $phpExcel->getActiveSheet();
        $maxCol = PhpExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn(1));
        $maxRow = $sheet->getHighestDataRow();

        $localesCols = [];
        for ($i = 3; $i < $maxCol; ++$i) {
            $locale = $sheet->getCellByColumnAndRow($i, 1)->getValue();
            $localesCols[$i] = $locale;
            $this->locales[$locale] = [];
        }

        $output->writeln('Parse XLS file');
        for ($i = 2; $i <= $maxRow; ++$i) {
            $domain = $sheet->getCellByColumnAndRow(0, $i)->getValue();
            $ident = $sheet->getCellByColumnAndRow(1, $i)->getValue();
            $idents = explode('.', $ident);
            for ($j = 3; $j < $maxCol; ++$j) {
                $this->addTrans($localesCols[$j], $domain, $idents, $sheet->getCellByColumnAndRow($j, $i)->getValue());
            }
        }

        $output->writeln('Dump YML files');
        $dumper = new Dumper();
        $fs = new Filesystem();

        foreach ($this->locales as $locale => $translations) {
            foreach ($translations as $domain => $trans) {
                $output->writeln($dir.'/'.$domain.'.'.$locale.'.yaml');
                $fs->dumpFile($dir.'/'.$domain.'.'.$locale.'.yaml', $dumper->dump($trans, 99));
            }
        }

        return Command::SUCCESS;
    }

    protected function addTrans($locale, $domain, array $idents, $trans)
    {
        if (!isset($this->locales[$locale])) {
            $this->locales[$locale] = [];
        }
        if (!isset($this->locales[$locale][$domain])) {
            $this->locales[$locale][$domain] = [];
        }

        $this->addTransRec($this->locales[$locale][$domain], $idents, $trans);
    }

    protected function addTransRec(array &$values, array $idents, $trans)
    {
        if (1 == count($idents)) {
            $values[$idents[0]] = $trans;
        } else {
            $key = array_shift($idents);
            $idents = array_filter($idents);
            if (!isset($values[$key])) {
                $values[$key] = [];
            }

            $this->addTransRec($values[$key], $idents, $trans);
        }
    }
}
