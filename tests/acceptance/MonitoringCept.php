<?php
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');
$I->wait(2);

// Load monitoring management page
$I->amOnPage('/monitor/configure');

$I->maximizeWindow();   // Otherwise the menu bar may hide the checkboxes

// Monitoring status

    // Check that monitoring is off
    $I->seeElement('#changeStatusForm .off');

    // Change the monitoring toggle button to ON
    $I->click('#changeStatusForm .toggle-group');
    $I->wait(1);

    // Change the monitoring period dropdown to 60 min
    $I->selectOption("#changeStatusForm select[name='monitoring_period']",array('value'=>60));

    // Click the save status button
    $I->click('#saveStateButton');
    $I->wait(1);

    // Check that monitoring is on
    $I->dontSeeElement('#changeStatusForm .off');

    // Check that the monitoring period is 60 min
    $I->seeInField("#changeStatusForm select[name='monitoring_period']",60);

    // Return status to original state
    $I->click('#changeStatusForm .toggle-group');
    $I->selectOption("#changeStatusForm select[name='monitoring_period']",array('value'=>30));
    $I->click('#saveStateButton');
    $I->wait(1);

// Update monitoring configuration

    // Check a specific server item is there
    $I->seeElement("#monitoringForm input[name='server--1']");

    // Check the checkbox of the first server in the list
    $I->checkOption("#monitoringForm input[name='server--1']");

    // Click the update button
    $I->click('#updateConfigurationButton');
    $I->waitForElementNotVisible('#toast-container',6);

    // Check that the server checkbox is checked
    $I->seeCheckboxIsChecked("#monitoringForm input[name='server--1']");

    // Un-check the checkbox of the first server in the list
    $I->uncheckOption("#monitoringForm input[name='server--1']");

    // Click the update button
    $I->click('#updateConfigurationButton');
    $I->wait(1);

    // Check that the server checkbox is not checked anymore
    $I->dontSeeCheckboxIsChecked("#monitoringForm input[name='server--1']");

// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');

// Logout
$I->click('#logoutMenuLink');
$I->wait(1);