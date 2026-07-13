<?php

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('wpotp_provider');
delete_option('wpotp_sms_url');
delete_option('wpotp_sms_key');

delete_option('wpotp_firebase_apiKey');
delete_option('wpotp_firebase_authDomain');
delete_option('wpotp_firebase_projectId');
delete_option('wpotp_firebase_appId');