<?
define('ABSPATH', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../sharing.php');

function add_action(){}

function get_post_meta(){}

function get_option(){}

function queryResponse(){
   return array('data' => array());
}

function get_fields(){}

use PHPUnit\Framework\TestCase;

final class RequestMethodTest extends TestCase
  {
    public function testSharingQueryFunction(): void
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->method('query')
         ->willReturn('');

      $statTest = new Sharing();
      $result = $statTest->queryResponse($ak, "");
      $this->assertEquals( $ak->query("", true), $result);
    }

    public function testSharingQueryFunctionParameters(): void
    {
      global $ak;
      $params = "
          SELECT
              COUNT(tbl.id) AS amount,
              tbl.source
          FROM
              core_action AS tbl
          WHERE
              tbl.page_id = 4 AND
              tbl.subscribed_user = 1 AND
              tbl.source LIKE 'vk&%'
          GROUP BY
              tbl.source
      ";
      $ak = $this->getMockBuilder(ActionKit::class)
                 ->setMethods(['query'])
                 ->getMock();

      $ak->expects($this->once())
              ->method('query')
              ->with($this->equalTo($params));

      $statTest = new Sharing($ak);
      $statTest->queryResponse($params);
    }
    public function testGet_sharing_stats_from_ak(): void
    {
      $sharing= $this->createMock(Sharing::class);
      $sharing->method('get_sharing_stats_from_ak');

      $statTest = new Sharing($sharing);
      $returnValue = $statTest->get_sharing_stats_from_ak('');
      $this->assertEquals($returnValue, array('sd'=>array(), 'si'=>array(), 'st'=>array()));
    }
  }
