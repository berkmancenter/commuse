<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * This controller handles file-related operations.
 */
class FilesController extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves the specified file and returns its contents.
   *
   * @param string $filename The name of the file to retrieve.
   * @return \CodeIgniter\HTTP\Response
   */
  public function get($filename)
  {
    $filepath = WRITEPATH . 'uploads/' . implode('/', func_get_args());
    if (file_exists($filepath)) {
      $file = file_get_contents($filepath);
      $mime = mime_content_type($filepath);
      return $this->response->setHeader('Content-Type', $mime)->setBody($file);
    } else {
      return $this->response
        ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
        ->setJSON([
            'status' => 404,
            'error' => 'File not found.'
        ]);
    }
  }
}
