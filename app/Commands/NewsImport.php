<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\DataImporters\NewsImporter;

/**
 * Command class for importing news data.
 */
class NewsImport extends BaseCommand
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
  protected $name = 'news_import';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Imports news data';

  /**
   * Executes the news import command.
   *
   * @param array $params The command parameters.
   *
   * @return void
   */
  public function run(array $params)
  {
    $newsImporter = new NewsImporter();

    $newsImporter->fetchContentItems();

    CLI::write('News import completed successfully.');
  }
}
