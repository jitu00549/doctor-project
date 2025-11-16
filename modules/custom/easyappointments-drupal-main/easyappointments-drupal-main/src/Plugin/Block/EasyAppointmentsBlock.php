<?php

namespace Drupal\easyappointments\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Easy!Appointments Block.
 *
 * @Block(
 *   id = "easyappointments_block",
 *   admin_label = @Translation("Easy!Appointments Block"),
 * )
 */
class EasyAppointmentsBlock extends BlockBase
{

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        return [
            'width' => '100%',
            'height' => '600',
            'provider_id' => '',
            'service_id' => '',
            'style' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();

        $form['width'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Iframe Width'),
            '#description' => $this->t('Enter the width of the iframe (e.g., 100%, 800px).'),
            '#default_value' => $config['width'],
        ];

        $form['height'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Iframe Height'),
            '#description' => $this->t('Enter the height of the iframe (e.g., 600px, 100vh).'),
            '#default_value' => $config['height'],
        ];

        $form['provider_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Provider ID'),
            '#description' => $this->t('Enter the provider ID for Easy!Appointments.'),
            '#default_value' => $config['provider_id'],
        ];

        $form['service_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Service ID'),
            '#description' => $this->t('Enter the service ID for Easy!Appointments.'),
            '#default_value' => $config['service_id'],
        ];

        $form['style'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Iframe Style'),
            '#description' => $this->t('Enter custom CSS styles for the iframe (e.g., border: 1px solid black;).'),
            '#default_value' => $config['style'],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->setConfigurationValue('width', $form_state->getValue('width'));
        $this->setConfigurationValue('height', $form_state->getValue('height'));
        $this->setConfigurationValue('provider_id', $form_state->getValue('provider_id'));
        $this->setConfigurationValue('service_id', $form_state->getValue('service_id'));
        $this->setConfigurationValue('style', $form_state->getValue('style'));
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $global_config = \Drupal::config('easyappointments.settings');
        $url = $global_config->get('easyappointments_url');

        if (empty($url)) {
            return [
                '#markup' => $this->t('In order to render the Easy!Appointments iframe, you will need to first set the target booking URL.'),
            ];
        }

        $block_config = $this->getConfiguration();
        $width = $block_config['width'];
        $height = $block_config['height'];
        $providerId = $block_config['provider_id'];
        $serviceId = $block_config['service_id'];
        $style = $block_config['style'];

        // Build the iframe attributes.
        $iframe_attributes = [
            'src' => $url,
            'width' => $width,
            'height' => $height,
            'frameborder' => '0',
            'allowfullscreen' => 'true',
            'style' => $style,
        ];

        // Add providerId and serviceId as query parameters if they are set.
        if (!empty($providerId) || !empty($serviceId)) {
            $query_params = [];
            if (!empty($providerId)) {
                $query_params['provider'] = $providerId;
            }
            if (!empty($serviceId)) {
                $query_params['service'] = $serviceId;
            }
            $iframe_attributes['src'] .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . http_build_query($query_params);
        }

        return [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['block__content'],
            ],
            'iframe' => [
                '#type' => 'html_tag',
                '#tag' => 'iframe',
                '#attributes' => $iframe_attributes,
            ],
        ];
    }

}
