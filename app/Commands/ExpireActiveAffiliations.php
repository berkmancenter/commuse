<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

/**
 * Command class for importing news data.
 */
class ExpireActiveAffiliations extends BaseCommand
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
  protected $name = 'expire_active_affiliations';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Converts active affiliations to archived affiliations';

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
      ->select('
        people.user_id
      ')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->where('custom_fields.machine_name', 'activeAffiliation')
      ->where('
        jsonb_path_exists(
          custom_field_data.value_json,
          \'$[*] ? (@.to < $timestamp)\',
          \'{"timestamp": ' . time() . '}\'
        )
      ');

    $people = $builder->get()->getResultArray();

    if (empty($people)) {
      CLI::write('No active affiliations to expire.');
      return;
    }

    foreach ($people as $person) {
      $usersModel = new UserModel();

      $userData = $usersModel->getUserProfileData($person['user_id']);
      $activeAffiliation = $userData['activeAffiliation'][0];

      if (isset($userData['affiliation']) && $userData['affiliation'] && is_array($userData['affiliation'])) {
        $userData['affiliation'][] = $activeAffiliation;
      } else {
        $userData['affiliation'] = [$activeAffiliation];
      }

      CLI::write("Expiring active affiliation for user ID {$person['user_id']}.");

      $userData['activeAffiliation'] = [];

      $usersModel->saveProfileData($userData, $person['user_id']);
    }

    CLI::write('Completed successfully.');
  }
}
