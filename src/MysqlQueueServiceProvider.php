<?php namespace Mysql\Queue;

use Illuminate\Support\ServideProvider;
use Mysql\Queue\Connectors\MysqlQueueConnector;

/**
 * Mysql Queue Service Provider class
 *
 * @author Ismael
 */
class MysqlQueueServiceProvider extends ServideProvider{

  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = true;

  /**
     * Add the connector to the queue drivers
     */
  public function boot(){
    $manager = $this->app['queue'];
    $manager->addConnector('mysql', function()
    {
        return new MysqlQueueConnector;
    });
  }

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {

  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return array();
  }

}