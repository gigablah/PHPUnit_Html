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
 * PHPUnit_Html configuration and execution container.
 *
 * @package    PHPUnit_Html
 * @author     Nick Turner
 * @author     Chris Heng
 */
class PHPUnit_Html
{
    /**
     * @var array                       default configuration options
     */
    private $defaults = array(
        'phpunit_html' => null,
        'phpunit' => null,
        'tpldir' => null,
        'template' => 'default',
        'test' => null,
        'testFile' => null,
        'bootstrap' => null,
        'configuration' => null,
        'noConfiguration' => false,
        'coverageClover' => null,
        'coverageHtml' => null,
        'filter' => null,
        'groups' => null,
        'excludeGroups' => null,
        'processInsolation' => false,
        'syntaxCheck' => false,
        'stopOnError' => false,
        'stopOnFailure' => false,
        'stopOnIncomplete' => false,
        'stopOnSkipped' => false,
        'noGlobalsBackup' => true,
        'staticBackup' => true,
        'strict' => false,
    );

    /**
     * @var array                       runtime configuration options
     */
    private $config;

    /**
     * Constructor.
     *
     * @param   array       $config     configuration options
     */
    public function __construct($config = array())
    {
        $this->handleResourceRequest($config);

        // Merge any config parameters passed in
        if (isset($config) && is_array($config)) {
            $this->config = array_merge($this->defaults, $config);
        } else {
            $this->config = $this->defaults;
        }

        // Merge any config parameters specified in the request
        foreach ($_REQUEST as $n => $v) {
            if (!array_key_exists($n, $defaults)) {
                throw new \Exception('Unknown request parameter: '.$n);
            }

            if (is_bool($this->config[$n])) {
                if (!isset($v) || $v === '' || strcasecmp($v, 'true') === 0 || $v === '1') {
                    $_REQUEST[$n] = true;
                } else if (strcasecmp($v, 'false') === 0 || $v === '0') {
                    $_REQUEST[$n] = false;
                } else {
                    throw new \Exception("Request parameter '$n' must be either '0', '1', 'true', 'false'.");
                }
            } else if (!isset($v) || $v === '') {
                throw new \Exception("Request parameter '$n' must have a value.");
            }

            $this->config[$n] = $_REQUEST[$n];
        }

        // Sanitize a few config variables
        if (is_null($this->config['tpldir'])) {
            $this->config['tpldir'] = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.($this->config['template'] ?: 'default').'/';
        }
        if (!is_dir($this->config['tpldir'])) {
            $this->config['template'] = 'default';
            $this->config['tpldir'] = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR;
        }
        if (isset($this->config['groups']) && is_string($this->config['groups'])) {
            $this->config['groups'] = explode(',', $this->config['groups']);
        }
        if (isset($this->config['excludeGroups']) && is_string($this->config['excludeGroups'])) {
            $this->config['excludeGroups'] = explode(',', $this->config['excludeGroups']);
        }

        if (method_exists('PHP_CodeCoverage_Filter', 'getInstance')) {
            $filter = PHP_CodeCoverage_Filter::getInstance();
        } else {
            $filter = new PHP_CodeCoverage_Filter();
        }
        $filter->addDirectoryToBlacklist(__DIR__,                 '.php', '', 'PHPUNIT', false);
        $filter->addDirectoryToBlacklist(__DIR__.'/templates',    '.php', '', 'PHPUNIT', false);
        $filter->addDirectoryToBlacklist($this->config['tpldir'], '.php', '', 'PHPUNIT', false);
        $filter->addFileToBlacklist(__FILE__,                                 'PHPUNIT', false);
        $filter->addFileToBlacklist($_SERVER['SCRIPT_FILENAME'],              'PHPUNIT', false);
    }

    /**
     * Execute the test.
     *
     * @return  void
     */
    public function run()
    {
        PHPUnit_Html_TestRunner::run(null, $this->config);
    }

    /**
     * Serve a requested resource.
     *
     * @param   array       $config     configuration options
     * @return  void
     */
    private function handleResourceRequest($config = array())
    {
        if (isset($_SERVER['PATH_INFO'])) {
            // Resource request
            $path = $_SERVER['PATH_INFO'];
            if (strncasecmp($path, '/template/', 10) === 0) {
                // Return a template resource
                $root = __DIR__.'/templates/'.(isset($config['template']) ? $config['template'] : 'default').'/';
            }
            $file = $root.substr($path, 10);
            if (file_exists($file)) {
                switch (pathinfo($file, PATHINFO_EXTENSION )) {
                    case 'css': $mime = 'text/css'; break;
                    case 'js':  $mime = 'text/js'; break;
                    case 'gif': $mime = 'image/gif'; break;
                    case 'png': $mime = 'image/png'; break;
                    case 'jpeg':
                    case 'jpg': $mime = 'image/jpg'; break;
                    default:
                        $finfo = finfo_open();
                        $mime = finfo_file($finfo, $file, FILEINFO_MIME);
                        finfo_close($finfo);
                        break;
                }
                $size = filesize($file);
                $time = filemtime($file);
                header('HTTP/1.1 200 OK');
                header('Content-Type: '.$mime);
                header('Content-Length: '.$size);
                header('Etag: '.md5("{$size}-{$time}"));
                header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $time));
                readfile($file);
            } else {
                header('HTTP/1.0 404 Not Found');
            }

            exit;
        }
    }
}
