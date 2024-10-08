<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PeopleModel;

/**
 * Command class for processing future affiliations that have become active.
 */
class ProcessFutureAffiliations extends BaseCommand
{
  /**
   * The command group.
   *
   * @var string
   */
  protected $group = 'custom';

  /**
   * The command name.
   *
   * @var string
   */
  protected $name = 'process_future_affiliations';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Process future affiliations that are now active';

  /**
   * Executes the command.
   *
   * @param array $params The command parameters.
   *
   * @return void
   */
  public function run(array $params)
  {
    $db = \Config\Database::connect();
    $builder = $db->table('people');

    $timestamp = time();
    $timestampRangeFrom = $timestamp - 600;

    // We need server time because a cron job will run this command at midnight local server time
    $localTimezoneName = trim(shell_exec('timedatectl show -p Timezone --value'));
    $localTimezone = new \DateTimeZone($localTimezoneName);
    $timeInLocalTimezone = new \DateTime('now', $localTimezone);
    $secsToGmt = $localTimezone->getOffset($timeInLocalTimezone);
    $timestamp = $timestamp + $secsToGmt;
    $timestampRangeFrom = $timestampRangeFrom + $secsToGmt;

    $builder
      ->select('people.user_id')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->where('custom_fields.machine_name', 'activeAffiliation')
      ->where('
        jsonb_path_exists(
          custom_field_data.value_json,
          \'$[*] ? (@.from > $timestamp_range_from && @.from < $timestamp_range_to)\',
          \'{"timestamp_range_from": ' . $timestampRangeFrom . ', "timestamp_range_to": ' . $timestamp . '}\'
        )
      ');

    $people = $builder->get()->getResultArray();

    if (empty($people)) {
      CLI::write('No future affiliations to process.');
      return;
    }

    foreach ($people as $person) {
      $peopleModel = new PeopleModel();

      $userData = $peopleModel->getProfileData($person['user_id']);

      // This will trigger a sync call to the remote service
      $peopleModel->saveProfileData($userData, $person['user_id'], true);

      CLI::write("Processing future affiliation for user ID {$person['user_id']}.");
    }

    CLI::write('Completed successfully.');
  }
}
