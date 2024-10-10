<?php

namespace NyroDev\UtilityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Rem2pxCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('nyrodev:rem2px')
            ->setDescription('Convert CSS files into px')
            ->addArgument('src', InputArgument::REQUIRED, 'Which files to parse?')
            ->addArgument('dst', InputArgument::REQUIRED, 'Where to save the file?')
            ->addArgument('selector', InputArgument::OPTIONAL, 'Selector for files to parse?', '*')
            ->addOption('append', 'a', InputOption::VALUE_NONE, 'Append to dst file?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $src = $input->getArgument('src');
        $dst = $input->getArgument('dst');
        $selector = $input->getArgument('selector');
        $append = $input->getOption('append');

        // Create CSS dest object
        $dstCss = new \Sabberworm\CSS\Parser($append && file_exists($dst) ? file_get_contents($dst) : null);
        $dstCssDoc = $dstCss->parse();

        $finder = new Finder();
        $resources = $finder
                    ->files()
                    ->depth(0)
                    ->in($src)
                    ->name($selector.'.css');

        foreach ($resources as $res) {
            $output->writeln('CSS Found: '.$res->getRealPath());
            $css = new \Sabberworm\CSS\Parser(file_get_contents($res->getRealPath()));
            $cssDoc = $css->parse();
            foreach ($cssDoc->getContents() as $block) {
                if ($block instanceof \Sabberworm\CSS\RuleSet\DeclarationBlock) {
                    /* @var $ruleset \Sabberworm\CSS\RuleSet\DeclarationBlock */
                    // Keep only the root CSS (ignore media queries)
                    $keepRules = [];
                    foreach ($block->getRules() as $rule) {
                        /* @var $rule \Sabberworm\CSS\Rule\Rule */
                        $value = $rule->getValue();
                        if (is_object($value)) {
                            // keep only object with size rem
                            $keepValue = false;
                            switch (get_class($value)) {
                                case 'Sabberworm\CSS\Value\Size':
                                    /* @var $value \Sabberworm\CSS\Value\Size */
                                    if ('rem' == $value->getUnit()) {
                                        $value->setSize($value->getSize() * 10);
                                        $value->setUnit('px');
                                        $keepValue = true;
                                    }
                                    break;
                                case 'Sabberworm\CSS\Value\RuleValueList':
                                    /* @var $value \Sabberworm\CSS\Value\RuleValueList */
                                    foreach ($value->getListComponents() as $c) {
                                        if ($c instanceof \Sabberworm\CSS\Value\Size) {
                                            if ('rem' == $c->getUnit()) {
                                                $c->setSize($c->getSize() * 10);
                                                $c->setUnit('px');
                                                $keepValue = true;
                                            }
                                        }
                                    }
                                    break;
                            }
                            if ($keepValue) {
                                $keepRules[] = $rule;
                            }
                        }
                    }

                    if (count($keepRules)) {
                        $oItem = new \Sabberworm\CSS\RuleSet\DeclarationBlock();
                        $oItem->setSelectors($block->getSelectors());
                        foreach ($keepRules as $rule) {
                            $oItem->addRule($rule);
                        }

                        $dstCssDoc->append($oItem);
                    }
                }
            }
        }

        $output->writeln('Write '.$dst);
        file_put_contents($dst, $dstCssDoc->render(\Sabberworm\CSS\OutputFormat::createPretty()));

        return Command::SUCCESS;
    }
}
