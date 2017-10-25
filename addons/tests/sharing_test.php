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

class wpdb {
  function prepare(){}
  function get_results(){
    return array();
  }
}

class wpdbWithValues {
  function prepare(){}
  function get_results(){
    return array('type' => 4, 'variant' => 4, 'views' => 4, 'convverstions' => 4);
  }
}

// $results =
use PHPUnit\Framework\TestCase;

final class SharingTest extends TestCase
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
    // public function testGet_sharing_stats_from_akFieldsSet(): void
    // {
    //   $sharing= $this->createMock(Sharing::class);
    //   $sharing->method('get_sharing_stats_from_ak');
    //   $sharing->$fields = array('share_descriptions' => '');
    //
    //   $statTest = new Sharing($sharing);
    //   $returnValue = $statTest->get_sharing_stats_from_ak('');
    //   print_r($returnValue);
    //   $this->assertEquals($returnValue, array('sd'=>array(), 'si'=>array(), 'st'=>array()));
    // }
    public function testGetSharingPerformanceFunction(): void
    {
      global $wpdb;
      $wpdb = new wpdb;

      $params = array('post_id'=>'');

      $sharingPerformanceTest = new Sharing();
      $result = $sharingPerformanceTest->get_sharing_performance($params);
      $this->assertEquals( array(
          'share_descriptions' => array(),
          'share_images' => array(),
          'share_titles' => array(),
      ), $result);
    }
    public function testGetSharingPerformanceFunctionWithResultsArray(): void
    {
      global $wpdb;
      $wpdb = new wpdbWithValues;

      $params = array('post_id'=>'');

      $sharingPerformanceTest = new Sharing();
      $result = $sharingPerformanceTest->get_sharing_performance($params);
      $this->assertEquals( array(
          'share_descriptions' => array(),
          'share_images' => array(),
          'share_titles' => array(),
          '' => array(''=>array('views'=> 0, 'conversions' => 0))
      ), $result);
    }
}
