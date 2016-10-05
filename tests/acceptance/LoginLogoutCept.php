<?php 
$I = new AcceptanceTester($scenario);

$I->wantTo('login to website');

// Load the landing page
$I->amOnPage('/');
$I->seeElement('#inputEmail');

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');

// Check that home page has been loaded
$I->wait(1);
$I->seeCurrentUrlEquals('/home');
$I->seeElement('#addDomainButton');

// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');
 
// Logout
$I->click('#logoutMenuLink');
        
// Check we are on landing page
$I->wait(1);
$I->seeCurrentUrlEquals('/');
$I->seeElement('#inputEmail');

