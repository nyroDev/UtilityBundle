<?php
namespace NyroDev\UtilityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

/**
 * Symfony2 command to copy CSS images in public directories into web directories 
 */
class cssImagesCommand extends ContainerAwareCommand {
	
	/**
	 * Configure the command 
	 */
	protected function configure() {
		$this
			->setName('nyrodev:cssImages')
			->setDescription('Publish CSS Images');
	}
	
	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \RuntimeException 
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Searching for CSS images');
		
		$finder = new Finder();
		$finderRes = new Finder();
		$resources = $finderRes
					->directories()
					->depth(2)
					->in('./src/')
					->name('Resources');
		
		$ds = DIRECTORY_SEPARATOR;
		$found = false;
		$subFolders = array();
		$subFolder = $ds.'public'.$ds.'css'.$ds.'images'.$ds;
		foreach($resources as $res) {
			$resPath = $res->getRealpath();
			if (file_exists($resPath.$subFolder)) {
				$found = true;
				$finder->in($resPath.$subFolder);
				$subFolders[] = $resPath.$subFolder;
			}
		}
		
		if ($found) {
			$dest = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
			if (!file_exists($dest)) {
				if (false === @mkdir($dest, 0777, true)) {
					throw new \RuntimeException('Unable to create directory '.$dest);
				}
				$output->writeln('Directory creeated: '.$dest);
			}
			$imgs = $finder->files();
			foreach($imgs as $img) {
				$d = $dest.str_replace($subFolders, '', $img->getRealPath());
				$dir = dirname($d);
				if (!file_exists($dir)) {
					if (false === @mkdir($dir, 0777, true)) {
						throw new \RuntimeException('Unable to create directory '.$dir);
					}
					$output->writeln('Directory creeated: '.$dir);
				}
				copy($img->getRealPath(), $d);
				$output->writeln('Copying: '.$img->getRealPath());
			}
		} else {
			$output->writeln('<comment>No images folder found</comment>');
		}
		
	}
}