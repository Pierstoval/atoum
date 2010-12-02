<?php

namespace mageekguy\atoum\tests\units\score;

use \mageekguy\atoum;
use \mageekguy\atoum\mock;
use \mageekguy\atoum\score;

require_once(__DIR__ . '/../../runner.php');

class coverage extends atoum\test
{
	public function test__construct()
	{
		$coverage = new score\coverage();

		$this->assert
			->object($coverage)->isInstanceOf('\countable')
			->array($coverage->getLines())->isEmpty()
			->array($coverage->getMethods())->isEmpty()
		;
	}

	public function testAddXdebugData()
	{
		$coverage = new score\coverage();

		$this->assert
			->object($coverage->addxdebugData($this, array()))->isIdenticalTo($coverage)
			->array($coverage->getLines())->isEmpty()
		;

		$mockGenerator = new mock\generator();
		$mockGenerator
			->generate('\reflectionClass')
			->generate('\reflectionMethod')
		;

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->isAbstract = false;
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getStartLine = 6;
		$methodController->getEndLine = 8;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };
		$classController->getMethods = array(new mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$coverage->setReflectionClassInjector(function($class) use ($classController) { return new mock\reflectionClass($class, $classController); });

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				5 => 1,
				6 => 2,
				7 => 3,
				8 => 2,
				9 => 1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$this->assert
			->object($coverage->addxdebugData($this, $xdebugData))->isIdenticalTo($coverage)
			->array($coverage->getLines())->isEqualTo(array($classFile => array(6 => 2, 7 => 3, 8 => 2)))
			->array($coverage->getMethods())->isEqualTo(array(
					$classFile => array(
						$className => array(
							$methodName => array(
								6 => 2,
								7 => 3,
								8 => 2
							)
						)
					)
				)
			)
			->object($coverage->addxdebugData($this, $xdebugData))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEqualTo(array(
					$classFile => array(
						$className => array(
							$methodName => array(
								6 => 4,
								7 => 6,
								8 => 4
							)
						)
					)
				)
			)
		;
	}

	public function testMerge()
	{
		$mockGenerator = new mock\generator();
		$mockGenerator
			->generate('\reflectionClass')
			->generate('\reflectionMethod')
		;

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getStartLine = 6;
		$methodController->getEndLine = 8;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };
		$classController->getMethods = array(new mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				5 => 1,
				6 => 2,
				7 => 3,
				8 => 2,
				9 => 1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage = new score\coverage();

		$coverage
			->setReflectionClassInjector(function($class) use ($classController) { return new mock\reflectionClass($class, $classController); })
		;

		$otherCoverage = new score\coverage();
		$otherCoverage
			->setReflectionClassInjector(function($class) use ($classController) { return new mock\reflectionClass($class, $classController); })
			->addxdebugData($this, $xdebugData)
		;

		$this->assert
			->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
			->array($coverage->getLines())->isEqualTo(array($classFile => array(6 => 2, 7 => 3, 8 => 2)))
			->array($coverage->getMethods())->isEqualTo(array(
					$classFile => array(
						$className => array(
							$methodName => array(
								6 => 2,
								7 => 3,
								8 => 2
							)
						)
					)
				)
			)
		;

		$this->assert
			->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
			->array($coverage->getLines())->isEqualTo(array($classFile => array(6 => 4, 7 => 6, 8 => 4)))
			->array($coverage->getMethods())->isEqualTo(array(
					$classFile => array(
						$className => array(
							$methodName => array(
								6 => 4,
								7 => 6,
								8 => 4
							)
						)
					)
				)
			)
		;
	}

	public function testCount()
	{
		$coverage = new score\coverage();

		$this->assert
			->integer(sizeof($coverage))->isZero()
		;
	}
}

?>
