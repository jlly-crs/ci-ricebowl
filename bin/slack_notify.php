<?php

require __DIR__ . '/../vendor/autoload.php';

\Cloudinary::config(array(
  "cloud_name" => getenv('CLOUDINARY_CLOUD_NAME'),
  "api_key" => getenv('CLOUDINARY_KEY'),
  "api_secret" => getenv('CLOUDINARY_SECRET')
));

/**
 * @file
 * Contains the slack notification helper.
 */

// Load environment variables
$cli_user = 'Drupal@echidna.ca';
$environment = 'CRSStore';

// Load Slack helper functions.
require_once __DIR__ . '/slack_helper.php';

// Assemble the arguments.
$slack_type = $argv[1];
$slack_channel = getenv('SLACK_CHANNEL');

switch($slack_type) {
  case 'drupal_updates':
    $slack_agent = 'Drupal Update Manager';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/drupal.png';
    $slack_color = '#0678BE';
    $slack_message = 'Kicking off checks for updates for Drupal core and contrib modules...';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'drupal_no_coreupdates':
		$slack_agent = 'Drupal Update Manager';
		$slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/drupal.png';
		$slack_color = '#0678BE';
		$slack_message = array('Drupal core is up to date.');
		_slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
		break;
  case 'drupal_coreupdates':
		$slack_agent = 'Drupal Update Manager';
		$slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/drupal.png';
		$slack_color = '#0678BE';
		$slack_message = array('Drupal core has an update *available*: ' . str_replace('Update to ', '', str_replace('\n', '', shell_exec('terminus upstream:updates:list ${SITE_UUID}.${TERMINUS_ENV} --field=message'))));
		_slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
		break;
  case 'drupal_no_moduleupdates':
		$slack_agent = 'Drupal Update Manager';
		$slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/drupal.png';
		$slack_color = '#0678BE';
		$slack_message = array('Drupal contrib is up to date.');
		_slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
		break;
  case 'drupal_moduleupdates':
    $slack_agent = 'Drupal Update Manager';
    $slack_icon = 'http://live-drupalcon-github-magic.pantheonsite.io/sites/default/files/icons/drupal.png';
    $slack_color = '#0678BE';
    $updates = implode(", ", array_slice($argv,2));
    $slack_message = array('Drupal contrib has *updates available* for the following modules: ' . $updates);
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
  case 'visual_same':
    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'https://garris.github.io/BackstopJS/assets/lemurFace.png';
    $slack_color = '#800080';
    $slack_message = array('No Visual Differences Detected!');
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color); 
    break;
  case 'visual_different':
    // Post the File Using Uploads.IM
    $file_name_with_full_path = $argv[2];
    $response = \Cloudinary\Uploader::upload($file_name_with_full_path);

    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'https://garris.github.io/BackstopJS/assets/lemurFace.png';
    $slack_color = '#800080';
    $slack_message = 'Visual regression tests failed! Please review the <https://dashboard.pantheon.io/sites/' . getenv('SITE_UUID') . '#' . getenv('TERMINUS_ENV') . '/code|the ' . getenv('TERMINUS_ENV') . ' environment>! ' . $response['secure_url'];
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'visual':
    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'https://garris.github.io/BackstopJS/assets/lemurFace.png';
    $slack_color = '#800080';
    $slack_message = 'Kicking off a Visual Regression test using BackstopJS between the `ci-update` and `live` environments...';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'circle_start':
    $slack_agent = 'CircleCI';
    $slack_icon = 'https://circleci.com/circleci-logo-stacked-fb.png';
    $slack_color = '#229922';
    $slack_message = 'Time to check for new updates! Kicking off a new build...';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Build ID'] = $argv[2];
    $slack_message['Build URL'] = 'https://circleci.com/gh/Opswatch/ci-ricebowl/' . $argv[2];
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'terminus_login':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = "Authenticating to Pantheon with Terminus machine token...";
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['CLI Version'] = '1.8.1';
    $slack_message['CLI User'] = $cli_user;
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'terminus_coreupdates':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = 'Applying update for Drupal core...';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
		$slack_message = array();
		$slack_message['Operation'] = 'terminus upstream:updates:apply';
		$slack_message['Environment'] = $environment;
		_slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'terminus_moduleupdates':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = "Applying updates for Drupal contrib modules...";
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Operation'] = 'terminus drush pm-updatecode';
    $slack_message['Environment'] = $environment;
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'pantheon_multidev_setup':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = "Setting up a testing environment with Pantheon Multidev...";
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Operation'] = 'terminus multidev:create';
    $slack_message['Environment'] = $environment;
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'pantheon_deploy':
    $slack_agent = 'Pantheon';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#EFD01B';
    $slack_message = array();
    $slack_message['Deploy to Environment'] = '`' . $argv[2] . '`';
    $slack_message['Message'] = 'Auto deploy of Drupal updates (core, modules)';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'pantheon_backup':
    $slack_agent = 'Pantheon';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#EFD01B';
    $slack_message = 'Creating a backup of the `live` environment.';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);   
    break;

  case 'wizard_noupdates':
    $slack_agent = 'Drupal Update Wizard';
    $slack_icon = '';
    $slack_color = '#666666';
    $slack_message = 'No new updates are found. Have a good day - http://framera.com/wp-content/uploads/2017/04/Have-a-Good-Day.jpg';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wizard_updates':
		$slack_agent = 'Drupal Update Wizard';
		$slack_icon = '';
    $slack_color = '#666666';
    $slack_message = 'New updates are present and available for testing! Time to do this - https://media.giphy.com/media/12l061Wfv9RKes/giphy.gif';
    _slack_tell( $slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

	case 'wizard_done':
		$slack_agent = 'Drupal Update Wizard';
		$slack_icon = '';
		$slack_color = '#666666';

    // Post the File Using Uploads.IM
    $file_name_with_full_path = $argv[2];
    $response = \Cloudinary\Uploader::upload($file_name_with_full_path);

    $slack_message = 'Your updates have been tested and applied. Enjoy your updated site! - '. $response['secure_url'];
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
}

