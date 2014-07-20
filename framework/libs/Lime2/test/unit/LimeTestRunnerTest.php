<?php

/*
 * This file is part of the Lime framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../bootstrap/unit.php';


class TestCase
{
  public $methodCalls;

  public function __construct(LimeTest $test)
  {
    $this->methodCalls = new LimeExpectationList($test);
  }

  public function __call($method, $args)
  {
    $this->methodCalls->addActual($method);
  }

  public function handleExceptionSuccessful(Exception $error)
  {
    $this->methodCalls->addActual('handleExceptionSuccessful');

    return true;
  }

  public function handleExceptionFailed(Exception $error)
  {
    $this->methodCalls->addActual('handleExceptionFailed');

    return false;
  }

  public function testThrowsError()
  {
    1/0;
  }

  public function testThrowsException()
  {
    throw new Exception();
  }
}


$t = new LimeTest(21);


$t->diag('The test comments are printed');

  // fixtures
  $output = $t->mock('LimeOutputInterface');
  $output->comment('A test comment');
  $output->replay();
  $stub = $t->stub('Stub');
  $stub->testDoSomething();
  $stub->replay();
  $info = new LimeTestRunner($output);
  $info->addTest(array($stub, 'testDoSomething'), 'A test comment');
  // test
  $info->run();
  // assertions
  $output->verify();


$t->diag('The before callbacks are called before each test method');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $info = new LimeTestRunner();
  $info->addBefore(array($mock, 'setUp'));
  $info->addTest(array($mock, 'testDoSomething'));
  $info->addTest(array($mock, 'testDoSomethingElse'));
  $mock->setUp();
  $mock->testDoSomething();
  $mock->setUp();
  $mock->testDoSomethingElse();
  $mock->replay();
  // test
  $info->run();
  // assertions
  $mock->verify();


$t->diag('The after callbacks are called before each test method');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $info = new LimeTestRunner();
  $info->addAfter(array($mock, 'tearDown'));
  $info->addTest(array($mock, 'testDoSomething'));
  $info->addTest(array($mock, 'testDoSomethingElse'));
  $mock->testDoSomething();
  $mock->tearDown();
  $mock->testDoSomethingElse();
  $mock->tearDown();
  $mock->replay();
  // test
  $info->run();
  // assertions
  $mock->verify();


$t->diag('The before-all callbacks are called before the whole test suite');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $info = new LimeTestRunner();
  $info->addBeforeAll(array($mock, 'setUp'));
  $info->addTest(array($mock, 'testDoSomething'));
  $info->addTest(array($mock, 'testDoSomethingElse'));
  $mock->setUp();
  $mock->testDoSomething();
  $mock->testDoSomethingElse();
  $mock->replay();
  // test
  $info->run();
  // assertions
  $mock->verify();


$t->diag('The after-all callbacks are called before the whole test suite');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $info = new LimeTestRunner();
  $info->addAfterAll(array($mock, 'tearDown'));
  $info->addTest(array($mock, 'testDoSomething'));
  $info->addTest(array($mock, 'testDoSomethingElse'));
  $mock->testDoSomething();
  $mock->testDoSomethingElse();
  $mock->tearDown();
  $mock->replay();
  // test
  $info->run();
  // assertions
  $mock->verify();


$t->diag('The exception handlers are called when a test throws an exception');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $info = new LimeTestRunner();
  $info->addTest(array($mock, 'testThrowsException'));
  $info->addExceptionHandler(array($mock, 'handleExceptionFailed'));
  $info->addExceptionHandler(array($mock, 'handleExceptionSuccessful'));
  $mock->testThrowsException()->throws('Exception');
  $mock->any('handleExceptionFailed')->returns(false);
  $mock->any('handleExceptionSuccessful')->returns(true);
  $mock->replay();
  // test
  $info->run();
  // assertions
  $mock->verify();


$t->diag('If no exception handler returns true, the exception is thrown again');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $info = new LimeTestRunner();
  $info->addTest(array($mock, 'testThrowsException'));
  $info->addExceptionHandler(array($mock, 'handleExceptionFailed'));
  $mock->testThrowsException()->throws('Exception');
  $mock->any('handleExceptionFailed')->returns(false);
  $mock->replay();
  // test
  $t->expect('Exception');
  try
  {
    $info->run();
    $t->fail('The exception was thrown');
  }
  catch (Exception $e)
  {
    $t->pass('The exception was thrown');
  }
