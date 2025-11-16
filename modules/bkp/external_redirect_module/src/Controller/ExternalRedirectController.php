<?php

namespace Drupal\external_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;

class ExternalRedirectController extends ControllerBase {

  public function redirect() {
    // Replace this with your desired external URL
    $url = 'https://echsindia.com/';

    // Use TrustedRedirectResponse for external URLs
    $response = new TrustedRedirectResponse($url);
    // Optional: Add HTTP status code (e.g. 302 temporary redirect)
    $response->setStatusCode(302);
    
    return $response;
  }

}
