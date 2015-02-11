<?php namespace Mysql\Queue;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
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
  public function __construct($table = "jobs", $default = 'default')
  {
    $this->default = $default;
    $this->table = $table;
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
    $options = array('name' => $job);
    return $this->pushRaw($this->createPayload($job, $data), $queue, $options);
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
    $options = array("delay" => $this->getSeconds($delay), 'name' => $job);
    return $this->pushRaw($this->createPayload($job, $data), $queue, $options);
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
    $options = array("attempts" => $job['attempts'], "delay" => $this->getSeconds($delay));
    $this->pushRaw($job['payload'], $queue, $options);
  }

  public function pop($queue = null)
  {
    $queue = $this->getQueue($queue);

    $job = DB::table($this->table)
            ->lockForUpdate()
            ->where('queue', $this->getQueue($queue))
            ->where('status', MysqlQueueJob::STATUS_PENDING)
            ->where('run_at', '<=', date("Y-m-d H:i:s"))
            ->orderBy('id', 'asc')
            ->first();

    if(!is_null($job)){
      DB::table($this->table)->where('id', $job['id'])->update([
        'status' => MysqlQueueJob::STATUS_STARTED, 'time_started' => date('Y-m-d H:i:s'),
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
    DB::table($this->table)->where('id', $id)->delete();
  }

  /**
   * Push a raw payload onto the queue.
   *
   * @param  string  $payload
   * @param  string  $queue
   * @param  array   $options
   * @return mixed
   */
  public function pushRaw($payload, $queue = null, array $options = array())
  {
    return $this->createJob(
      array_get($options, 'name', $this->getJobName($payload)),
      $payload,
      $queue,
      $this->getSeconds(array_get($options, 'delay', 0)),
      array_get($options, 'attempts', 0)
    );
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

    return DB::table($this->table)->insertGetId([
      'name' => $jobName,
      'queue' => $this->getQueue($queue),
      'payload' => $payload,
      'status' => MysqlQueueJob::STATUS_PENDING,
      'attempts' => $attempts,
      'run_at' => $this->getFireDatetime($delay),
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s')
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