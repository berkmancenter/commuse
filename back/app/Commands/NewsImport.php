<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\DataImporters\NewsImporter;

class NewsImport extends BaseCommand
{
    protected $group = 'custom';
    protected $name = 'news_import';
    protected $description = 'Imports news data';

    public function run(array $params) {
      $newsImporter = new NewsImporter();
      $newsImporter->fetchContentItems();

      CLI::write('Done');
    }
}
