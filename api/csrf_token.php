<?php
/**
 * JASS Logistics - CSRF Generator v2
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/CSRF.php';

sendResponse(true, 'Token generated', ['csrf_token' => CSRF::generateToken()]);
