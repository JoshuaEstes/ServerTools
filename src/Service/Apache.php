<?php

namespace Service;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Apache
 * @version
 */
use Symfony\Component\Process\Process;
use Service\Service;

class Apache extends Service
{

  public function start()
  {
    return $this->doRun('start');
  }

  public function stop()
  {
    return $this->doRun('stop');
  }

  public function reload()
  {
    return $this->doRun('reload');
  }

  public function status()
  {
    return $this->doRun('status');
  }

  public function restart()
  {
    return $this->doRun('restart');
  }

  protected function doRun($name)
  {
    $p = new Process('which apachectl');
    $p->run();
    $apachectl = trim($p->getOutput());
    if (\strlen($apachectl) > 0)
    {
      $process = new Process(sprintf('sudo %s -k %s', $apachectl, $name));
      $output = $this->output;
      $process->run(function($type, $buffer) use($output)
        {
          $output->write($buffer);
        }
      );
      return $process->getExitCode();
    }
    else
    {
      throw Exception('Could not find "apachectl"');
    }
  }

}