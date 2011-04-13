<?php

/*
 * This file is part of the sfDependencyInjectionContainerPlugin
 * (c) Noel Guilbert <noelguilbert@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDependencyInjectionContainerPlugin configuration.
 *
 * @package     sfDependencyInjectionContainerPlugin
 * @subpackage  task
 * @author      Noel Guilbert
 * @version     SVN: $Id: sfServiceContainerDumperGraphviz.class.php $
 */
class sfServiceContainerDumpGraphvizTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'services';
    $this->name             = 'dump-graphviz';
    $this->briefDescription = 'generates a \'dot\' representation of your service container';
    $this->detailedDescription = <<<EOF
The [services:dump-graphiz|INFO] task dump generates a dot representation of your service container
Call it with:

  [php symfony services:dump-graphiz|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $sc = $this->configuration->getServiceContainer();
    $dumper = new sfServiceContainerDumperGraphviz($sc);
    file_put_contents(sfConfig::get('sf_data_dir').'/container.dot', $dumper->dump());
  }
}
