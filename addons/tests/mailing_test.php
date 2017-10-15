<?
declare(strict_types=1);
define('ABSPATH', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');

function get_option($one){
}

function add_action($one, $two){
}

function wp_remote_post(){
  return array('header' => array(), 'response'=> array('code' => ''));
  // return array('response'=> array('code' =>''));
}

function getAll(){
  return array("location" => "blah");
}

use PHPUnit\Framework\TestCase;

final class RequestMethodTest extends TestCase
  {
    public function testRequestMethodReturnsHeaders(): void
    {
      $stub = $this->createMock(Actionkit::class);
      $stub->method('request')
           ->willReturn(array('header' => getAll()));

     $mailings = new Mailings();
     $result = $mailings->requestMail(array('subscribers'=>'', 'from_line'=>'', 'subject'=>'', 'limit'=>''), "");

     $this->assertEquals( $stub->request(array()), $result);
    }
  }
  //result = array('response'=> false, 'error' => false);