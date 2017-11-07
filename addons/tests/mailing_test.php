<?
declare(strict_types=1);
define('ABSPATH', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');
require_once(__DIR__. '/../mockClasses/wordpress.php');
require_once(__DIR__. '/../mockClasses/mailingsHelpers.php');
function get_option(){}

function add_action($one, $two){}

function getAll(){
  return array("location" => "blah");
}

function wp_remote_post($one, $two){
   return array(
            'header'=> getAll(),
            'response' =>
              array(
                'code' => '')
            );
}

define('ARRAY_A', 'Array_A');

use PHPUnit\Framework\TestCase;

final class RequestMethodTest extends TestCase
  {
    public function testGetDistributionsGetOptionsMocked()
    {
      global $wp;
      global $mh;

      $wp = $this->createMock(WordPress::class);
      $wp ->expects($this->once())
          ->method('getOptions')
          ->willReturn(0);

      $mh = $this->createMock(MailingsHelpers::class);
      $mh ->method('loopActiveCampaigns')
          ->willReturn(array());

      $mailingsFunc = new Mailings();

      $result = $mailingsFunc->get_distributions();
      $this->assertEquals(array('campaigns' => array(), 'overall' => array()), $result);
    }

    public function testGetDistributions()
    {
      global $wp;
      global $wpdb;
      global $mh;

      $object =  (object) array(
                          'posts' => (object)
                            array((object)
                              array(
                                'ID'=> 0,
                                'post_title'=>'')
                              )
                          );

      $wp = $this->createMock(WordPress::class);
      $wp ->expects($this->once())
          ->method('getOptions')
          ->willReturn(1);

      $wp ->expects($this->once())
          ->method('getResults')
          ->willReturn(array(''=>
                        array('campaign_id'=>'')
                        )
                      );
  //this isn't working
      $wp ->expects($this->once())
          ->method('wordPressQuery')
          ->willReturn($object);

      $mh = $this->createMock(MailingsHelpers::class);
      $mh ->method('loopActiveCampaigns')
          ->willReturn(array());

      $mailingsFunc = new Mailings();
      $mailingsFunc->get_distributions();
   }

    public function testloopActiveCampaigns()
    {
      global $wpdb;
      $param =  (object) array(
                          'posts' => (object)
                            array((object)
                              array(
                                'ID'=> 2,
                                'post_title'=>'hello world')
                            )
                      );
      $wp = $this->createMock(WordPress::class);
      $wp ->expects($this->once())
          ->method('getFields')
          ->willReturn(array(
                        'subjects' =>
                          array(
                            'one',
                            'two',
                            'three')
                          )
                        );

      $mh = new MailingsHelpers($wpdb);
      $result = $mh->loopActiveCampaigns($param, $wp);

      $this->assertSame(array(2 =>
                          array(
                           'conversions' => 0,
                           'fields' => array(
                                        'subjects' =>
                                          array(
                                            'one',
                                            'two',
                                            'three')
                                          ),
                           'id' => 2,
                           'losses' => 0,
                           'sent' => 0,
                           'subjects' => array(
                                          0 => array(
                                                'conversions' => 0,
                                                'losses' => 0,
                                                'sent' => 0),
                                          1 => array(
                                                'conversions' => 0,
                                                'losses' => 0,
                                                'sent' => 0),
                                          2 => array(
                                                'conversions' => 0,
                                                'losses' => 0,
                                                'sent' => 0)),
                           'title' => 'hello world',
                           'valid' => true,
                          )
                        ), $result
                      );
    }
    public function testGet_distributionsWPMailingStatsNoCampaignId()
    {
      $mh = new MailingsHelpers();
      $result = $mh->mailingStats(array(
                                    array(
                                     'campaign_id'=>0)),
                                        array(
                                          'noId' => null), '');
      $this->assertSame(array(
                        'campaign_result'=> array(
                                            'noId' => null
                                            ),'overall_result' =>''), $result);
    }

    public function testGet_distributionsWPMailingStatsCampaignId()
    {
      $mailings = array(
                    array(
                      'campaign_id'=> 0,
                      'conversions' => 10,
                      'losses' => 10,
                      'sent' => 10,
                      'variation_subject' => 'hello')
                    );
      $campaigns = array(
                    0 => array(
                      'conversions' => 0,
                      'losses' => 0,
                      'sent' => 0,
                      'subjects' => array(

                        )
                      )
                    );
      $overall = array(
                  'conversions' => 0,
                  'losses' => 0,
                  'sent' => 0,
                );
      $mh = new MailingsHelpers();
      $result = $mh->mailingStats($mailings, $campaigns, $overall);
      $this->assertSame(array(
                        'campaign_result' => array(
                         0 => array(
                              'conversions' => 10,
                              'losses' => 10,
                              'sent' => 10,
                              'subjects' => array(
                                  'hello' => array(
                                    'conversions' => 10,
                                    'losses' => 10,
                                    'sent' => 10)
                                  )
                                )
                              ),
                              'overall_result' => array(
                                'conversions'=> 10,
                                'losses' => 10,
                                'sent' => 10)
                              ), $result);
    }

    public function testOverallRateCalcuationWithBoostFunction()
    {
      $overall = array(
                  'conversions'=> 10,
                  'losses' => 10,
                  'sent' => 10
                );
      $boost = 500;
      $mh = new MailingsHelpers();
      $result = $mh->overall_rate_calculation_with_boost($overall, $boost);
      $this->assertSame(array(
                        'boost_value' => 500,
                        'overall_value' => array(
                          'conversions' => 10,
                          'losses' => 10,
                          'sent' => 10,
                          'boost' => 500,
                          'rate' => 0.9803921568627451)
                        ), $result);
    }

    public function testOverallRateCalcuationWithBoostFunctionNegativeRate()
    {
      $overall = array(
                  'conversions'=> -501,
                  'losses' => 0,
                  'sent' => 0
                  );
      $boost = 500;
      $mh = new MailingsHelpers();
      $result = $mh->overall_rate_calculation_with_boost($overall, $boost);
      $this->assertSame(array(
                        'boost_value' => 500,
                        'overall_value' => array(
                        'conversions' => -501,
                        'losses' => 0,
                        'sent' => 0,
                        'boost' => 500,
                        'rate' => 0)
                      ), $result);
    }
//do next
    public function testCalculateShares()
    {
      $campaigns = array(0 => array(
                               'conversions' => 10,
                               'losses' => 10,
                               'sent' => 10,
                               'fields' => array(
                                 'subjects' => array(
                                   'hello' => array(
                                     'enabled' => false,
                                     'subject' => 'two'
                                    )
                                  )
                                ),
                                'subjects' => array(
                                'hello' => array(
                                  'conversions' => 10,
                                  'losses' => 10,
                                  'sent' => 10
                                )
                              )
                            )
                          );
      $overall = array('rate' => 10);
      $boost = 500;
      $mh = new MailingsHelpers();
      $result = $mh->calculate_shares($campaigns, $overall, $boost);

      $this->assertSame(array('campaigns_values' => array (
                                    0 => array (
                                        'conversions' => 10,
                                        'losses' => 10,
                                        'sent' => 10,
                                        'fields' => array (
                                            'subjects' => array (
                                                'hello' => array (
                                                    'enabled' => false,
                                                    'subject' => 'two',
                                                )
                                            )
                                        ),
                                        'subjects' => array (

                                            'hello' => array (
                                                'conversions' => 10,
                                                'losses' => 10,
                                                'sent' => 10,
                                                'title' => 'two',
                                                'rate' => 0
                                            )
                                        ),
                                        'valid' => false
                                      )
                                  )
                              ), $result);
    }

    public function testGetMailingsStatsFromAkQueryMethod()
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->expects($this->exactly(3))
         ->method('query')
         ->willReturn(array(
                      'data' => array(
                        'conversions' => '',
                        'losses' => '',
                        'sent' => '')
                      )
                    );
      $mailingsTest = new Mailings($ak);
      $result = $mailingsTest->get_mailing_stats_from_ak('');
    }

    public function testGetMailingsStatsFromAkReturnValue()
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->expects($this->exactly(3))
         ->method('query')
         ->willReturn(array(
                      'data' => array(
                        'conversions' => 'one',
                        'losses' => 'two',
                        'sent' => 'three')
                      )
                    );
      $mailingsTest = new Mailings($ak);
      $result = $mailingsTest->get_mailing_stats_from_ak('');
      $this->assertEquals(array(
                          'conversions' => 'one',
                          'losses' => 'two',
                          'sent' => 'three'), $result);
    }
//Testing mailings requestMail function is returning value from ActionKit request method
    public function testActionkitAndMailingsRequestMethodReturnValue(): void
    {
      global $ak;
      $ak = $this->createMock(ActionKit::class);
      $ak->method('request')
         ->willReturn('');

     $mailings = new Mailings();
     $result = $mailings->requestMail(array(
                                        'from_line' => '',
                                        'subject' => '',
                                        'subscribers' => '',
                                        'limit' => '')
                                        , '');
     $this->assertEquals( $ak->request(''), $result);
    }
// set up observer on ActionKit class request method to be called once from mailings class requestMailings method with
//the same parameters
    public function testActionkitAndMailingsRequestMethodParameters()
    {
      $params = array(
                'from_line'=>'one',
                'subject'=>'two',
                'subscribers' =>'three',
                'limit'=>'four');
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
                             )
                           )
                         );
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