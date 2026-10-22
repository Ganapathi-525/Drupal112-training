<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event Registration Form with DEVEL/KINT debugging.
 */
class EventRegistrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div class="event-registration-form">';
    $form['#suffix'] = '</div>';

    // Debug logs at TOP - always visible
    $values = $form_state->getValues();
    $debug_output = '<div style="background: #ffff00; padding: 20px; margin-bottom: 30px; border: 4px solid #ff0000;">
      <h2 style="color: #ff0000; margin: 0;">🔍 DEBUG INFO</h2>
      <pre style="background: #fff; padding: 10px; color: #000;">' . htmlspecialchars(print_r($values, TRUE)) . '</pre>
    </div>';

    $form['debug_header'] = [
      '#markup' => $debug_output,
      '#weight' => -100,
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#maxlength' => 100,
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#maxlength' => 100,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#description' => $this->t('We will not share your email.'),
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#required' => FALSE,
      '#pattern' => '[0-9\-\(\)\+\s]*',
    ];

    $form['event_selection'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select an Event'),
      '#options' => [
        'workshop' => $this->t('Workshops & Training'),
        'conference' => $this->t('Annual Conference'),
        'meetup' => $this->t('Local Meetup'),
        'webinar' => $this->t('Live Webinar'),
      ],
      '#required' => TRUE,
    ];

    $form['attendees'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Attendees'),
      '#min' => 1,
      '#max' => 50,
      '#default_value' => 1,
      '#required' => TRUE,
    ];

    $form['dietary_restrictions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Dietary Restrictions'),
      '#options' => [
        'vegetarian' => $this->t('Vegetarian'),
        'vegan' => $this->t('Vegan'),
        'gluten_free' => $this->t('Gluten Free'),
        'nut_allergy' => $this->t('Nut Allergy'),
      ],
      '#required' => FALSE,
    ];

    $form['comments'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Additional Comments'),
      '#rows' => 4,
      '#description' => $this->t('Any special requests or questions?'),
    ];

    $form['agree'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I agree to the terms and conditions'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register Now'),
      '#button_type' => 'primary',
    ];

    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear Form'),
      '#submit' => ['::resetForm'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
    }

    $phone = $form_state->getValue('phone');
    if ($phone && !preg_match('/^[\d\+\-\(\)\s]+$/', $phone)) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid phone number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Log the registration
    \Drupal::logger('event_registration')->info(
      'Event registration submitted: @name (@email) for @event',
      [
        '@name' => $values['first_name'] . ' ' . $values['last_name'],
        '@email' => $values['email'],
        '@event' => $values['event_selection'],
      ]
    );

    $this->messenger->addMessage(
      $this->t('Thank you @name! Your registration for @event has been received.',
        [
          '@name' => $values['first_name'],
          '@event' => $values['event_selection'],
        ]
      )
    );

    // Optionally log full submission with devel if enabled
    if ($this->isDevelEnabled()) {
      \Drupal::logger('event_registration')->debug(
        'Full registration data: <pre>@data</pre>',
        ['@data' => kint_print($values, TRUE)]
      );
    }
  }

  /**
   * Reset the form.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    $this->messenger->addMessage($this->t('Form has been cleared.'));
  }

  /**
   * Check if Devel module is enabled.
   */
  private function isDevelEnabled() {
    return \Drupal::moduleHandler()->moduleExists('devel');
  }

}
