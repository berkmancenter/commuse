<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Files extends BaseController
{
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
