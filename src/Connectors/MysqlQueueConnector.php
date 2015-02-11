<?php namespace Mysql\Queue\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use Mysql\Queue\MysqlQueue;

class MysqlQueueConnector implements ConnectorInteface{

  /**
   * Establish a queue connection.
   *
   * @param  array  $config
   * @return Mysql\Queue\MysqlQueue
   */
  public function connect(array $config)
  {
    return new MysqlQueue(
      $this->connections->connection(array_get($config, 'connection')),
      array_get($config, 'table', 'jobs'),
      array_get($config, 'queue', 'default')
    );
  }
  
}