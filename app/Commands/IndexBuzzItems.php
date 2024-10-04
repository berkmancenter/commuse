<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BuzzModel;

class IndexBuzzItems extends BaseCommand
{
  protected $group       = 'Elasticsearch';
  protected $name        = 'index:buzz';
  protected $description = 'Indexes all buzz items into Elasticsearch.';

  public function run(array $params)
  {
    $buzzModel = new BuzzModel();

    // Retrieve all buzz IDs
    $buzzItems = $buzzModel->findAll();

    foreach ($buzzItems as $buzz) {
      $buzzId = $buzz['id'];

      // Use model method to index each item
      $buzzModel->indexSingleBuzzItem($buzzId);

      CLI::write("Indexed Buzz ID: {$buzzId}", 'green');
    }

    CLI::write('All buzz items indexed successfully.', 'light_blue');
  }
}
