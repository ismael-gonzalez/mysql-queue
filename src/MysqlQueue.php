<?php namespace Mysql\Queue;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueInterface;
use Mysql\Queue\Jobs\MysqlQueueJob;

/**
 * Mysql Queue class
 *
 * @author Ismael
 */
class MysqlQueue extends Queue implements QueueInterface{

  /**
   * The database connection.
   *
   * @var 
   */
  protected $database;

  /**
   * The name of the default queue.
   *
   * @var \Illuminate\Database\Connection
   */
  protected $default;

  /**
   * The database table that holds the jobs.
   *
   * @var string
   */
  protected $table;

  /**
   * Create a new mysql queue instance.
   *
   * @param  string  $connection
   * @param  string  $default
   * @return void
   */
  public function __construct(Connection $database, $table = "jobs", $default = 'default')
  {
    $this->default = $default;
    $this->database = $database;
    $this->table = $table
  }

  /**
   * Push a new job onto the queue.
   *
   * @param  string  $job
   * @param  mixed   $data
   * @param  string  $queue
   * @return int
   */
  public function push($job, $data = '', $queue = null)
  {
    return $this->createJob(
      $job,
      $this->createPayload($job, $data),
      $queue
    );
  }

  /**
   * Push a new job onto the queue after a delay.
   *
   * @param  DateTime|int  $delay either string in "Y-m-d H:i:s" format or int for seconds
   * @param  string  $job
   * @param  mixed   $data
   * @param  string  $queue
   * @return void
   */
  public function later($delay, $job, $data = '', $queue = null)
  {
    return $this->createJob(
      $job,
      $this->createPayload($job, $data),
      $queue,
      $this->getSeconds($delay)
    );
  }

  /**
   * Release a reserved job back onto the queue.
   *
   * @param  string  $queue
   * @param  string  $payload
   * @param  int  $delay
   * @param  int  $attempts
   * @return void
   */
  public function release($queue, $job, $delay)
  {
    $this->createJob(
      $this->getJobName($job->payload),
      $job->payload,
      $queue,
      $delay,
      $job->attempts
    );
  }

  public function pop($queue = null)
  {
    $queue = $this->getQueue($queue);

    $job = $this->database->table($this->table)
            ->lockForUpdate()
            ->where('queue', $this->getQueue($queue))
            ->where('status', MysqlQueueJob::STATUS_PENDING)
            ->where('run_at', '<=', $this->getTime())
            ->orderBy('id', 'asc')
            ->first();

    if(!is_null($job)){
      $this->database->table($this->table)->where('id', $job['id'])->update([
        'status' => MysqlQueueJob::STATE_STARTED, 'time_started' => $this->getTime(),
      ]);

      return new MysqlQueueJob($this->container, $this, $job, $queue);
    }
  }

  /**
   * Delete a reserved job from the queue.
   *
   * @param  string  $queue
   * @param  string  $id
   * @return void
   */
  public function deleteStarted($queue, $id)
  {
    $this->database->table($this->table)->where('id', $id)->delete();
  }

  /**
   * Create a new job in the database
   * @param  string  $jobName  name of the job for easier manual look ups
   * @param  mixed  $payload  payload for the job
   * @param  string  $queue    name of the bucket for the job to go in to
   * @param  integer $delay    amount od seconds to delay the job
   * @param  integer $attempts number of attempts
   * @return int            id of job just created
   */
  protected function createJob($jobName, $payload, $queue, $delay = 0, $attempts = 0){

    return $this->database->table($this->table)->insertGetId([
      'name' => $jobName
      'queue' => $this->getQueue($queue),
      'payload' => $payload,
      'status' => MysqlQueueJob::STATUS_PENDING,
      'attempts' => $attempts,
      'run_at' => $this->getFireDatetime($delay),
      'created_at' => $this->getTime(),
      'updated_at' => $this->getTime()
    ]);
  }

  /**
   * Get the queue or return the default.
   *
   * @param  string|null  $queue
   * @return string
   */
  protected function getQueue($queue)
  {
    return ($queue ?: $this->default);
  }

  /**
   * Get the appropriate mysql database datetime string format given any delay in seconds
   *     
   * @param  int $delay time to delay job in seconds 
   * @return string
   */
  protected function getFireDatetime($delay)
  {
    return date('Y-m-d H:i:s', strtotime('+'.$delay.'seconds'));
  }

  /**
   * Extract the name of the job for release functionality
   * @param  mixed $payload job payload
   * @return string          name of the job
   */
  protected function getJobName($payload)
  {
    return json_decode($payload, true)['job'];
  }
}