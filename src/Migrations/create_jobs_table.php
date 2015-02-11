<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateJobsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('jobs', function(Blueprint $table)
    {
      $table->increments('id');
      $table->string('name');
      $table->string('queue', 255)->nullable()->default(null);
      $table->enum('status', ["PENDING", "STARTED"])->default("PENDING");
      $table->integer('attempts')->default(0);
      $table->dateTime('time_started')->nullable()->default(null);
      $table->dateTime('run_at');
      $table->longText('payload')->nullable();
      $table->timestamps();
    });
  }
  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('jobs');
  }
}