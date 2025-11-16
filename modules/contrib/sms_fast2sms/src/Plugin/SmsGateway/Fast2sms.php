<?php

namespace Drupal\sms_fast2sms\Plugin\SmsGateway;

use Drupal\Component\Serialization\Json;
use Drupal\sms\Plugin\SmsGatewayPluginBase;
use Drupal\sms\Message\SmsDeliveryReport;
use Drupal\sms\Message\SmsMessageInterface;
use Drupal\sms\Message\SmsMessageResult;
use Drupal\sms\Message\SmsMessageReportStatus;
use Drupal\Core\Form\FormStateInterface;
use Google\Exception;

/**
 * @SmsGateway(
 *   id = "fast2sms",
 *   label = @Translation("fast2sms"),
 *   outgoing_message_max_recipients = 600,
 *   reports_pull = TRUE,
 *   reports_push = TRUE,
 *   schedule_aware = TRUE,
 * )
 */
class Fast2sms extends SmsGatewayPluginBase {

  /**
   * The Guzzle client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;


  /**
   * Constructs a new SmsGateway plugin.
   *
   * @param array $configuration
   *   The configuration to use and build the sms gateway.
   * @param string $plugin_id
   *   The gateway id.
   * @param mixed $plugin_definition
   *   The gateway plugin definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $this->client = \Drupal::httpClient();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [];
    $defaults['account'] = [
      'api_key' => '',
      'route' => '',
      'sender_id' => '',
    ];
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['fast2sms'] = [
      '#type' => 'details',
      '#title' => $this->t('Fast2sms'),
      '#open' => TRUE,
    ];

    $form['fast2sms']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Key'),
      '#required' => TRUE,
      '#default_value' => $config['account']['api_key'],
    ];
    $form['fast2sms']['route'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route'),
      '#default_value' => $config['account']['route'],
    ];
    $form['fast2sms']['sender_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sender ID'),
      '#default_value' => $config['account']['sender_id'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['account']['api_key'] = trim($form_state->getValue('api_key'));
    $this->configuration['account']['route'] = trim($form_state->getValue('route'));
    $this->configuration['account']['sender_id'] = trim($form_state->getValue('sender_id'));
  }

  /**
   * {@inheritdoc}
   */
  public function send(SmsMessageInterface $sms_message) {
    $result = new SmsMessageResult();

    $url = 'https://www.fast2sms.com/dev/bulkV2';

    $recipients = $sms_message->getRecipients();
    $message = $sms_message->getMessage();
    $data = [
      'numbers' => implode(',', $recipients),
      'message' => $message,
      'route' => 'q'
    ];

    $data = array_merge($data, array_filter($this->configuration['account']));
    $data = array_merge($data, array_filter($sms_message->getOptions()));

    $params['body'] = Json::encode($data);
    $params['method'] = 'POST';
    $params['headers'] = [
      'authorization' => $this->configuration['account']['api_key'],
      "accept" => "*/*",
      "cache-control" => "no-cache",
      "content-type" => "application/json",
    ];

    try {
      $response = $this->client->request('POST', $url, $params);
      $response = $response->getBody();
      $response = Json::decode($response);
    }
    catch (Exception $err) {
      $response = ['return' => FALSE, 'status_code' => $err->getCode(), 'message' => $err->getMessage()];
    }
    $report = new SmsDeliveryReport();

    $message_id = $error_message = $error_code = FALSE;
    if ($response['return']) {
      $message_id = !empty($response['request_id']) ? $response['request_id'] : NULL;
    }
    else {
      $error_code = $response['status_code'];
      $error_message = $response['message'];
    }

    $report->setRecipient(implode(',', $recipients));
    if ($message_id) {
      $report->setMessageId($message_id);
    }

    // If $error_code is FALSE or NULL then there was an no error.
    if (!$error_code) {
      // Success!
      $report->setStatus(SmsMessageReportStatus::DELIVERED);
    }
    else {
      $report->setStatusMessage(sprintf('Error: %s', $error_message));
      $report->setStatus(SmsMessageReportStatus::ERROR);
    }

    $result->addReport($report);

    return $result;
  }

}
