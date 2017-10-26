<?
declare(strict_types=1);
define('ABSPATH', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');

function get_option(){}
function add_action(){}
function update_option(){}


use PHPUnit\Framework\TestCase;

final class RequestMethodTest extends TestCase
  {
    public function testVkMailingsUpdateSubscribedCallsActionkitQuery(): void
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->expects($this->once())
         ->method('query');

      vk_mailings_update_subscribed_users_count_action($ak);
    }

    public function testVkMailingsUpdateSubscribedReturnValueZero(): void
     {
       global $ak;
       $ak = $this->createMock(ActionKit::class);
       $ak->method('query')
          ->willReturn(array('success' => null));

      $result = vk_mailings_update_subscribed_users_count_action($ak);
      $this->assertEquals( 0, $result);
     }

     public function testVkMailingsUpdateSubscribedReturnValueFour(): void
      {
        global $ak;
        $ak = $this->createMock(ActionKit::class);
        $ak->method('query')
           ->willReturn(array('success' => true, 'data' => array('user_count' => 4)));

       $result = vk_mailings_update_subscribed_users_count_action($ak);
       $this->assertEquals( 4, $result);
      }
  }