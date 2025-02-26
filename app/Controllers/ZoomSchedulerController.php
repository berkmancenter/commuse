<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Libraries\ZoomApiService;

/**
 * This class is responsible for handling Zoom Scheduler-related operations.
 */
class ZoomSchedulerController extends BaseController {
  use ResponseTrait;

  /**
   * Retrieves all the meetings scheduled for the current user.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function index() {
    // Create a new instance of the Zoom API service.
    $zoomApi = new ZoomApiService();
    $result  = $zoomApi->getMeetings();

    // Check if an error was returned.
    if (isset($result['error'])) {
      return $this->failServerError($result['error']);
    }

    // Return a success response with the list of meetings.
    return $this->respond($result);
  }

  /**
   * Schedules a new meeting.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function createMeeting() {
    // Get the JSON payload from the request.
    $request = $this->request->getJSON();
    if (!$request) {
      return $this->fail('Invalid JSON payload provided.');
    }

    // Validate required fields (title, email, dateStart, dateEnd).
    if (!isset($request->title) || !isset($request->email) || !isset($request->dateStart) || !isset($request->dateEnd)) {
      return $this->fail('Missing required fields.');
    }

    // Check if the meeting start date is in the past.
    $dateStart = new \DateTime($request->dateStart);
    $now       = new \DateTime();
    if ($dateStart < $now) {
      return $this->fail('The meeting start date cannot be in the past.');
    }

    // Check if the meeting end date is before the start date.
    $dateEnd = new \DateTime($request->dateEnd);
    if ($dateEnd < $dateStart) {
      return $this->fail('The meeting end date cannot be before the start date.');
    }

    // Calculate the duration of the meeting in minutes.
    $duration  = $dateEnd->getTimestamp() - $dateStart->getTimestamp();
    $duration  = round($duration / 60);

    // Prepare meeting data according to Zoom API requirements.
    $meetingData = [
      'topic'        => $request->title,
      'start_time'   => $dateStart->format('Y-m-d\TH:i:s'),
      //'schedule_for' => $request->email,
      'duration'     => $duration,
    ];

    // Create a new instance of the Zoom API service.
    $zoomApi = new ZoomApiService();
    $result  = $zoomApi->createMeeting($meetingData);

    // Check if an error was returned.
    if (isset($result['error'])) {
      if (strstr($result['error'], 'schedule_for')) {
        return $this->fail("Most likely there is no Zoom user created for this email address or the user is not a member of the workspace.");
      }

      return $this->failServerError($result['error']);
    }

    // Return a success response with the Zoom meeting details.
    return $this->respondCreated($result);
  }
}
