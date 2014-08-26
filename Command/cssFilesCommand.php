<?php
namespace NyroDev\UtilityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

/**
 * Symfony2 command to copy CSS images in public directories into web directories 
 */
class cssFilesCommand extends ContainerAwareCommand {
	
	/**
	 * Configure the command 
	 */
	protected function configure() {
		$this
			->setName('nyrodev:cssFiles')
			->setDescription('Publish CSS Files');
	}
	
	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \RuntimeException 
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Searching for CSS files');
		
		$finder = new Finder();
		$finderRes = new Finder();
		$resources = $finderRes
					->directories()
					->depth(2)
					->in('./src/')
					->name('Resources');
		
		$ds = DIRECTORY_SEPARATOR;
		$found = false;
		$hasFonts = false;
		$subFolders = array();
		$subFolderTests = array(
			$ds.'public'.$ds.'css'.$ds.'images'.$ds,
			$ds.'public'.$ds.'css'.$ds.'fonts'.$ds,
		);
		foreach($resources as $res) {
			$resPath = $res->getRealpath();
			foreach($subFolderTests as $subFolder) {
				if (file_exists($resPath.$subFolder)) {
					$found = true;
					$finder->in($resPath.$subFolder);
					$subFolders[] = $resPath.$subFolder;
					$tmp = explode($ds, $subFolder);
					if ($tmp[count($tmp)-2] == 'fonts')
						$hasFonts = true;
				}
			}
		}
		
		if ($found) {
			$dest = $this->getContainer()->getParameter('kernel.root_dir').$ds.'..'.$ds.'web'.$ds.'css'.$ds.'images'.$ds;
			if (!file_exists($dest)) {
				if (false === @mkdir($dest, 0777, true)) {
					throw new \RuntimeException('Unable to create directory '.$dest);
				}
				$output->writeln('Directory creeated: '.$dest);
			}
			if ($hasFonts) {
				$destFonts = $this->getContainer()->getParameter('kernel.root_dir').$ds.'..'.$ds.'web'.$ds.'css'.$ds.'fonts'.$ds;
				if (!file_exists($destFonts)) {
					if (false === @mkdir($destFonts, 0777, true)) {
						throw new \RuntimeException('Unable to create directory '.$destFonts);
					}
					$output->writeln('Directory creeated: '.$destFonts);
				}
			}
			$imgs = $finder->files();
			foreach($imgs as $img) {
				$d = ($hasFonts && strpos($img->getRealPath(), $ds.'fonts'.$ds) !== false ? $destFonts : $dest).str_replace($subFolders, '', $img->getRealPath());
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
			$output->writeln('<comment>No images or fonts folder found</comment>');
		}
		
	}
}