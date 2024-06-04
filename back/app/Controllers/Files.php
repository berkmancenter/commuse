<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

/**
 * This controller handles file-related operations.
 */
class Files extends BaseController
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
      return redirect()->back()->with('error', 'File not found.');
    }
  }
}
