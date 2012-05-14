<?php

namespace Test\Etc\Hosts;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package
 * @subpackage
 * @version
 */
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use Etc\Hosts\AddCommand;
use org\bovigo\vfs\vfsStream;

class AddCommandTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var  vfsStreamDirectory
   */
  private $root;

  /**
   * set up test environmemt
   */
  public function setUp()
  {
    $this->root = vfsStream::setup('exampleDir');
  }

  public function testExecute()
  {
    $hostsfile = '/tmp/' . \time();
    // Create the file
    $filesystem = new Filesystem();
    $filesystem->touch($hostsfile);

    $application = new Application();
    $application->add(new AddCommand());

    $command = $application->find('etc:hosts:add');
    $commandTester = new CommandTester($command);
    $commandTester->execute(
      array(
        'command' => $command->getName(),
        '--hostname' => 'test.local',
        '--hosts-file' => $hostsfile,
      ),
      array(
        'interactive' => false,
//        'decorated' => true,
        'verbosity' => true
      )
    );

    $contents = \file_get_contents($hostsfile);

    // remove the file
    $filesystem->remove($hostsfile);

    $this->assertContains("127.0.0.1 test.local", $contents);
  }

}