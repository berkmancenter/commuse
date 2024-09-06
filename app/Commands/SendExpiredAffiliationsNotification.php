<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\SystemSettingsWrapper;

/**
 * Command class for sending notifications for expired affiliations.
 */
class SendExpiredAffiliationsNotification extends BaseCommand
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
  protected $name = 'send_expired_affiliations_notification';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Sends notifications for expired affiliations';

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

    $twoWeeksInSeconds = 1209600;
    $timestamp = time();
    $timestampRangeFrom = $timestamp + $twoWeeksInSeconds - 600;
    $timestampRangeTo = $timestamp + $twoWeeksInSeconds;

    // We need server time because a cron job will run this command at midnight local server time
    $localTimezoneName = trim(shell_exec('timedatectl show -p Timezone --value'));
    $localTimezone = new \DateTimeZone($localTimezoneName);
    $timeInLocalTimezone = new \DateTime('now', $localTimezone);
    $secsToGmt = $localTimezone->getOffset($timeInLocalTimezone);
    $timestampRangeFrom = $timestampRangeFrom + $secsToGmt;
    $timestampRangeTo = $timestampRangeTo + $secsToGmt;

    $builder
      ->select('
        people.user_id,
        people.first_name,
        people.last_name
        ')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->where('custom_fields.machine_name', 'activeAffiliation')
      ->where('
        jsonb_path_exists(
          custom_field_data.value_json,
          \'$[*] ? (@.to > $timestamp_range_from && @.to < $timestamp_range_to && @.autoExtend != true && @.autoExtendOnce != true)\',
          \'{"timestamp_range_from": ' . $timestampRangeFrom . ', "timestamp_range_to": ' . $timestampRangeTo . '}\'
        )
      ');

    $people = $builder->get()->getResultArray();

    if (empty($people)) {
      CLI::write('No expired affiliations to process.');
      return;
    }

    $twoWeeksFromNow = date('m/d/Y', time() + $twoWeeksInSeconds);
    $body = "The following users have affiliations that will expire in two weeks on {$twoWeeksFromNow}:<br><br>";
    $baseUrl = $_ENV['app.baseURL'];
    $body .= '<table>';
    foreach ($people as $person) {
      $body .= '<tr>';
      $body .= "<td>{$person['first_name']} {$person['last_name']}</td><td><a href=\"{$baseUrl}/profile/{$person['user_id']}\">Edit profile</a></td><br>";
      $body .= '</tr>';

      CLI::write("Sending notification to user #{$person['user_id']} about their expired affiliation that will expire on {$twoWeeksFromNow}.");
    }

    $email = \Config\Services::email();
    $email->setTo(SystemSettingsWrapper::getInstance()->getSettingByKey('ReintakeAdminEmails')['value']);
    $email->setSubject('Expiring affiliations');
    $email->setMessage($body);
    $email->send();

    CLI::write('Completed successfully.');
  }
}
