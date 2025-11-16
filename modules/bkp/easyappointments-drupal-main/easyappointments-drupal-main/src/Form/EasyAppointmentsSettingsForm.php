<?php

namespace Drupal\easyappointments\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for configuring Easy!Appointments settings.
 */
class EasyAppointmentsSettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['easyappointments.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'easyappointments_settings_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('easyappointments.settings');

        $form['easyappointments_url'] = [
            '#type' => 'url',
            '#title' => $this->t('Easy!Appointments URL'),
            '#description' => $this->t('Enter the URL to your Easy!Appointments booking page.'),
            '#default_value' => $config->get('easyappointments_url'),
            '#required' => TRUE,
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);

        // Validate the URL.
        if (!filter_var($form_state->getValue('easyappointments_url'), FILTER_VALIDATE_URL)) {
            $form_state->setErrorByName('easyappointments_url', $this->t('The URL is not valid.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('easyappointments.settings')
            ->set('easyappointments_url', $form_state->getValue('easyappointments_url'))
            ->save();

        parent::submitForm($form, $form_state);
    }

}
