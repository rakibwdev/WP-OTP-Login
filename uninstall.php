<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('wpotp_method');
delete_option('wpotp_sms_url');
delete_option('wpotp_sms_key');