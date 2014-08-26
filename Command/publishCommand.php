<?php
namespace NyroDev\UtilityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Symfony2 command to call every command requested to publish all needed files 
 */
class publishCommand extends ContainerAwareCommand {
	
	/**
	 * Configure the command
	 */
	protected function configure() {
		$this
			->setName('nyrodev:publish')
			->setDescription('Publish All needed files');
	}
	
	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output 
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$args = array(
			'command'=>'nyrodev:cssFiles',
			'--env'=>'prod',
			'--no-debug'=>true
		);
		$command = $this->getApplication()->find($args['command']);
		$command->run(new ArrayInput($args), $output);
		
		$args = array(
			'command'=>'assetic:dump',
			'--env'=>'prod',
			'--no-debug'=>true
		);
		$command = $this->getApplication()->find($args['command']);
		$command->run(new ArrayInput($args), $output);
		
		$args = array(
			'command'=>'assets:install',
			'--env'=>'prod',
			'--no-debug'=>true
		);
		$command = $this->getApplication()->find($args['command']);
		$command->run(new ArrayInput($args), $output);
	}
}