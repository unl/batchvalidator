<?php
function autoload($class)
{
    $class = str_replace('_', '/', $class);
    if (file_exists(dirname(__FILE__) . '/../src/' . $class . '.php')) {
        include dirname(__FILE__) . '/../src/' . $class . '.php';
    }
}

spl_autoload_register("autoload");

class ValidationLogger extends Spider_LoggerAbstract
{
    public $validator;
    
    public $page;
    
    function __construct($validator, $page)
    {
        $this->validator = $validator;
        $this->page      = $page;
    }
    
    function log($uri, DOMXPath $xpath)
    {
        
        $this->page->maincontentarea .= '<h1>'.htmlentities($uri).'</h1>';
        
//        $r = $this->validator->validate($uri);
//        $this->displayValidationDetails($r);
    }
    
    function displayValidationDetails($r)
    {
        if ($r->isValid()) {
            $this->page->maincontentarea .= '<h2 class="results" class="valid">This page is Valid!</h2>';
            $this->page->maincontentarea .=  'Passed validation, '.count($r->warnings).' Warnings';
        } else {
            $this->page->maincontentarea .= '<h2 class="results" class="invalid">This page is <strong>not</strong> Valid!</h2>';
            $this->page->maincontentarea .=  'Failed validation, '.count($r->errors).' Errors';
        }
        $this->page->maincontentarea .= '<div class="results_container">';
        if (count($r->warnings)) {
            $this->page->maincontentarea .= '<h3 class="preparse_warnings">Important Warnings</h3>
            <p>The validator has found the following problem(s) prior to validation, 
            which should be addressed in priority:</p>';
            $this->page->maincontentarea .= '<ol class="warnings">';
            addMessageSection($r->warnings, 'warn', $this->page);
            $this->page->maincontentarea .= '</ol>';
        }
        if (count($r->errors)) {
            $this->page->maincontentarea .= '
            <h3 class="invalid">Validation Output: '.count($r->errors).' Errors</h3>';
            $this->page->maincontentarea .= '<ol class="error_loop">';
            addMessageSection($r->errors, 'err', $this->page);
            $this->page->maincontentarea .= '</ol>';
        }
        $this->page->maincontentarea .= '</div>';
    }
}

class FileExtensionFilter extends Spider_UriFilterInterface
{
    function accept()
    {
        $path_parts = pathinfo($this->current());
        if (!isset($path_parts['extension'])
            || $path_parts['extension'] == 'html'
            || $path_parts['extension'] == 'php'
            || $path_parts['extension'] == 'shtml'
            || $path_parts['extension'] == 'asp'
            || $path_parts['extension'] == 'aspx'
            || $path_parts['extension'] == 'jsp') {
            return true;
        }
        return false;
    }
}

/**
 * Example file demonstrating a user interface for validating pages.
 *  
 * PHP versions 5
 * 
 * @category Services
 * @package  Services_W3C_HTMLValidator
 * @author   Brett Bieber <brett.bieber@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  CVS: $id$
 * @link     http://pear.php.net/package/Services_W3C_HTMLValidator
 * @since    File available since Release 0.2.0
 */

error_reporting(E_ALL);
ini_set('display_errors', true);
require_once 'Services/W3C/HTMLValidator.php';
require_once 'UNL/Templates.php';

UNL_Templates::$options['version'] = 3;

const VALIDATOR_URI = 'http://validator.unl.edu/check';

$v = new Services_W3C_HTMLValidator();
$v->validator_uri = VALIDATOR_URI;

$p = UNL_Templates::factory('Document');
$p->titlegraphic = '<h1>UNL W3C Validator Instance</h1><h2>Hey validator, rock n roll. Rock on.</h2>';
$p->head .= '<style type="text/css" media="all">@import "'.substr($v->validator_uri,0,-5).'/style/results.css";</style>';
$p->navlinks = '<ul><li><a href="http://www.unl.edu/wdn/">WDN</a></li></ul>';
$p->maincontentarea = '';

$uri = '';

if (isset($_GET['uri'])) {
    $uri = htmlentities($_GET['uri'], ENT_QUOTES);
}

$p->maincontentarea .= '<form id="form" method="get" action="">
  <table class="header">
    <tbody>
    <tr>
      <th><label title="Address of Page to Validate" for="uri">Address</label>:</th>
      <td colspan="2"><input id="uri" name="uri" value="'.$uri.'" size="50" type="text" /></td>
    </tr>
  </tbody></table>
  <fieldset id="revalidate_opts">
        <div id="revalidate_button" class="submit_button"><input value="Revalidate" title="Validate this document again" type="submit" /></div>
  </fieldset>
</form>';

function addMessageSection($messages, $mtype, &$p)
{
    foreach ($messages as $message) {
        addMessage($message, $mtype, $p);
    }
}

function addMessage($message, $mtype, &$p)
{
    $p->maincontentarea .= '
    <li class="msg_'.$mtype.'">
    <span class="err_type">';
    if ($mtype == 'err') {
        $p->maincontentarea .= '<img src="'.substr(VALIDATOR_URI, 0, -5).'/images/info_icons/error.png" alt="Error" title="Error" />';
    } else {
        $p->maincontentarea .= '<img src="'.substr(VALIDATOR_URI, 0, -5).'/images/info_icons/warning.png" alt="Warning" title="Warning" />';
    }
    $p->maincontentarea .= '</span>
       <em>Line '.$message->line.', Column '.$message->col.'</em>:
       <span class="msg">'.htmlentities($message->message).'</span>.<pre><code class="input">'.$message->source.'</code></pre>';
    $p->maincontentarea .= $message->explanation;
    $p->maincontentarea .= '
     </li>';
}

if (isset($_GET['uri'])) {
    $logger           = new ValidationLogger($v, $p);
    $downloader       = new Spider_Downloader();
    $parser           = new Spider_Parser();
    $spider           = new Spider($downloader, $parser);
    
    $spider->addLogger($logger);
    $spider->addUriFilter('Spider_AnchorFilter');
    $spider->addUriFilter('Spider_MailtoFilter');
    $spider->addUriFilter('FileExtensionFilter');
    
    $spider->spider($_GET['uri']);
}

echo $p->toHtml();

?>
