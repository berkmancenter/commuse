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

    foreach ($result['meetings'] as &$value) {
      $value['start_time'] = strtotime($value['start_time']);
    }

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

    // Validate required fields (title, email, dateStart, dateEnd, timezone).
    if (!isset($request->title) ||
        !isset($request->email) ||
        !isset($request->dateStart) ||
        !isset($request->dateEnd) ||
        !isset($request->timezone)) {
      return $this->fail('Missing required fields.');
    }

    try {
      // Use the provided timezone from the request.
      $timezone = new \DateTimeZone($request->timezone->key);

      // Create dateStart and dateEnd using the client timezone.
      $dateStartWithoutTimezone = new \DateTime($request->dateStart);
      $dateStart = new \DateTime($request->dateStart, $timezone);
      $dateEnd = new \DateTime($request->dateEnd, $timezone);

      // For consistency, the current time is set in the same timezone.
      $now = new \DateTime('now', $timezone);
    } catch (\Exception $e) {
      return $this->fail('Invalid date or timezone provided: ' . $e->getMessage());
    }

    // Check if the meeting start date is in the past.
    if ($dateStart < $now) {
      return $this->fail('The meeting start date cannot be in the past.');
    }

    // Check if the meeting end date is before the start date.
    if ($dateEnd < $dateStart) {
      return $this->fail('The meeting end date cannot be before the start date.');
    }

    // Calculate the duration of the meeting in minutes.
    $durationInSeconds = $dateEnd->getTimestamp() - $dateStart->getTimestamp();
    $duration = round($durationInSeconds / 60);

    // Prepare meeting data according to Zoom API requirements.
    $meetingData = [
      'topic'        => $request->title,
      'start_time'   => $dateStartWithoutTimezone->format('Y-m-d\TH:i:s'),
      'schedule_for' => $request->email,
      'duration'     => $duration,
      'timezone'     => $request->timezone->key,
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

  /**
   * Deletes a meeting by its ID.
   *
   * @param int $meetingId The ID of the meeting to delete.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function deleteMeeting(int $meetingId) {
    // Create a new instance of the Zoom API service.
    $zoomApi = new ZoomApiService();
    $result  = $zoomApi->deleteMeeting($meetingId);

    // Check if an error was returned.
    if (isset($result['error'])) {
      return $this->failServerError($result['error']);
    }

    // Return a success response.
    return $this->respond([
      'message' => 'The meeting has been successfully deleted.',
      'status'  => 'ok',
    ]);
  }
}
