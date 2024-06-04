<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PeopleModel;
use App\Models\UserModel;

/**
 * Command class for indexing people search data.
 */
class PeopleSearchIndex extends BaseCommand
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
  protected $name = 'people_search_index_all';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Fills the full_text_search column in the people table';

  /**
   * Indexes all people search data in the database.
   *
   * @param array $params The command parameters.
   *
   * @return void
   */
  public function run(array $params)
  {
    $peopleModel = new PeopleModel();
    $userModel = new UserModel();

    $people = $peopleModel->getPeopleWithCustomFields();

    foreach ($people as $person) {
      CLI::write("Indexing user #{$person['id']}");

      $userModel->saveProfileData($person, $person['user_id']);
    }

    CLI::write('People search indexing completed successfully.');
  }
}
