<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PeopleModel;

class IndexPeopleItems extends BaseCommand
{
  protected $group       = 'Elasticsearch';
  protected $name        = 'index:people';
  protected $description = 'Indexes all people items into Elasticsearch.';

  /**
   * Indexes all people items in the database.
   *
   * @param array $params The command parameters.
   *
   * @return void
   */
  public function run(array $_params)
  {
    $peopleModel = new PeopleModel();

    $people = $peopleModel->getPeopleWithCustomFields();

    foreach ($people as $person) {
      $personId = $person['id'];

      // Use model method to index each item
      $peopleModel->removeFromSearchIndex($personId);
      $peopleModel->indexInSearchIndex($personId, $person);

      CLI::write("Indexed Person ID: {$personId}", 'green');
    }

    CLI::write('All people items indexed successfully.', 'light_blue');
  }
}
