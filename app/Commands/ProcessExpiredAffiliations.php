<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

/**
 * Command class for importing news data.
 */
class ProcessExpiredAffiliations extends BaseCommand
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
  protected $name = 'process_expired_affiliations';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Processed expired affiliations';

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
      CLI::write('No expired affiliations to process.');
      return;
    }

    // Get the active affiliation custom field metadata
    $customFieldsModel = model('CustomFieldModel');
    $activeAffiliationField = $customFieldsModel
    ->select('machine_name, metadata')
    ->whereIn('machine_name', ['activeAffiliation'])
    ->findAll();
    $activeAffiliationFieldMetadata = json_decode(
      array_values(
        array_filter($activeAffiliationField, fn($item) => $item['machine_name'] === 'activeAffiliation')
      )[0]['metadata'],
      true
    );

    foreach ($people as $person) {
      $usersModel = new UserModel();

      $userData = $usersModel->getUserProfileData($person['user_id']);
      $activeAffiliation = $userData['activeAffiliation'][0];

      // Auto extend expired affiliation if needed based on the autoExtend value
      if (
        isset($activeAffiliationFieldMetadata['autoExtend']) &&
        $activeAffiliationFieldMetadata['autoExtend'] &&
        isset($userData['activeAffiliation'][0]['autoExtend']) &&
        $userData['activeAffiliation'][0]['autoExtend']
      ) {
        // Extend the affiliation by 1 year
        $userData['activeAffiliation'][0]['to'] = intval($userData['activeAffiliation'][0]['to']) + 31536000;
      } else {
        if (isset($userData['affiliation']) && $userData['affiliation'] && is_array($userData['affiliation'])) {
          $userData['affiliation'][] = $activeAffiliation;
        } else {
          $userData['affiliation'] = [$activeAffiliation];
        }

        $userData['activeAffiliation'] = [];
      }

      $usersModel->saveProfileData($userData, $person['user_id']);

      CLI::write("Processing expired affiliation for user ID {$person['user_id']}.");
    }

    CLI::write('Completed successfully.');
  }
}