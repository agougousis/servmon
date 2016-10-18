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
$I->amOnPage('/backup');
$I->maximizeWindow();   // Otherwise the menu bar may hide the checkboxes

// Check something on the page
$I->seeElement('li#domains_counter');

// Create a new backup

    // Click the backup button
    $I->click('#createBackupButton');
    $date = gmdate('d-m-Y H:i');
    $I->wait(1);

    // Check a very recent backup is at the end of the backups list
    $I->see($date,'#backup-list-table tbody tr:last-child');

// Delete the backup

    // Click on the backup delete icon
    $I->click('#backup-list-table tbody tr:last-child .backupDeleteIcon');
    $I->wait(1);

    // Check the modal is there
    $I->seeElement('#deleteBackupDialog');

    // Click the confirmation button
    $I->click('#deleteBackupButton');
    $I->wait(1);

    // Check that the last backup is not there
    $I->dontSee($date,'#backup-list-table');


// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');

// Logout
$I->click('#logoutMenuLink');
$I->wait(1);