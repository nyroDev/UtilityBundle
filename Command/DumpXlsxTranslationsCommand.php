<?php

namespace NyroDev\UtilityBundle\Command;

use NyroDev\UtilityBundle\Services\NyrodevService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

class DumpXlsxTranslationsCommand extends Command
{
    private array $locales = [];

    public function __construct(
        private readonly NyrodevService $nyrodev,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('nyrodev:dumpXlsxTranslations')
            ->setDescription('Dump XLS file into YAML translations')
            ->addArgument('file', InputArgument::REQUIRED, 'XLS file')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Directory to save in', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->nyrodev->increasePhpLimits();
        $file = $input->getArgument('file');
        $dir = $input->getArgument('dir');

        $output->writeln('Open XLSX file');

        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $maxCol = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn(1));
        $maxRow = $worksheet->getHighestDataRow();

        $localesCols = [];
        for ($i = 4; $i <= $maxCol; ++$i) {
            $locale = $worksheet->getCell([$i, 1])->getValue();
            $localesCols[$i] = $locale;
            $this->locales[$locale] = [];
        }

        $output->writeln('Parse XLS file');
        for ($i = 2; $i <= $maxRow; ++$i) {
            $domain = $worksheet->getCell([1, $i])->getValue();
            $ident = $worksheet->getCell([2, $i])->getValue();
            $idents = explode('.', $ident);
            for ($j = 4; $j <= $maxCol; ++$j) {
                $this->addTrans($localesCols[$j], $domain, $idents, $worksheet->getCell([$j, $i])->getValue());
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

    private function addTrans(string $locale, string $domain, array $idents, ?string $trans = null): void
    {
        if (!isset($this->locales[$locale])) {
            $this->locales[$locale] = [];
        }
        if (!isset($this->locales[$locale][$domain])) {
            $this->locales[$locale][$domain] = [];
        }

        $this->addTransRec($this->locales[$locale][$domain], $idents, $trans);
    }

    private function addTransRec(array &$values, array $idents, ?string $trans = null)
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
