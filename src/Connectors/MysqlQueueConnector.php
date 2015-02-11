<?php namespace Mysql\Queue\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use Mysql\Queue\MysqlQueue;

class MysqlQueueConnector implements ConnectorInterface{

  /**
   * Establish a queue connection.
   *
   * @param  array  $config
   * @return Mysql\Queue\MysqlQueue
   */
  public function connect(array $config)
  {
    return new MysqlQueue(
      array_get($config, 'table', 'jobs'),
      array_get($config, 'queue', 'default')
    );
  }
  
}