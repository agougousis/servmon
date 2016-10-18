<?php
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');
$I->maximizeWindow();

// Click on the "Forgot your password?" button
$I->click('Forgot your password?');
$I->wait(1);

// Check the password reset form is there
$I->seeElement('#password_request_form');

// Fill the form fields
$I->fillField("#password_request_form input[name='email']",'agougousis@hcmr.gr');
$I->wait(9); // complete the captcha manually
$I->click('#password_request_form_button');
$I->wait(3);

// Get the reset link code from database
$uid = $I->grabFromDatabase('users', 'id', array('email' => 'agougousis@hcmr.gr'));
$code = $I->grabFromDatabase('password_reset_links', 'code', array('uid' => $uid));

// Go to password reset page
$path = '/password_reset/'.$code;
$I->amOnPage($path);
$I->wait(2);

// Check that the password reset page has been loaded normally
$I->seeElement('#send_password_button');

// Fill in the new password
$I->fillField("input[name='new_password']",'user1pwd');
$I->fillField("input[name='repeat_password']",'user1pwd');

// Click the button to send the new password
$I->click('#send_password_button');
$I->wait(2);

// Check that we are on login page
$I->see('Sign in');