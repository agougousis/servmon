<?php
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');
$I->maximizeWindow();

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');
$I->wait(2);

// Load user management page
$I->amOnPage('/domains/delegation');

// Check that demo domain is there
$I->seeElement('#domainLine-gougousis-gr');

// Delegate a server to a user

    // Click on the man icon on a server line
    $I->click('#serverLine3 .imgLink');
    $I->wait(1);

    // Check if modal is there
    $I->seeElement('#newDelegationDialog');

    // Check if field values is correct
    $I->seeInField("#new_delegation_form input[name='dtype']",'server');
    $I->seeInField("#new_delegation_form input[name='ditem']",'3');

    // Fill the modal fields
    $I->selectOption("#new_delegation_form select[name='duser']",array('value'=>'user1@gmail.com'));

    // Click the confirmation button
    $I->click('#delegationConfirmButton');
    $I->wait(1);

    // Check that the new delegation item is there
    $I->see('Alexandros Gougousis','#serverLine3');

// Delete a server delegation

    // Click the x button on the server delegation
    $I->click('#serverLine3 .close');
    $I->wait(1);

    // Check that the server delegation was removed
    $I->dontSee('Alexandros Gougousis','#serverLine3');

// Create a domain delegation

    // Click on the man icon on a server line
    $I->click('#domainLine-takis-gr .imgLink');
    $I->wait(1);

    // Check if modal is there
    $I->seeElement('#newDelegationDialog');

    // Check if field values is correct
    $I->seeInField("#new_delegation_form input[name='dtype']",'domain');
    $I->seeInField("#new_delegation_form input[name='ditem']",'takis.gr');

    // Fill the modal fields
    $I->selectOption("#new_delegation_form select[name='duser']",array('value'=>'user1@gmail.com'));

    // Click the confirmation button
    $I->click('#delegationConfirmButton');
    $I->wait(1);

    // Check that the new delegation item is there
    $I->see('Alexandros Gougousis','#domainLine-takis-gr');

// Delete the domain delegation

    // Click the x button on the domain delegation
    $I->click('#domainLine-takis-gr .close');
    $I->wait(1);

    // Check that the server delegation was removed
    $I->dontSee('Alexandros Gougousis','#domainLine-takis-gr');

// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');

// Logout
$I->click('#logoutMenuLink');
$I->wait(1);