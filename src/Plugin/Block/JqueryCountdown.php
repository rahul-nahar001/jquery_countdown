<?php

namespace Drupal\jquery_countdown\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Random;

/**
 * Provides a 'Countdown' Block.
 *
 * @Block(
 *   id = "jquery_countdown_block",
 *   admin_label = @Translation("jQuer Countdown block"),
 * )
 */
class JqueryCountdown extends BlockBase {

  /**
   * Implements a block render.
   */
  public function build($delta = "") {

    $config = $this->getConfiguration();
    $init_format = date("F d, Y g:i a", time());
    $format = '';
    $msg_format = SafeMarkup::checkPlain($config['jquery_countdown_msg_format']);

    $format .= preg_match("/%years/", $msg_format)? 'Y' : $format;
    $format .= preg_match("/%months/", $msg_format)? 'O' : $format;
    $format .= preg_match("/%weeks/", $msg_format)? 'W' : $format;
    $format .= preg_match("/%days/", $msg_format)? 'D' : $format;
    $format .= preg_match("/%hours/", $msg_format)? 'H' : $format;
    $format .= preg_match("/%minutes/", $msg_format)? 'M' : $format;
    $format .= preg_match("/%seconds/", $msg_format)? 'S' : $format;
    $event_name = SafeMarkup::checkPlain($config['jquery_countdown_event_name']);

    $until = date("F d, Y g:i a", strtotime($config['jquery_countdown_target']));

    $description = $this->t($config['jquery_countdown_description']. ' ', array('@event_name' => $event_name));

    $options = array(
      'until' => $until,
      'format' => $format,
      'description' => $description,
      'onExpiry' => 'Drupal.jQueryCountdownEvent',
      'expiryText' => SafeMarkup::checkPlain($config['jquery_countdown_exp_txt'])
    );
    static $added_selectors = array();
    $selector = 'jquery-countdown-block-'. $config['jquery_countdown_block_id'];
    $added_selectors[$selector] = $options;
    $build = [
      '#theme' => 'jquery_countdown',
      '#cache' => ['max-age' => 0],
      '#until' => $until,
      '#format' => $format,
      '#description' => $description,
      '#onExpiry' => "Drupal.jQueryCountdownEvent",
      '#expiryText' => SafeMarkup::checkPlain($config['jquery_countdown_exp_txt']),
      '#id' => 'jquery-countdown-block-'. $config['jquery_countdown_block_id'],
      '#attached' => [
        'library' => ['jquery_countdown/jquery.countdown','jquery_countdown/jquerycountdownblock'],
        'drupalSettings' => [
          'jquery_countdown' => [
            'jquerycountdownblock' => [
              'options' => $added_selectors,
            ],
          ],
        ],
      ],
    ];
    //echo "<pre>";print_r($build);exit;
    return $build;
  }

  /**
   * Implements a block form handler.
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();
    $form['jquery_countdown_block_id'] = [
      '#type' => 'textfield',
      '#value' => ($config['jquery_countdown_block_id']) ? $config['jquery_countdown_block_id'] : Random::name().time()
    ];
    $form['jquery_countdown_event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#default_value' => ($config['jquery_countdown_event_name']) ? $config['jquery_countdown_event_name'] : '',
      '#max_length' => 250,
      '#size' => 25,
      '#required' => TRUE,
    ];

    $form['jquery_countdown_target'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start countdown from date'),
      '#default_value' => ($config['jquery_countdown_timestamp']) ? DrupalDateTime::createFromTimestamp($config['jquery_countdown_timestamp']) : DrupalDateTime::createFromTimestamp(time()),
      '#required' => TRUE
    ];

    $form['jquery_countdown_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Countdown description'),
      '#default_value' => ($config['jquery_countdown_description']) ? $config['jquery_countdown_description'] : $this->t("Until @event_name"),
      '#description' => $this->t('Enter the description to go below the countdown. You may use <strong>@event_name</strong> as a special token in this field that will be replaced with the dynamic value of the event name. The default is "Until <strong>@event_name</strong>". If you do not wish to have a description, simply leave this field blank.'),
    ];

    $form['jquery_countdown_exp_txt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Expiry Text'),
      '#default_value' => ($config['jquery_countdown_exp_txt']) ? $config['jquery_countdown_exp_txt'] : '',
      '#description' => $this->t('Enter the message that will be shown when the countdown reaches zero.'),
    ];

    $form['jquery_countdown_msg_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message format'),
      '#default_value' => ($config['jquery_countdown_msg_format']) ? $config['jquery_countdown_msg_format'] : $this->t("%days %hours %minutes %seconds"),
      '#description' => $this->t('Enter time components seperated by spaces, each component will be included in the countdown block. For example: <strong>%years %months %weeks %days %hours %minutes %seconds</strong> will display <strong>Years Months Weeks Days Hours Minutes Seconds</strong> in countdown, <strong>%days %hours</strong> will display <strong>Days Hours</strong> in countdown. The order of component will not affect the countdown output i.e. <strong>%months %years</strong> will display <strong>Years Months</strong>.'),
    ];
    return $form;
  }

  /**
   * Implements a block submit handler.
   *
   * Save configuration into system.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('jquery_countdown_event_name', $form_state->getValue('jquery_countdown_event_name'));
    $this->setConfigurationValue('jquery_countdown_description', $form_state->getValue('jquery_countdown_description'));
    $this->setConfigurationValue('jquery_countdown_exp_txt', $form_state->getValue('jquery_countdown_exp_txt'));
    $this->setConfigurationValue('jquery_countdown_msg_format', $form_state->getValue('jquery_countdown_msg_format'));
    $this->setConfigurationValue('jquery_countdown_target', $form_state->getValue('jquery_countdown_target')->format("Y-m-d H:i:s"));
    $this->setConfigurationValue('jquery_countdown_timestamp', $form_state->getValue('jquery_countdown_target')->getTimestamp());
    $this->setConfigurationValue('jquery_countdown_block_id', $form_state->getValue('jquery_countdown_block_id'));
  }
}
