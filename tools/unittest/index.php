<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
apd_set_pprof_trace();
require_once '../../lib/base/boot.php';
import('../../lib/unittest');

$libTest = new UnitLibraryTestCase('../../lib');
$libTest->test();
$cases = $libTest->getCompletedTestCases();
$favicon = $libTest->passed() ? 'success' : 'failed';


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>Unit test</title>
		<link rel="icon" href="favicon_<?=$favicon?>.ico" type="image/x-icon" />
		<link rel="shortcut icon" href="favicon_<?=$favicon?>.ico" type="image/x-icon" /> 
		<link rel="stylesheet" href="style.css" media="all" title="Style" />
	</head>
	<body>
		<h1>Unit test</h1>
		<?
		
		foreach($cases as $case)
		{
			# Assemble class info
			$classInfo = $case->getClassInfo();
			$ifs = $classInfo->getInterfaces();
			$ifNames = array();
			foreach($ifs as $if)
				$ifNames[] = $if->getName();
			
			
			# Render HTML
			print '<div class="case">'
				. '<h2 class="header '.($case->passed() ? 'passed' : 'failed').'">'
				. ($classInfo->isAbstract() ? ' abstract' : '')
				. ($classInfo->isFinal() ? ' final' : '')
				. ($classInfo->isInterface() ? ' interface' : ' class')
				. ' '.$classInfo->getName()
				. ($classInfo->getParentClass() ? ' extends '.$classInfo->getParentClass()->getName() : '')
				. ($ifNames ? ' implements '.implode(' ', $ifNames) : '')
				. '</h2>'
				. '<div class="body">'
				. 'Defined in ' . $classInfo->getFileName() . '<br />';
			
			# Render exception, if any
			if($case->hasException())
				print ABException::format($case->getException());
			
			
			# Render each assertion
			if($case->getAssertions())
			{
				$numAssertions = count($case->getAssertions());
				print '<h3>' . $numAssertions . ' failure'.($numAssertions > 1 ? 's' : '').':</h3>';
				foreach($case->getAssertions() as $assertion)
					print '<div class="assertion html">' . $assertion->toHTML() . '</div>';
			}
			
			# Finish him!
			print '</div>'
				. '</div>';
		}
		
		?>
	</body>
</html>