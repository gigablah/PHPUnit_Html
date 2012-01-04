<?php
/** 
 * HTML format PHPUnit tests results.
 *
 * To allow the running of normal PHPUnit tests from a web browser.
 *
 * @package    PHPUnit_Html
 * @author     Nick Turner
 * @copyright  2011 Nick Turner <nick@nickturner.co.uk>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.nickturner.co.uk/
 */


/**
 * A TestRunner for the HTML WebBrowser interface.
 *
 * @package    PHPUnit_Html
 * @author     Nick Turner
 */
class PHPUnit_Html_TestRunner extends PHPUnit_TextUI_TestRunner {

    /**
     * Run the runner.
     *
     * This will get a singleton instance of the class and configure
     * it according to the given configuration and any $_REQUEST
     * overriding parameters.
     *
     * It will configure it to use a {@link PHPUnit_Html_Printer} object
     * as the default output printer.
     *
     * @param   array       $arguments  configuration
     * @return  void
     */
    public static function run($test=null, array $arguments = array()) {
		
        $printer = $arguments['printer'] = new PHPUnit_Html_Printer($arguments['tpldir']);
		
        try {
            if ($arguments['bootstrap']) {
                PHPUnit_Util_Fileloader::checkAndLoad($arguments['bootstrap'], $arguments['syntaxCheck']);
            }
			
            $runner = new PHPUnit_Html_TestRunner();
            $xml = null;

            if (isset($arguments['configuration'])) {
                $xml = PHPUnit_Util_Configuration::getInstance($arguments['configuration']);
                $config = $xml->getPHPUnitConfiguration();
                if (is_array($config)) $arguments = array_merge($config, $arguments);
            }
			
            if ($xml && !isset($arguments['test']) && !isset($arguments['testFile'])) {
                $suite = $xml->getTestSuiteConfiguration($arguments['syntaxCheck']);
            }
            else {
                $arguments['test'] = getcwd();
                $suite = $runner->getTest(
                    $arguments['test'],
                    $arguments['testFile'],
                    $arguments['syntaxCheck']
                );
            }

            $result = $runner->doRun($suite, $arguments);
            $printer->printResult($result);

        } catch (Exception $e) {

            $printer->printAborted($e);

        }
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4: */

?>
