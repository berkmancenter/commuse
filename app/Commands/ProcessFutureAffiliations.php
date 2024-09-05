<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

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

    $builder
      ->select('people.user_id')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->where('custom_fields.machine_name', 'activeAffiliation')
      ->where('
        jsonb_path_exists(
          custom_field_data.value_json,
          \'$[*] ? (@.from > $timestamp_range_from && @.from < $timestamp_range_to)\',
          \'{"timestamp_range_from": ' . (time() - 600) . ', "timestamp_range_to": ' . time() . '}\'
        )
      ');

    $people = $builder->get()->getResultArray();

    if (empty($people)) {
      CLI::write('No future affiliations to process.');
      return;
    }

    foreach ($people as $person) {
      $usersModel = new UserModel();

      $userData = $usersModel->getUserProfileData($person['user_id']);

      // This will trigger a sync call to the remote service
      $usersModel->saveProfileData($userData, $person['user_id'], true);

      CLI::write("Processing future affiliation for user ID {$person['user_id']}.");
    }

    CLI::write('Completed successfully.');
  }
}
