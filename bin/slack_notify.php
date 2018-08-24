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

// Load Slack helper functions.
require_once __DIR__ . '/slack_helper.php';

// Assemble the arguments.
$slack_type = $argv[1];
$slack_channel = getenv('SLACK_CHANNEL');

switch ($slack_type) {
  case 'wordpress_updates':
    $slack_agent = 'Wordpress Update Manager';
    $slack_icon = 'https://s.w.org/style/images/about/WordPress-logotype-wmark.png';
    $slack_color = '#0678BE';
    $slack_message = 'Kicking off checks for updates for Wordpress core and contrib plugins...';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wordpress_no_coreupdates':
    $slack_agent = 'Wordpress Update Manager';
    $slack_icon = 'https://s.w.org/style/images/about/WordPress-logotype-wmark.png';
    $slack_color = '#0678BE';
    $slack_message = array('Wordpress core is up to date.');
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wordpress_coreupdates':
    $slack_agent = 'Wordpress Update Manager';
    $slack_icon = 'https://s.w.org/style/images/about/WordPress-logotype-wmark.png';
    $slack_color = '#0678BE';
    $slack_message = array(
      'Wordpress core has an update *available*: ' . str_replace('Update to ', '', str_replace('\n', '', shell_exec('terminus upstream:updates:list ${SITE_UUID}.${TERMINUS_ENV} --field=message'))),
    );
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wordpress_no_pluginupdates':
    $slack_agent = 'Wordpress Update Manager';
    $slack_icon = 'https://s.w.org/style/images/about/WordPress-logotype-wmark.png';
    $slack_color = '#0678BE';
    $slack_message = array('Wordpress contrib is up to date.');
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wordpress_pluginupdates':
    $slack_agent = 'Wordpress Update Manager';
    $slack_icon = 'https://s.w.org/style/images/about/WordPress-logotype-wmark.png';
    $slack_color = '#0678BE';
    $updates = implode(", ", array_slice($argv, 2));
    $slack_message = array('Wordpress contrib has *updates available* for the following plugins: ' . $updates);
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'visual_same':
    $slack_agent = 'BackstopJS Visual Regression';
    $slack_icon = 'https://garris.github.io/BackstopJS/assets/lemurFace.png';
    $slack_color = '#800080';
    $slack_message = array('No Visual Differences Detected!');
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'visual_different':
    // Post the File Using Uploads.IM.
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
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Build ID'] = $argv[2];
    $slack_message['Build URL'] = 'https://circleci.com/gh/jlly-crs/ci-ricebowl/' . $argv[2];
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'terminus_login':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = "Authenticating to Pantheon with Terminus machine token...";
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['CLI Version'] = '1.8.1';
    $slack_message['CLI User'] = 'drupal@echidna.ca';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'terminus_coreupdates':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = 'Applying update for Wordpress core...';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Operation'] = 'terminus upstream:updates:apply';
    $slack_message['Environment'] = '`ci-update`';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'terminus_pluginupdates':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = "Applying updates for Wordpress contrib plugins...";
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Operation'] = 'terminus wp plugin update --all';
    $slack_message['Environment'] = '`ci-update`';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'pantheon_multidev_setup':
    $slack_agent = 'Terminus';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#1ec503';
    $slack_message = "Setting up a testing environment with Pantheon Multidev...";
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    $slack_message = array();
    $slack_message['Operation'] = 'terminus multidev:create';
    $slack_message['Environment'] = '`ci-update`';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'pantheon_deploy':
    $slack_agent = 'Pantheon';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#EFD01B';
    $slack_message = array();
    $slack_message['Deploy to Environment'] = '`' . $argv[2] . '`';
    $slack_message['Message'] = 'Auto deploy of Wordpress updates (core, plugins)';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'pantheon_backup':
    $slack_agent = 'Pantheon';
    $slack_icon = 'https://d1qb2nb5cznatu.cloudfront.net/startups/i/907-cbdae2927a54d2281280f41e954e8a3d-medium_jpg.jpg';
    $slack_color = '#EFD01B';
    $slack_message = 'Creating a backup of the `live` environment.';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wizard_noupdates':
    $slack_agent = 'Wordpress Update Wizard';
    $slack_icon = '';
    $slack_color = '#666666';
    $slack_message = 'No new updates are found. Have a good day - http://framera.com/wp-content/uploads/2017/04/Have-a-Good-Day.jpg';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wizard_updates':
    $slack_agent = 'Wordpress Update Wizard';
    $slack_icon = '';
    $slack_color = '#666666';
    $slack_message = 'New updates are present and available for testing! Time to do this - https://media.giphy.com/media/12l061Wfv9RKes/giphy.gif';
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;

  case 'wizard_done':
    $slack_agent = 'Wordpress Update Wizard';
    $slack_icon = '';
    $slack_color = '#666666';

    // Post the File Using Uploads.IM.
    $file_name_with_full_path = $argv[2];
    $response = \Cloudinary\Uploader::upload($file_name_with_full_path);

    $slack_message = 'Your updates have been tested and applied. Enjoy your updated site! - ' . $response['secure_url'];
    _slack_tell($slack_message, $slack_channel, $slack_agent, $slack_icon, $slack_color);
    break;
}
