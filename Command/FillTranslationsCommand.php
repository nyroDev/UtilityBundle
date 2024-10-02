<?php

namespace NyroDev\UtilityBundle\Command;

use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

/**
 * Symfony2 command to update confidentielles tags.
 */
class FillTranslationsCommand extends Command
{
    protected $nyrodev;
    protected $db;

    public function __construct(NyrodevService $nyrodev, DbAbstractService $db)
    {
        $this->nyrodev = $nyrodev;
        $this->db = $db;

        parent::__construct();
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('nyrodev:fillTranslations')
            ->setDescription('Fill translations database')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Directory to search in', 'src')
            ->addArgument('suffix', InputArgument::OPTIONAL, 'Translation file suffix', '')
            ->addArgument('extension', InputArgument::OPTIONAL, 'Translation file extension', 'yml');
    }

    protected $existing;
    protected $className;
    protected $accessor;

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->nyrodev->increasePhpLimits();
        $dir = $input->getArgument('dir');
        $suffix = $input->getArgument('suffix');
        $extension = $input->getArgument('extension');

        $translationDb = $this->nyrodev->getParameter('nyroDev_utility.translationDb');
        if (!$translationDb) {
            $output->writeln('translationDB is not configured, exiting');

            return Command::INVALID;
        }

        $locale = $this->nyrodev->getParameter('locale');
        $locales = explode('|', $this->nyrodev->getParameter('locales'));
        if (0 == count($locales)) {
            $output->writeln('locales is not configured or empty, exiting');

            return Command::INVALID;
        }

        $output->writeln('Search for original translations files');
        $originals = [];
        $finder = new Finder();
        $translations = $finder
                    ->directories()
                    ->in('./'.$dir)
                    ->name('translations');

        foreach ($translations as $translation) {
            $finderTr = new Finder();
            $files = $finderTr->files()->in($translation->getRealpath())->name('*.'.$locale.$suffix.'.'.$extension);
            foreach ($files as $file) {
                $originals[] = $file->getRealpath();
            }
        }

        $nbO = count($originals);
        if ($nbO) {
            $repo = $this->db->getRepository($translationDb);
            $this->className = $repo->getClassName();
            $this->accessor = PropertyAccess::createPropertyAccessor();

            $output->writeln('Retrieving existing translations');
            $this->existing = [];
            foreach ($repo->findAll() as $row) {
                $this->existing[$row->getDomain().'-'.$row->getLocale().'-'.$row->getIdent()] = true;
            }

            $output->writeln('Parsing '.$nbO.' translation files');
            $nbTrunc = -2 - strlen($extension) - strlen($locale) - strlen($suffix);
            $fs = new Filesystem();
            $nb = 0;
            foreach ($originals as $original) {
                $output->writeln('Start fetching '.$original);
                $domain = substr(basename($original), 0, $nbTrunc);
                $dir = dirname($original);

                $trans = Yaml::parse(file_get_contents($original));

                foreach ($locales as $loc) {
                    $curTrans = $trans;
                    if ($loc != $locale) {
                        $default = $dir.'/'.$domain.'.'.$loc.$suffix.'.'.$extension;
                        if ($fs->exists($default)) {
                            $curTrans = $this->mergeTr($curTrans, Yaml::parse(file_get_contents($default)));
                        }
                    }
                    $cur = $this->createNewTrans($domain, $loc, $curTrans);
                    $output->writeln($loc.' : '.$cur);
                    $nb += $cur;
                }
            }

            $output->writeln('Added translations: '.$nb);
            $output->writeln('Flushing...');
            $this->db->flush();

            return Command::SUCCESS;
        } else {
            $output->writeln('No original translation files found.');

            return Command::INVALID;
        }
    }

    protected function mergeTr(array $tr1, array $tr2)
    {
        $ret = $tr1;
        foreach ($tr2 as $k => $v) {
            if (isset($ret[$k])) {
                if (is_array($ret[$k]) && is_array($v)) {
                    $ret[$k] = $this->mergeTr($ret[$k], $v);
                } else {
                    $ret[$k] = $v;
                }
            } else {
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    protected function createNewTrans($domain, $locale, array $trans, $prefix = null)
    {
        $nb = 0;
        if (!is_null($prefix)) {
            $prefix .= '.';
        }
        foreach ($trans as $k => $v) {
            $curPrefix = $prefix.$k;
            $key = $domain.'-'.$locale.'-'.$curPrefix;
            if (is_array($v)) {
                $nb += $this->createNewTrans($domain, $locale, $v, $curPrefix);
            } elseif (!isset($this->existing[$key])) {
                $this->existing[$key] = true;
                $row = new $this->className();
                $row->setDomain($domain);
                $row->setLocale($locale);
                $row->setIdent($curPrefix);
                $row->setTranslation(trim($v).'');
                $this->db->persist($row);
                ++$nb;
            }
        }

        return $nb;
    }
}
