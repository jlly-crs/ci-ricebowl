<?php

// Load Slack helper functions
require_once( dirname( __FILE__ ) . '/slack_helper.php' );

// Assemble the Arguments
$slack_type = $argv[1]; // Argument One
$slack_channel = getenv('SLACK_CHANNEL');

switch($slack_type) {
  case 'drupal_updates':
  case 'drupal_no_coreupdates':
  case 'drupal_coreupdates':
  case 'drupal_no_moduleupdates':
  case 'drupal_moduleupdates':
    $slack_agent = 'Drupal Update Manager';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/drupal.png';
    $slack_color = '#0678BE';
    $slack_message = 'TODO';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'visualregression_finished_same':
    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/backstop.png';
    $slack_color = '#800080';
    $slack_message = 'No Visual Differences Detected!';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color); 
    break;
  case 'visualregression_finished_differences':
    // Post the File Using Uploads.IM
    $file_name_with_full_path = $argv[2];
    $target_url = 'http://uploads.im/api';
    $cFile = curl_file_create($file_name_with_full_path);
    $post = array('format' => 'json','file_contents'=> $cFile);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,$target_url);
    curl_setopt($curl, CURLOPT_POST,1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $curl_response = json_decode(curl_exec($curl));
    curl_close($curl);

    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/backstop.png';
    $slack_color = '#800080';
    $slack_message = 'Visual Differences Detected! ' . $curl_response->data->thumb_url;
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'visualregression':
    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/backstop.png';
    $slack_color = '#800080';
    $slack_message = array('Kicking off a Visual Regression test using BackstopJS between the `ci-test` and `live` environments...');
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color); 
    break;
  case 'behat': 
    $slack_agent = 'Behat';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/behat.png';
    $slack_color = '#0000000';
    $slack_message = 'Kicking off Behavioral Testing with Behat...';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message[] = '*Scenario*: A user should see "El Museo de Arte" on the homepage' . "\n" . '     *Given* I am on the homepage' . "\n" .  '      *Then* I should see the text "El Museo de Arte"';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'behat_finished':
    $slack_agent = 'Behat';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/behat.png';
    $slack_color = '#00ff00';
    $slack_message = 'Testing result: *Build PASSED*';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'circle_start':
    $slack_agent = 'CircleCI';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/circle.png';
    $slack_color = '#229922';
    $slack_message = 'Time to check for new updates! Kicking off a new build...';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Build ID'] = $argv[2];
    $slack_message['Build URL'] = 'https://circleci.com/gh/populist/drupal-auto-update/' . $argv[2];
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'terminus_login':
    $slack_agent = 'Terminus';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/terminus2.png';
    $slack_color = '#1ec503';
    $slack_message = "Authenticating to Pantheon with Terminus machine token...";
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['CLI Version'] = '1.1.2';
    $slack_message['CLI User'] = 'matt@pantheon.me';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'pantheon_multidev_setup':
    $slack_agent = 'Terminus';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/terminus2.png';
    $slack_color = '#1ec503';
    $slack_message = "Setting up Pantheon Multidev `update-dr` as the testing environment...";
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Operation'] = 'terminus multidev:create';
    $slack_message['Site URL'] = 'https://update-dr-drupalcon-github-magic.pantheonsite.io';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
}