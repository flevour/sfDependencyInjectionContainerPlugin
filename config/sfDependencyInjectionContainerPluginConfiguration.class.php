<?php

/**
 * sfDependencyInjectionContainerPlugin configuration.
 *
 * @package     sfDependencyInjectionContainerPlugin
 * @subpackage  config
 * @author      Noel Guilbert 
 * @author      Francesco Levorato
 */
class sfDependencyInjectionContainerPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '0.5.0-DEV';
  protected
    $serviceContainer;

  /**
   * @see sfPluginConfiguration
   *
   * Initialize the service container
   * - connect the initializeServiceContainer() method to context.load_factories to start the container after core classes are setup
   * - connect the listenToMethodNotFound() method to *.method_not_found events to extend those area with getService[Container] methods
   */
  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'initializeServiceContainer'));

    $this->dispatcher->connect('configuration.method_not_found', array($this, 'listenToMethodNotFound'));
    $this->dispatcher->connect('context.method_not_found', array($this, 'listenToMethodNotFound'));
    $this->dispatcher->connect('component.method_not_found', array($this, 'listenToMethodNotFound'));
    $this->dispatcher->connect('form.method_not_found', array($this, 'listenToMethodNotFound'));
    $this->dispatcher->connect('response.method_not_found', array($this, 'listenToMethodNotFound'));
    $this->dispatcher->connect('user.method_not_found', array($this, 'listenToMethodNotFound'));
    $this->dispatcher->connect('view.method_not_found', array($this, 'listenToMethodNotFound'));
  }

  /**
   * Listener method for the method_not_found event
   * Calls the getServiceContainer() method
   *
   * @return boolean
   */
  public function listenToMethodNotFound($event)
  {
    if ('getServiceContainer' == $event['method'])
    {
      $event->setReturnValue($this->getServiceContainer());

      return true;
    }

    if ('getService' == $event['method'])
    {
      $event->setReturnValue($this->getServiceContainer()->getService($event['arguments'][0]));

      return true;
    }

    return false;
  }

  /**
   * Returns the current service container instance
   *
   * @return sfServiceContainer
   */
  public function getServiceContainer()
  {
    return $this->serviceContainer;
  }

  /**
   * Initialize the service container and cache it.
   *
   * Notify a service_container.load_configuration event.
   */
  public function initializeServiceContainer(sfEvent $event)
  {

    $application = sfConfig::get('sf_app');
    $debug       = sfConfig::get('sf_debug');
    $environment = sfConfig::get('sf_environment');
    $name = 'Project'.md5($application.$debug.$environment).'ServiceContainer';
    $file = sfConfig::get('sf_app_cache_dir') . '/' . $name.'.php';

    if (!$debug && file_exists($file))
    {
      require_once $file;
      $sc = new $name();
    }
    else
    {
      // build the service container dynamically
      $sc = new sfServiceContainerBuilder();
      $loader = new sfServiceContainerLoaderFileYaml($sc);

      // Single services_ENV.yml should import a common services.yml if they need to do so.
      $loader->load(sfConfig::get('sf_config_dir') . "/di/services_$environment.yml");

      $this->dispatcher->notify(new sfEvent($this->serviceContainer, 'service_container.load_configuration'));

      if (!$debug)
      {
        $dumper = new sfServiceContainerDumperPhp($sc);

        file_put_contents($file, $dumper->dump(array('class' => $name)));
      }
    }
    $this->serviceContainer = $sc;
  }
}
