<?php

namespace Service;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Service
 * @version
 */
interface ServiceInterface
{
  public function start();

  public function stop();

  public function restart();

  public function reload();

  public function status();
}