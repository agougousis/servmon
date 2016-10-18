<?php
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');
$I->wait(2);

// Add a new server

	// Unfold a root domain with subdomains
        $I->click("#treeItem-gougousis\.gr i");

        // Select a subdomain
	$I->click("a[id='treeItem-dom1.gougousis.gr_anchor']");
        $I->wait(2);

        // Click the "add server" icon
        $I->click('#addServerButton');
        $I->wait(1);

        // Check if the modal is there
        $I->seeElement("#addServerDialog");

        // Check that the domain field value is right
	$I->seeInField(['name'=>'domain'],'dom1.gougousis.gr');

        // Fill in the fields
	$I->fillField('hostname','tomas');
        $I->fillField('ip','136.243.7.194');
        $I->fillField('os','Windows 7');

        // Click on the submit button
	$I->click('#add_server_confirm_button');
        $I->wait(1);

        // Unfold the root domain with subdomains
        $I->click("#treeItem-gougousis\.gr i");

        // Select the subdomain
	$I->click("a[id='treeItem-dom1.gougousis.gr_anchor']");
        $I->wait(2);

        // Check that the new server line is there
	$I->see('tomas','.server-list-row');

        // Check that the server line has correct info
        $serverId = $I->grabAttributeFrom('.server-list-row:last-child','id');
        $I->see('tomas','#'.$serverId.' td:nth-child(2)');
        $I->see('dom1.gougousis.gr','#'.$serverId.' td:nth-child(3)');
        $I->see('136.243.7.194','#'.$serverId.' td:nth-child(4)');
        $I->see('Windows 7','#'.$serverId.' td:nth-child(5)');

// Edit a server info

        // Select the server line
        $I->click('#'.$serverId);
        $I->wait(1);

        // Check that the server line css has changed
	$I->seeElement(['id'=>$serverId,'class'=>'selected-server-line']);

        // Check that the edit server icon is there
	$I->seeElement('#editServerButton');

        // Click the edit server icon
	$I->click('#editServerButton');
        $I->wait(1);

        // Check that modal has appeared
	$I->seeElement('#editServerDialog');

        // Check that field values are correct
	$I->seeInField("#editServerDialog input[name='domain']",'dom1.gougousis.gr');
        $I->seeInField("#editServerDialog input[name='hostname']",'tomas');
        $I->seeInField("#editServerDialog input[name='ip']",'136.243.7.194');
        $I->seeInField("#editServerDialog input[name='os']",'Windows 7');

        // Change some field values
	$I->fillField("#editServerDialog input[name='hostname']",'mike');

        // Click the submit button
	$I->click('#edit_server_confirm_button');
        $I->wait(1);

        // Check that server line has been updated
        $I->seeInField("#editServerDialog input[name='hostname']",'mike');

// Delete the new server

        // Check that server delete button is there
        $I->seeElement('#deleteServerButton');

        // Click the server delete button
        $I->click('#deleteServerButton');
        $I->wait(1);

        // Check that the delete server dialog is there
        $I->seeElement('#deleteServerDialog');

        // Click the confirmation button
        $I->click('#delete_server_confirm_button');
        $I->wait(1);

        // Check that the server line was removed
        $I->dontSee('#'.$serverId);

// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');

// Logout
$I->click('#logoutMenuLink');
$I->wait(1);