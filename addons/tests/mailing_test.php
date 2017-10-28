<?
declare(strict_types=1);
define('ABSPATH', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');

function get_option($one){}

function add_action($one, $two){}

function getAll(){
  return array("location" => "blah");
}

function wp_remote_post($one, $two){
   return array('header'=> getAll(), 'response' => array('code' => ''));
}

use PHPUnit\Framework\TestCase;

final class RequestMethodTest extends TestCase
  {
    public function testGetMailingsStatsFromAkQueryMethod()
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->expects($this->exactly(3))
          ->method('query')
          ->willReturn(array('data' => array('conversions' => '', 'losses' => '', 'sent' => '')));

      $mailingsTest = new Mailings($ak);
      $result = $mailingsTest->get_mailing_stats_from_ak('');
    }
    public function testGetMailingsStatsFromAkReturnValue()
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->expects($this->exactly(3))
          ->method('query')
          ->willReturn(array('data' => array('conversions' => 'one', 'losses' => 'two', 'sent' => 'three')));

      $mailingsTest = new Mailings($ak);
      $result = $mailingsTest->get_mailing_stats_from_ak('');
      $this->assertEquals(array('conversions' => 'one', 'losses' => 'two', 'sent' => 'three'), $result);
    }
//Testing mailings requestMail function is returning value from ActionKit request method
    public function testActionkitAndMailingsRequestMethodReturnValue(): void
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->method('request')
         ->willReturn('');

     $mailings = new Mailings();
     $result = $mailings->requestMail(array('from_line' => '', 'subject' => '', 'subscribers' => '', 'limit' => ''), '');
     $this->assertEquals( $ak->request(''), $result);
    }
// set up observer on ActionKit class request method to be called once from mailings class requestMailings method with
//the same parameters
    public function testActionkitAndMailingsRequestMethodParameters()
    {
      $params = array('from_line'=>'one', 'subject'=>'two', 'subscribers' =>'three', 'limit'=>'four');
      $html = '';

      global $ak;
      $ak = $this->getMockBuilder(ActionKit::class)
                       ->setMethods(['request'])
                       ->getMock();

      $ak->expects($this->once())
               ->method('request')
               ->with($this->equalTo(array(
                   'path' => 'mailer',
                   'method' => 'post',
                   'data' => array(
                       'fromline' => "/rest/v1/fromline/one/",
                       'subjects' => array('two'),
                       'notes' => 'Generated by VictoryKit',
                       'emailwrapper' => 27,
                       'includes' => array(
                           'lists' => array(26),
                           'users' => 'three',
                       ),
                       'limit' => 'four',
                       'tags' => array('victorykit'),
                       'html' => $html,
                       'sort_by' => 'random',
                   ),
               )));

      $mailings = new Mailings($ak);
      $mailings->requestMail($params, $html);
    }
//testing render function in mailings class
    public function testrenderFunctionWrapNotSet()
    {
      $params = array('body'=>'<span>one</span>', 'petition_headline'=>'<span>two</span>', 'salutation'=>'<span>three</span>', 'url' =>'<span>four</span>');

      $mailingsTest = new Mailings();
      $result = $mailingsTest->render($params);

      $this->assertContains('<span>one</span>', $result);
      $this->assertContains('<span>two</span>', $result);
      $this->assertContains('<span>three</span>', $result);
      $this->assertContains('<span>four</span>', $result);
    }
    public function testrenderFunctionWrapSet()
    {
      $params = array('body'=>'<span>one</span>', 'petition_headline'=>'<span>two</span>', 'salutation'=>'<span>three</span>', 'url' =>'<span>four</span>', 'wrap' =>'');

      $mailingsTest = new Mailings();
      $result = $mailingsTest->render($params);

      $this->assertContains('<!DOCTYPE html>', $result);
    }
};