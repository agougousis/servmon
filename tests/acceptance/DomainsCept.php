<?php 
$I = new AcceptanceTester($scenario);

// Load the landing page
$I->amOnPage('/');

// Login
$I->fillField('inputEmail','user1@gmail.com');
$I->fillField('inputPassword','user1pwd');
$I->click('#loginButton');
$I->wait(2);

// Add a new root domain

    // Check if the "add domain" icon is there
    $I->seeElement('#addDomainButton');
    
    // Click the "add domain" icon
    $I->click('#addDomainButton');
    $I->wait(1);
    
    // Check if the modal is there
    $I->seeElement("#addDomainDialog");
    
    // Fill in the fields
    $I->fillField("node_name","mydom.gr");

    // Click the submit button
    $I->click("#add_domain_confirm_button");
    $I->wait(1);

    // Check if the new root domain is there
    $I->seeElement("a[id='treeItem-mydom.gr_anchor']");
	
// Add a new subdomain

    // Click on the new root domain
    $I->click("a[id='treeItem-mydom.gr_anchor']");    
    
    // Check if the domain css has changed
    $I->seeElement(['id'=>'treeItem-mydom.gr_anchor','class'=>'jstree-clicked']);

    // Click the "add domain" icon
    $I->click('#addDomainButton');
    $I->wait(1);
    
    // Check if the parent domain field has the right value
    $I->seeInField(['name'=>'parent_domain'],'mydom.gr');
    
    // Fill in the fields
    $I->fillField("node_name","mysubdom");
    
    // Click the submit button
    $I->click("#add_domain_confirm_button");
    $I->wait(1);

    // Unfold the new root domain
    $I->click("#treeItem-mydom\.gr i");

    // Check if the new subdomain is there
    $I->seeElement("a[id='treeItem-mysubdom.mydom.gr_anchor']");
    
// Delete a subdomain

    // Click on the new subdomain
    $I->click("a[id='treeItem-mysubdom.mydom.gr_anchor']");

    // Click on the delete domain icon
    $I->click('#deleteDomainButton');
    $I->wait(1);

    // Check if the warning modal has been appeared
    $I->seeElement('#deleteDomainDialog');
    
    // Click on the OK button
    $I->click('#delete_domain_confirm_button');
    $I->wait(1);
        
    // Unfold the new root domain
    $I->click("#treeItem-mydom\.gr i");

    // Check if the subdomain has been removed from the tree   
    $I->dontSeeElement("a[id='treeItem-mysubdom.mydom.gr_anchor']");
            
    // Click on the new root domain
    $I->click("a[id='treeItem-mydom.gr_anchor']");
    $I->wait(1);
    
    // Click on the OK button
    $I->click('#delete_domain_confirm_button');