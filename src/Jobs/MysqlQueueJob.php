<?php namespace Mysql\Queue\Jobs;

use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Mysql\Queue\MysqlQueue;

class MysqlQueueJob extends Job{

  const STATUS_PENDING = "PENDING";
  const STATUS_STARTED = "STARTED";

  /**
   * The database queue instance.
   *
   * @var \Illuminate\Queue\DatabaseQueue
   */
  protected $database;

  /**
   * The database job payload.
   *
   * @var \StdClass
   */
  protected $job;

  /**
   * Name of queue table.
   *
   * @var string
   */
  protected $table = 'jobs';

  /**
   * Create a new job instance.
   *
   * @param  \Illuminate\Container\Container  $container
   * @param  \Myql\Queue\MysqlQueue  $database
   * @param  \StdClass  $job
   * @param  string  $queue
   * @return void
   */
  public function __construct(Container $container, MysqlQueue $database, $job, $queue)
  {
    $this->job = $job;
    $this->queue = $queue;
    $this->database = $database;
    $this->container = $container;
    $this->job['attempts'] = $this->job['attempts'] + 1;
  }

  /**
   * Fire the job.
   *
   * @return void
   */
  public function fire()
  {
    $this->resolveAndFire(json_decode($this->job['payload'], true));
  }

  /**
   * Delete the job from the queue.
   *
   * @return void
   */
  public function delete()
  {
    parent::delete();
    $this->database->deleteStarted($this->queue, $this->job['id']);
  }

  /**
   * Release the job back into the queue.
   *
   * @param  int  $delay
   * @return void
   */
  public function release($delay = 0)
  {
    $this->delete();
    $this->database->release($this->queue, $this->job, $delay);
  }

  /**
   * Get the number of times the job has been attempted.
   *
   * @return int
   */
  public function attempts()
  {
    return (int) $this->job['attempts'];
  }

  /**
   * Get the job identifier.
   *
   * @return string
   */
  public function getJobId()
  {
    return $this->job['id'];
  }

  /**
   * Get the raw body string for the job.
   *
   * @return string
   */
  public function getRawBody()
  {
    return $this->job['payload'];
  }

  /**
   * Get the IoC container instance.
   *
   * @return \Illuminate\Container\Container
   */
  public function getContainer()
  {
    return $this->container;
  }

  /**
   * Get the underlying queue driver instance.
   *
   * @return \Illuminate\Queue\DatabaseQueue
   */
  public function getDatabaseQueue()
  {
    return $this->database;
  }

  /**
   * Get the underlying database job.
   *
   * @return \StdClass
   */
  public function getDatabaseJob()
  {
    return $this->job;
  }
}