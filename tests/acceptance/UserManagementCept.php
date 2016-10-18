<?php
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');
$I->wait(2);

// Load user management page
$I->amOnPage('/user_management');

// Add a new user

    // Check if "Add new User" button is there
    $I->seeElement('#addUserButton');

    // Click the add new user button
    $I->click('#addUserButton');
    $I->wait(1);

    // Check to see if add user modal is there
    $I->seeElement('#addUserDialog');

    // Fill the form fields
    $I->fillField("#addUserDialog input[name='email']",'demo1@gmail.com');
    $I->fillField("#addUserDialog input[name='password']",'trexala00-!');
    $I->fillField("#addUserDialog input[name='verify_password']",'trexala00-!');
    $I->fillField("#addUserDialog input[name='lastname']",'Mpormpokis');
    $I->fillField("#addUserDialog input[name='firstname']",'Xristos');

    // Click the submission button
    $I->click('#add_user_confirm');
    $I->wait(1);

    // Check we are on the same page
    $I->seeInCurrentUrl('/user_management');

    // Check that the new user is in the list
    $I->see('demo1@gmail.com','#usersTable');

    // Get the new user row ID
    $rowIdList = $I->grabMultiple('#usersTable'.' tbody tr','id');
    $rowTextList = $I->grabMultiple('#usersTable'.' tbody tr');
    $rowId = $I->grabRowIdWithText($rowTextList,$rowIdList,'demo1@gmail.com');

    // Check the row values are correct
    $I->see('Xristos','#'.$rowId.' td:nth-child(2)');

    // Check that user status is disabled
    $I->seeElement('#'.$rowId.' .glyphicon-minus-sign');

    // Check that user is not superuser
    $suSrc = $I->grabAttributeFrom('#'.$rowId.' .superuserIcon','src');
    $parts = explode('/',$suSrc);
    $I->seeEqualValues('super_black.png',$parts[count($parts)-1]);

// Enable/Disable the new user

    // Click the 'Enable' button for the new user
    $I->click('#'.$rowId.' .enableUserButton');
    $I->wait(1);

    // Check that user status is enabled
    $I->seeElement('#'.$rowId.' .glyphicon-ok-sign');

    // Click the 'Disable' button for the new user
    $I->click('#'.$rowId.' .disableUserButton');
    $I->wait(1);

    // Check that user status is disabled
    $I->seeElement('#'.$rowId.' .glyphicon-minus-sign');

// Make/Unmake the new user superuser

    // Click the superuser icon
    $I->click('#'.$rowId.' .superuserIcon');
    $I->wait(1);

    // Check that the confirmation modal has appeared
    $I->seeElement('#superuserDialog');

    // Click the confirmation button
    $I->click('#superuserConfirmButton');
    $I->wait(1);

    // Check that user is superuser
    $suSrc = $I->grabAttributeFrom('#'.$rowId.' .superuserIcon','src');
    $parts = explode('/',$suSrc);
    $I->seeEqualValues('super.png',$parts[count($parts)-1]);

    // Click the superuser icon
    $I->click('#'.$rowId.' .superuserIcon');
    $I->wait(1);

    // Check that the confirmation modal has appeared
    $I->seeElement('#superuserDialog');

    // Click the confirmation button
    $I->click('#superuserConfirmButton');
    $I->wait(1);

    // Check that user is not superuser
    $suSrc = $I->grabAttributeFrom('#'.$rowId.' .superuserIcon','src');
    $parts = explode('/',$suSrc);
    $I->seeEqualValues('super_black.png',$parts[count($parts)-1]);

// View a user's profile

    // Click the "View" link
    $I->click('#'.$rowId.' td:last-child a');
    $I->wait(1);

    // Check that we are on the profile page
    preg_match('/([0-9]+)/',$rowId,$matches);
    $userId = $matches[1];
    $I->seeInCurrentUrl('user_management/'.$userId);

    // Check something on profile page
    $I->see('Registration Date','#user_profile_form');

// Delete the user

    // Go back to user management page
    $I->moveBack();
    $I->wait(2);

    // Click the "Delete" button
    $I->click('#'.$rowId.' .deleteUserButton');
    $I->wait(1);

    // Check that the modal is there
    $I->seeElement('#deleteUserDialog');

    // Click the confirmation button
    $I->click('#deleteUserConfirmButton');
    $I->wait(1);

    // Check that the user is no more on the list
    // Check that the new user is in the list
    $I->dontSee('demo1@gmail.com','#usersTable');

// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');

// Logout
$I->click('#logoutMenuLink');
$I->wait(1);