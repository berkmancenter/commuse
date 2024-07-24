<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitSystemSettings extends Migration
{
    public function up()
    {
        service('settings')->set('SystemSettings.settings', json_encode([
          'ReintakeAdminEmails' => [
            'type' => 'string',
            'value' => 'admin@example.com',
          ],
          'ReintakeMessage' => [
            'type' => 'long_text_rich',
            'value' => 'Welcome back, are you ready to rejoin the community?',
          ],
          'ReintakeAcceptedToUserSubject' => [
            'type' => 'string',
            'value' => 'Role accepted by ###ACCEPTED_BY_FIRST_NAME### ###ACCEPTED_BY_LAST_NAME### ###ACCEPTED_BY_EMAIL###',
          ],
          'ReintakeAcceptedToUserBody' => [
            'type' => 'long_text_rich',
            'value' => <<<EOD
This email confirms that you have accepted the affiliation offer sent to you via email.<br><br>
We are thrilled to have you along this journey with us contributing your expertise and building a community of internet and society researchers.<br><br>
Kind regards,
<br>
Team
EOD,
          ],
          'ReintakeRejectedToUserSubject' => [
            'type' => 'string',
            'value' => 'Role declined by ###REJECTED_BY_FIRST_NAME### ###REJECTED_BY_LAST_NAME### ###REJECTED_BY_EMAIL###',
          ],
          'ReintakeRejectedToUserBody' => [
            'type' => 'long_text_rich',
            'value' => <<<'EOD'
Hello!<br><br>
We have received your decision to decline the affiliation offer sent to you via email. If this was done in error, please reach out to the community team via email at admin@example.com.<br><br>
Thank you for your attention to this matter.<br><br>
Kind regards,
<br>
Team
EOD,
          ],
        ]),
      );
    }

    public function down()
    {
        service('settings')->forget('SystemSettings.settings');
    }
}
