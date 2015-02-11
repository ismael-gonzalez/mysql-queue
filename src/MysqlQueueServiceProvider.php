<?php namespace Mysql\Queue;

use Illuminate\Support\ServiceProvider;
use Mysql\Queue\Connectors\MysqlQueueConnector;

/**
 * Mysql Queue Service Provider class
 *
 * @author Ismael
 */
class MysqlQueueServiceProvider extends ServiceProvider{

  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = false;

  /**
     * Add the connector to the queue drivers
     */
  public function boot(){
    $this->package('ismael-gonzalez/mysql-queue');
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