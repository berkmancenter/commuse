<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PeopleModel;
use App\Models\UserModel;

class PeopleSearchIndex extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'people_search_index_all';
    protected $description = 'Fills the full_text_search column in the people table';

    public function run(array $params)
    {
        $peopleModel = new PeopleModel();
        $userModel = new UserModel();

        $people = $peopleModel->getPeopleWithCustomFields();

        foreach ($people as $person) {
          CLI::write("Indexing user #{$person['id']}");
          $userModel->saveProfileData($person, $person['user_id']);
        }

        CLI::write('Done');
    }
}
