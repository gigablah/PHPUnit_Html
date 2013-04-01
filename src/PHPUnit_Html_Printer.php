<?php
/**
 * HTML format PHPUnit tests results.
 *
 * To allow the running of normal PHPUnit tests from a web browser.
 *
 * @package    PHPUnit_Html
 * @author     Nick Turner
 * @author     Chris Heng
 * @copyright  2011 Nick Turner <nick@nickturner.co.uk>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.nickturner.co.uk/
 */

/**
 * Prints the result of a PHPUnit_TextUI_TestRunner to a web browser
 * in HTML format.
 *
 * @package    PHPUnit_Html
 * @author     Nick Turner
 * @author     Chris Heng
 */
class PHPUnit_Html_Printer extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var array Reference to current suite in {@link $results}
     */
    private $_suite = null;

    /**
     * @var array Reference to current test in {@link $results}
     */
    private $_test = null;

    /**
     * @var string Path to template directory
     */
    protected $tpldir = null;

    /**
     * @var array Array of tests by suite
     */
    protected $results = array();

    /**
     * @var array Array of cached file contents
     */
    protected $source = array();

    /**
     * @var array Array of deprecated information
     */
    protected $deprecated = array();

    /**
     * Constructor.
     *
     * @param string $tpldir Template directory
     *
     * @throws InvalidArgumentException
     */
    public function __construct($tpldir)
    {
        $this->tpldir = rtrim($tpldir, DIRECTORY_SEPARATOR);
        if (!is_dir($this->tpldir)) {
            throw new \InvalidArgumentException('No such template directory: '.$this->tpldir);
        }
    }

    /**
     * Return a URL to a resource
     *
     * Resource are served up by the main index so that the libraries resources need not
     * by in a directory accessible to the web server.
     *
     * @param string $file Virtual path to required resource
     *
     * @return string
     */
    protected function url($file)
    {
        return str_replace(' ', '%20', (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '').'/'.$file);
    }

    /**
     * Retrieve the relevant portion of the PHP source file with syntax highlighting
     *
     * @param string $fileName Path to the source file
     * @param int    $midLine  Line to show around
     * @param int    $numLines Number of lines to show
     *
     * @return string Highlighted source HTML formatted
     */
    protected function highlightSourceAround($fileName, $midLine, $numLines = true)
    {
        $offset = max(0, $midLine - ceil($numLines / 2));
        return $this->highlightSource($fileName, $offset, $numLines, $midLine);
    }

    /**
     * Retrieve the relevant portion of the PHP source file with syntax highlighting
     *
     * @param string $fileName  Path to the source file
     * @param int    $firstLine First line number to show
     * @param int    $numLines  Number of lines to show
     * @param int    $markLine  Line number to mark if required
     *
     * @return string Highlighted source HTML formatted
     */
    protected function highlightSource($fileName, $firstLine = 1, $numLines = null, $markLine = null)
    {
        if (!isset($this->source[$fileName])) {
            $lines = highlight_file($fileName, true);
            $lines = explode("<br />", $lines);
            $this->source[$fileName] = $lines;
        } else {
            $lines = $this->source[$fileName];
        }

        $lines = array_slice($lines, $firstLine - 1, $numLines);

        $html = '<table class="code" cellpadding="0" cellspacing="0" border="0">';
        $row = 0;
        $lineno = $firstLine;
        foreach ($lines as $line) {
            $html .= '<tr class="line'.($lineno == $markLine ? ' hilite' : '').($row & 1 ? ' odd' : ' even').'"><td class="linenum">'.$lineno.'</td><td class="linetxt"><span>'.$line.'</span></td></tr>';
            $lineno++;
            $row++;
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Retrieve the source for the given test with syntax highlighting
     *
     * @param string $fileName  Path to the source file
     * @param int    $firstLine First line number to show
     * @param int    $numLines  Number of lines to show
     * @param int    $markLine  Line number to mark if required
     *
     * @return string Highlighted source HTML formatted
     */
    protected function listing($suite, $test)
    {
        // Return file source code
        if (strpos($suite['name'], '::') !== false) {
            list($suite['name'], $dummy) = explode('::', $suite['name']);
        }
        if (strpos($test['name'], ' ') !== false) {
            list($test['name'], $dummy) = explode(' ', $test['name']);
        }
        $r = new \ReflectionMethod($suite['name'], $test['name']);
        $f = $r->getFileName();
        $s = $r->getStartLine();
        $e = $r->getEndLine();

        return '<h1>'.htmlentities($f).'</h1>'.$this->highlightSource($f, $s, ($e - $s) + 1);
    }

    /**
     * Return reference to named test in {@link $results}
     *
     * @param string $name Test name
     *
     * @return array Reference to test
     */
    protected function &test($name)
    {
        if ($name === null) {
            unset($this->_test);
            $this->_test = null;
        } else {
            if (!$this->_suite) {
                throw new \Exception('Suite is unknown');
            }
            if (!isset($this->_suite['tests'][$name])) {
                $this->_suite['tests'][$name] = array('name' => $name, 'test' => null, 'errors' => null, 'output' => null, 'status' => null, 'results' => null, 'assertions' => 0, 'deprecated' => null, 'time' => 0);
            }
            $this->_test =& $this->_suite['tests'][$name];
        }

        return $this->_test;
    }

    /**
     * Return reference to named suite in {@link $results}
     *
     * @param string $name Suite name
     *
     * @return array Reference to suite
     */
    protected function &suite($name)
    {
        if (strpos($name, '::') !== false) {
            list($name, $dummy) = explode('::', $name);
        }
        if ($name === null) {
            //unset($this->_suite);
            //$this->_suite = null;
        } else {
            if (!isset($this->results['suites'][$name])) {
                $this->results['suites'][$name] = array('name' => $name, 'suite' => null, 'tests' => array(), 'status' => null, 'stats' => null, 'assertions' => 0, 'errors' => 0, 'deprecated' => 0, 'time' => 0);
            }
            $this->_suite =& $this->results['suites'][$name];
        }

        return $this->_suite;
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $t =& $this->test($test->getName());
        $t['status'] = 'failed';
        $t['errors'][] = compact('e', 'time');
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $t =& $this->test($test->getName());
        $t['status'] = 'failed';
        $t['result'] = compact('e', 'time');
    }

   /**
     * Incomplete test
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $t =& $this->test($test->getName());
        $t['status'] = 'incomplete';
        $t['result'] = compact('e', 'time');
    }

   /**
     * Skipped test
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $t =& $this->test($test->getName());
        $t['status'] = 'skipped';
        $t['result'] = compact('e', 'time');
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $t =& $this->test($test->getName());
        $t['test'] = $test;
        ob_start();
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof PHPUnit_Framework_Warning) {
            ob_end_clean();
            throw new \Exception($test->getStatusMessage());
        }
        $t =& $this->test($test->getName());
        if (!$t['status']) {
            $t['status'] = 'passed';
        }
        $deprecated = $test->getTestResultObject()->deprecatedFeatures();
        $t['deprecated'] = array_diff($deprecated, $this->deprecated);
        $t['assertions'] = $test->getNumAssertions();
        $t['output'] = ob_get_contents();
        $t['time'] = $time;
        $this->deprecated = $deprecated;
        $this->test(null);
        ob_end_clean();
    }

    /**
     * A testsuite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $s =& $this->suite($suite->getName());
        $t['suite'] = $suite;
    }

    /**
     * A testsuite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $s =& $this->suite($suite->getName());
        $s['stats'] = array('total' => count($s['tests']), 'passed' => 0, 'failed' => 0, 'skipped' => 0, 'incomplete' => 0);
        foreach ($s['tests'] as $t) {
            $s['stats'][$t['status']]++;
            $s['deprecated'] += count($t['deprecated']);
            $s['errors'] += count($t['errors']);
            $s['assertions'] += $t['assertions'];
            $s['time'] += $t['time'];
        }
        $s['status'] = (($s['stats']['passed'] === $s['stats']['total']) ? 'passed' : (($s['stats']['failed'] > 0) ? 'failed' : 'warning'));
        $this->suite(null);
    }

    /**
     * Get results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Write the given buffer to the output stream.
     *
     * At the moment all writes are discarded.
     *
     * @param string $buffer Data to write to output
     */
    public function write($buffer)
    {
    }

    /**
     * Print the final results in HTML format.
     *
     * The output is printed using the templates in the given
     * template directory {@link $tpldir}.
     *
     * @param PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $title = $result->topTestSuite()->getName();
        $suiteno = 0;
        $numsuites = count($this->results['suites']);
        include($this->tpldir.'/header.php');
        foreach ($this->results['suites'] as $suitename => $suite) {
            if ($suitename && count($suite['tests'])) {
                include($this->tpldir.'/suite.php');
            }
            $suiteno++;
        }
        include($this->tpldir.'/footer.php');
    }

    /**
     * Print an abort page.
     *
     * The output is printed using the templates in the given
     * template directory {@link $tpldir}.
     *
     * @param PHPUnit_Framework_TestResult $result
     */
    public function printAborted(\Exception $e)
    {
        $title = 'Aborted';
        include($this->tpldir.'/header.php');
        include($this->tpldir.'/aborted.php');
        include($this->tpldir.'/footer.php');
    }
}
