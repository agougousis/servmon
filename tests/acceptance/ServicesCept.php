<?php
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');
$I->wait(2);

// Add a service

        // Select a root domain with servers
        $I->click("a[id='treeItem-gougousis.gr_anchor']");
        $I->wait(1);

        // Click on the server line
	$serverId = $I->grabAttributeFrom('.server-list-row:last-child','id');
        $serverHostname = $I->grabTextFrom('#'.$serverId.' td:nth-child(2)');
        $I->click('#'.$serverId);
        $I->wait(1);

        // Check that the add service icon is there
	$I->seeElement('#add-service-icon');

        // Click the add service icon
	$I->click('#add-service-icon');
        $I->wait(1);

        // Check that the modal is there
	$I->seeElement('#addServiceDialog');

        // Check that server field value is correct
	$I->seeInField("#addServiceDialog input[name='server']",$serverHostname);

        // Fill the modal fields
        $I->selectOption("#addServiceDialog select[name='stype']",'mysql');
        $I->fillField("#addServiceDialog input[name='version']",'5.4');

        // Check that the port field has been auto-completed
        $I->seeInField("#addServiceDialog input[name='port']",'3306');

        // Click the submit button
	$I->click('#add_service_button');
        $I->wait(2);

        // Check that the new service line is there
        $serviceRowId = $I->grabAttributeFrom('#services-list-table tbody tr:last-child','id');
	$I->see('MySQL',"#$serviceRowId");

        // Check that service line values are correct
        $I->see('MySQL','#'.$serviceRowId.' td:nth-child(2)');
        $I->see('5.4','#'.$serviceRowId.' td:nth-child(3)');
        $imageSrc = $I->grabAttributeFrom('#'.$serviceRowId.' img.serviceImg','src');
        $parts = explode('/',$imageSrc);
        $I->seeEqualValues($parts[count($parts)-1],'mysql.png');

// Edit the service

        // Click on the service edit icon
        $I->click('#'.$serviceRowId.' img.serviceImg');
        $I->wait(1);

        // Check that the service editing modal is there
        $I->seeElement('#serviceInfoDialog');

        // Change the value of one field
        $I->fillField("#serviceInfoDialog input[name='version']",'5.3');

        // Submit changes
        $I->click('#update_service_button');
        $I->wait(1);

        // Check that service line has been updated
        $I->see('5.3','#'.$serviceRowId.' td:nth-child(3)');


// Delete the service

        // Click on the service edit icon
        $I->click('#'.$serviceRowId.' img.serviceImg');
        $I->wait(1);

        // Click on the delete button
        $I->click('#delete_service_button');
        $I->wait(1);

        // Check that service line has been removed
        $I->dontSeeElement('#'.$serviceRowId);

// Unfold main menu
$I->click('#fullnameMenuLink');
$I->seeElement('#logoutMenuLink');

// Logout
$I->click('#logoutMenuLink');
$I->wait(1);