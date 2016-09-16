<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $prophet = $this->prophesize(Spiderman::class);
        $prophet->jump()->shouldBeCalled();
        
        $spiderman = $prophet->reveal();
        $spiderman->jump();

    }
}

class Spiderman {
    
    public function jump(){
        
    }
        
    
}