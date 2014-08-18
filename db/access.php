<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

  'block/my_courses:myaddinstance' => array(
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => array(
        'user' => CAP_ALLOW
    ),

    'clonepermissionsfrom' => 'moodle/my:manageblocks'
  ),

  'block/my_courses:addinstance' => array(
    'riskbitmask' => RISK_SPAM | RISK_XSS,

    'captype' => 'write',
    'contextlevel' => CONTEXT_BLOCK,
    'archetypes' => array(
        'editingteacher' => CAP_ALLOW,
        'manager' => CAP_ALLOW
    ),

    'clonepermissionsfrom' => 'moodle/site:manageblocks'
  ),
);
