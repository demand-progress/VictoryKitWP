<?
declare(strict_types=1);
define('ABSPATH', 1);
define('ARRAY_A', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');
require_once(__DIR__. '/twoCampaigns.php');
require_once(__DIR__. '/singleCampaign.php');
require_once(__DIR__. '/twoSubjectCampaign.php');
require_once(__DIR__. '/../mockClasses/mailingsHelpers.php');

function get_option(){
    return 25;
}
function add_action(){}
function get_permalink(){}

class WP_Query {
    public $posts = array(); 
}

class wpdb {
    function get_results(){return array();}

    function get_col(){return array();}
    
    function query(){}
};

use PHPUnit\Framework\TestCase;

final class mailingsClass extends TestCase
    {   
        public function test_vk_mailings_create_new_mailings_action_two_campaigns(): void 
        {   
            global $TwoCampaigns;

            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);
            $mhMock= $this->createMock(MailingsHelpers::class);
        
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn($TwoCampaigns);
                    
            $mhMock->expects($this->exactly(2))
                ->method('get_fresh_subscribers_for_campaign')
                ->willReturn(array( 0 => 6630470, 1 => 6630472,));
            
            $mhMock->expects($this->once())
                ->method('getoption')
                ->willReturn(4);

            $mhMock->expects($this->exactly(3))
                ->method('send');

            $wpdb_mock->expects($this->exactly(1))
                ->method('query');

            vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock, $mhMock);
        }

        public function test_vk_mailings_create_new_mailings_action_one_campaigns(): void 
        {   
            global $singleCampaign;

            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);
            $mhMock = $this->createMock(MailingsHelpers::class);
        
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn($singleCampaign);
                
            $mhMock->expects($this->exactly(1))
                ->method('get_fresh_subscribers_for_campaign')
                ->willReturn(array( 0 => 6630470, 1 => 6630472,));
            
            $mhMock->expects($this->once())
                ->method('getoption')
                ->willReturn(4);

            $mhMock->expects($this->exactly(2))
                ->method('send');

            $wpdb_mock->expects($this->exactly(1))
                ->method('query');

            vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock, $mhMock); 
        }

        public function test_get_distribution_two_subject_campaign(): void 
        {   
            global $twoSubjectCampaign;

            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);
            $mhMock = $this->createMock(MailingsHelpers::class);

            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn($twoSubjectCampaign);
                   
            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->willReturn(array( 0 => 6630470, 1 => 6630472,));
            
            $mhMock->expects($this->once())
                ->method('getoption')
                ->willReturn(4);

            $mhMock->expects($this->once())
                ->method('send')
                ->willReturn(array ( 'ak_mailing_id' => 1));
            
            $wpdb_mock->expects($this->once())
                ->method('query');

            vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock, $mhMock);  
        }

        public function test_mailingsHelpers_wp_query_posts_method(): void 
        {
            $mailingsMock = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500; 
            $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);

            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                        ->setMethods(['wp_query_posts'])
                        ->getMock();

            $mhMock->expects($this->once())
                        ->method('wp_query_posts')
                        ->will($this->returnValue($postObject));

            $mailingsMock->get_distributions($wpdb, $mhMock, $boost_test_value); 
        }

        public function test_get_fields(): void 
        {
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500; 
            $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);

            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                         ->setMethods(['wp_query_posts', 'getFields'])
                         ->getMock();
            
            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('getFields');
           
            $subject = new Mailings();
    
            $subject->get_distributions($wpdb, $mhMock, $boost_test_value);
        }

        public function test_mailingsHelpers_setUpCampaigns(): void 
        {
            $wpdb = new wpdb();
            $boost_test_value = 500; 
            // $postObject = new stdClass();
            $postObject = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            $campaigns = array( 0 => array( 'fields'=> '', 'campaign_id' => '', 'subjects' => array()));

            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                         ->setMethods(['wp_query_posts', 'setUpCampaigns'])
                         ->getMock();
            
            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $subject = new Mailings();
    
            $subject->get_distributions($wpdb, $mhMock, $boost_test_value);
        }

        public function test_mailingsHelpers_get_mailings_results_wpdb(): void 
        {
            $wpdb = new wpdb();
            $boost_test_value = 500; 
            // $postObject = new stdClass();
            $postObject = array( 0 => array( 'campaign_id' => '1'));

            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                         ->setMethods(['get_mailings_results_wpdb'])
                         ->getMock();
            
            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($postObject));
           
            $subject = new Mailings();
    
            $subject->get_distributions($wpdb, $mhMock, $boost_test_value);
        }

        public function test_mailings_distribution_post_method(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500;
      
            $postObject->posts = array ( 0 => (object)(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), );
                
           //** must update return value from campaigns
         
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, ),       1 => array ( 'subject' => "Block Trump's attacks on immigrants", 'enabled' => true, ),)
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => "No wall testing",
                    "valid" => true
                )
            );
            // $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            
            $mailings_stats = array(0 => [
                                            'campaign_id' => 263, 
                                            'conversions' => 0, 
                                            'losses' => 0,
                                            'sent' => 0,
                                            'variation_subject' => 0
                                        ]);
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($mailings_stats));

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479']));
            
            $this->assertEquals(array(
                'campaigns' => Array(
                    0 => Array(
                        'conversions' => 0,
                        'fields' => Array(
                            'subjects' => Array(
                                0 => Array(
                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                    'enabled' => true
                                ),
                                1 => Array(
                                    'subject' => "Block Trump's attacks on immigrants",
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 263,
                        'losses' => 0,
                        'sent' => 0,
                        'subjects' => Array(
                            0 => Array(
                                'conversions' => 0,
                                'losses' => 0,
                                'sent' => 0,
                                'title' => 'Tell Senate: No attacks on immigrants',
                                'rate' => 1,
                                'share' => 1
                            )
                        ),
                        'title' => 'No wall testing',
                        'valid' => true,
                        'rate' => 1,
                        'share' => 1,
                        'limit' => 4.0
                    )
                ),
                'overall' => Array(
                    'conversions' => 0,
                    'losses' => 0,
                    'sent' => 0,
                    'boost' => 500,
                    'rate' => 1
                )
            ), $mailings->get_distributions($wpdb, $mhMock, $boost_test_value));
        }

        public function test_get_distributions_one_campaign_method(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500;
            $postObject->posts = array ();
                
           //** must update return value from campaigns
         
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, ),       1 => array ( 'subject' => "Block Trump's attacks on immigrants", 'enabled' => true, ),)
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => "No wall testing",
                    "valid" => true
                )
            );
            // $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            
            $mailings_stats =  array ( 
                    0 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '0', 
                        'conversions' => '1', 
                        'losses' => '0', 
                        'sent' => '2', ), 
                    1 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '1', 
                        'conversions' => '1', 
                        'losses' => '0', 
                        'sent' => '2', )
                    );
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($mailings_stats));

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479']));
            
            $this->assertEquals(array(
                'campaigns' => Array(
                    0 => Array(
                        'conversions' => 2,
                        'fields' => Array(
                            'subjects' => Array(
                                0 => Array(
                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                    'enabled' => true
                                ),
                                1 => Array(
                                    'subject' => "Block Trump's attacks on immigrants",
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 263,
                        'losses' => 0,
                        'sent' => 4,
                        'subjects' => Array(
                            0 => Array(
                                'conversions' => 1,
                                'losses' => 0,
                                'sent' => 2,
                                'title' => 'Tell Senate: No attacks on immigrants',
                                'rate' => 0.994055523936002,
                                'share' => 0.5
                            ),
                            1 => Array(
                                'conversions' => 1,
                                'losses' => 0,
                                'sent' => 2,
                                'title' => "Block Trump's attacks on immigrants",
                                'rate' => 0.994055523936002,
                                'share' => 0.5
                            )
                        ),
                        'title' => 'No wall testing',
                        'valid' => true,
                        'rate' => 0.9920949861426052,
                        'share' => 1.0,
                        'limit' => 4.0
                    )
                ),
                'overall' => Array(
                    'conversions' => 2,
                    'losses' => 0,
                    'sent' => 4,
                    'boost' => 500,
                    'rate' => 0.996031746031746
                )
            ), $mailings->get_distributions($wpdb, $mhMock, $boost_test_value)); 
        }
        public function test_get_distributions_one_campaign_one_greater_conversions_method(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500;
      
            $postObject->posts = array ();
                
           //** must update return value from campaigns
         
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, ),       1 => array ( 'subject' => "Block Trump's attacks on immigrants", 'enabled' => true, ),)
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => "No wall testing",
                    "valid" => true
                )
            );
            // $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            
            $mailings_stats =  array ( 
                    0 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '0', 
                        'conversions' => '2', 
                        'losses' => '0', 
                        'sent' => '2', ), 
                    1 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '1', 
                        'conversions' => '0', 
                        'losses' => '0', 
                        'sent' => '2', )
                    );
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($mailings_stats));

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479']));
            
            $this->assertEquals(array(
                'campaigns' => Array(
                    0 => Array(
                        'conversions' => 2,
                        'fields' => Array(
                            'subjects' => Array(
                                0 => Array(
                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                    'enabled' => true
                                ),
                                1 => Array(
                                    'subject' => "Block Trump's attacks on immigrants",
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 263,
                        'losses' => 0,
                        'sent' => 4,
                        'subjects' => Array(
                            0 => Array(
                                'conversions' => 2,
                                'losses' => 0,
                                'sent' => 2,
                                'title' => 'Tell Senate: No attacks on immigrants',
                                'rate' => 0.996047555808512,
                                'share' => 0.501001972135632
                            ),
                            1 => Array(
                                'conversions' => 0,
                                'losses' => 0,
                                'sent' => 2,
                                'title' => "Block Trump's attacks on immigrants",
                                'rate' => 0.9920634920634921,
                                'share' => 0.498998027864368
                            )
                        ),
                        'title' => 'No wall testing',
                        'valid' => true,
                        'rate' => 0.9920949861426052,
                        'share' => 1.0,
                        'limit' => 4.0
                    )
                ),
                'overall' => Array(
                    'conversions' => 2,
                    'losses' => 0,
                    'sent' => 4,
                    'boost' => 500,
                    'rate' => 0.996031746031746
                )
            ), $mailings->get_distributions($wpdb, $mhMock, $boost_test_value)); 
        }

        public function test_send_function_with_one_campaign_one_subject_greater_conversion(): void 
        {
            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);
            $mhMock = $this->createMock(MailingsHelpers::class);

            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn(Array(
                    'campaigns' => Array(
                        0 => Array(
                            'conversions' => 2,
                            'fields' => Array(
                                'subjects' => Array(
                                    0 => Array(
                                        'subject' => 'Tell Senate: No attacks on immigrants',
                                        'enabled' => true
                                    ),
                                    1 => Array(
                                        'subject' => "Block Trump's attacks on immigrants",
                                        'enabled' => true
                                    )
                                    ),
                                    'from_line' => '41',
                                    'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                                    'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                                    'salutation' => '{{ user.first_name|default:"Hi" }},', 
 
                            ),
                            'id' => 263,
                            'losses' => 0,
                            'sent' => 4,
                            'subjects' => Array(
                                0 => Array(
                                    'conversions' => 2,
                                    'losses' => 0,
                                    'sent' => 2,
                                    'title' => 'Tell Senate: No attacks on immigrants',
                                    'rate' => 0.996047555808512,
                                    'share' => 0.501001972135632
                                ),
                                1 => Array(
                                    'conversions' => 0,
                                    'losses' => 0,
                                    'sent' => 2,
                                    'title' => "Block Trump's attacks on immigrants",
                                    'rate' => 0.9920634920634921,
                                    'share' => 0.498998027864368
                                )
                            ),
                            'title' => 'No wall testing',
                            'valid' => true,
                            'rate' => 0.9920949861426052,
                            'share' => 1.0,
                            'limit' => 4.0
                        )
                    ),
                    'overall' => Array(
                        'conversions' => 2,
                        'losses' => 0,
                        'sent' => 4,
                        'boost' => 500,
                        'rate' => 0.996031746031746
                    )
                )
            );

            $mhMock->expects($this->once())
                    ->method('getoption')
                    ->willReturn(3.5714285714285716);
            
            $mhMock->expects($this->once())
                    ->method('get_url')
                    ->willReturn('https://victorykit.local/c/no-wall-testing-2/');
                    

            $mhMock->expects($this->exactly(1))
                    ->method('get_fresh_subscribers_for_campaign')
                    ->willReturn(array( 0 => 6630470, 1 => 6630472, 2 => 6630478, 
                    3 => 6630479));

            $mhMock->expects($this->exactly(2))
                    ->method('send');

            vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock, $mhMock);
        }

          public function test_get_distributions_one_campaign_one_succesful_subject_method(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500;
            $postObject->posts = array ();
                
           //** must update return value from campaigns
         
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 
                                        0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, ),      
                                        1 => array ( 'subject' => "Block Trump's attacks on immigrants", 'enabled' => true, ),
                                    )
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => "No wall testing",
                    "valid" => true
                )
            );
            // $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            
            $mailings_stats =  array ( 
                    0 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '0', 
                        'conversions' => '500', 
                        'losses' => '0', 
                        'sent' => '500', ), 
                    1 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '1', 
                        'conversions' => '0', 
                        'losses' => '0', 
                        'sent' => '500', )
                    );
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($mailings_stats));

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479']));
            
            $this->assertEquals(array(
                'campaigns' => Array(
                    0 => Array(
                        'conversions' => 500,
                        'fields' => Array(
                            'subjects' => Array(
                                0 => Array(
                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                    'enabled' => true
                                ),
                                1 => Array(
                                    'subject' => "Block Trump's attacks on immigrants",
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 263,
                        'losses' => 0,
                        'sent' => 1000,
                        'subjects' => Array(
                            0 => Array(
                                'conversions' => 500,
                                'losses' => 0,
                                'sent' => 500,
                                'title' => 'Tell Senate: No attacks on immigrants',
                                'rate' => 0.8333333333333333,
                                'share' => 0.7142857142857143
                            ),
                            1 => Array(
                                'conversions' => 0,
                                'losses' => 0,
                                'sent' => 500,
                                'title' => "Block Trump's attacks on immigrants",
                                'rate' => 0.3333333333333333,
                                'share' => 0.28571428571428575
                            )
                        ),
                        'title' => 'No wall testing',
                        'valid' => true,
                        'rate' => 0.5555555555555555,
                        'share' => 1.0,
                        'limit' => 4.0
                    )
                ),
                'overall' => Array(
                    'conversions' => 500,
                    'losses' => 0,
                    'sent' => 1000,
                    'boost' => 500,
                    'rate' => 0.6666666666666666
                )
            ), $mailings->get_distributions($wpdb, $mhMock, $boost_test_value)); 
        }
        
          public function test_create_mailings_mailing_no_conversions_only_losses(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500; 
            $postObject->posts = array ();
                
           //** must update return value from campaigns
         
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 
                                        0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, ),      
                                        1 => array ( 'subject' => "Block Trump's attacks on immigrants", 'enabled' => true, ),
                                    )
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => "No wall testing",
                    "valid" => true
                )
            );
            $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            
            $mailings_stats =  array ( 
                    0 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '0', 
                        'conversions' => '250', 
                        'losses' => '250', 
                        'sent' => '500', ), 
                    1 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '1', 
                        'conversions' => '0', 
                        'losses' => '0', 
                        'sent' => '500', )
                    );
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($mailings_stats));

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479']));
            
            $this->assertEquals(array(
                'campaigns' => Array(
                    0 => Array(
                        'conversions' => 250,
                        'fields' => Array(
                            'subjects' => Array(
                                0 => Array(
                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                    'enabled' => true
                                ),
                                1 => Array(
                                    'subject' => "Block Trump's attacks on immigrants",
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 263,
                        'losses' => 250,
                        'sent' => 1000,
                        'subjects' => Array(
                            0 => Array(
                                'conversions' => 250,
                                'losses' => 250,
                                'sent' => 500,
                                'title' => 'Tell Senate: No attacks on immigrants',
                                'rate' => 0.16666666666666666,
                                'share' => 0.5
                            ),
                            1 => Array(
                                'conversions' => 0,
                                'losses' => 0,
                                'sent' => 500,
                                'title' => "Block Trump's attacks on immigrants",
                                'rate' => 0.16666666666666666,
                                'share' => 0.5
                            )
                        ),
                        'title' => 'No wall testing',
                        'valid' => true,
                        'rate' => 0.1111111111111111,
                        'share' => 1.0,
                        'limit' => 4.0
                    )
                ),
                'overall' => Array(
                    'conversions' => 250,
                    'losses' => 250,
                    'sent' => 1000,
                    'boost' => 500,
                    'rate' => 0.3333333333333333
                )
            ), $mailings->get_distributions($wpdb, $mhMock, $boost_test_value));
        }

        public function test_vk_mailings_create_new_mailings_action_return_value(): void 
        {
            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb = new wpdb();
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign', 'send', 'get_url'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479','6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479']));
            
            $mhMock->expects($this->exactly(2))
                ->method('send');

            $mhMock->expects($this->once())
                ->method('get_url')
                ->willReturn('https://victorykit.local/c/no-wall-testing-2/');
            
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->will($this->returnValue(Array(
                            'campaigns' => Array(
                                0 => Array(
                                    'conversions' => 500,
                                    'fields' => Array(
                                        'subjects' => Array(
                                            0 => Array(
                                                'subject' => 'Tell Senate: No attacks on immigrants',
                                                'enabled' => true
                                            ),
                                            1 => Array(
                                                'subject' => "Block Trump's attacks on immigrants",
                                                'enabled' => true
                                            ),
                                        ),
                                        'from_line' => 41,
                                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                                    ),
                                    'id' => 263,
                                    'losses' => 0,
                                    'sent' => 1000,
                                    'subjects' => Array(
                                        0 => Array(
                                            'conversions' => 500,
                                            'losses' => 0,
                                            'sent' => 500,
                                            'title' => 'Tell Senate: No attacks on immigrants',
                                            'rate' => 0.8333333333333333,
                                            'share' => 0.7142857142857143
                                        ),
                                        1 => Array(
                                            'conversions' => 0,
                                            'losses' => 0,
                                            'sent' => 500,
                                            'title' => "Block Trump's attacks on immigrants",
                                            'rate' => 0.3333333333333333,
                                            'share' => 0.28571428571428575
                                        )
                                    ),
                                    'title' => 'No wall testing',
                                    'valid' => true,
                                    'rate' => 0.5555555555555555,
                                    'share' => 1.0,
                                    'limit' => 4.0
                                )
                            ),
                            'overall' => Array(
                                'conversions' => 500,
                                'losses' => 0,
                                'sent' => 1000,
                                'boost' => 500,
                                'rate' => 0.6666666666666666
                            )
                        )
                    )
                );
                $this->assertEquals(Array(
                    0 => Array(
                        'from_line' => 41,
                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                        'campaign_id' => 263,
                        'limit' => 3.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => 'Tell Senate: No attacks on immigrants',
                        'subscribers' => Array (
                            0 => '6630475',
                            1 => '6630477',
                            2 => '6630478'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 0
                    ),
                    1 => Array(
                        'from_line' => 41,
                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                        'campaign_id' => 263,
                        'limit' => 1.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => "Block Trump's attacks on immigrants",
                        'subscribers' => Array (
                            0 => '6630479'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 1
                    )
                ),vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb, $mhMock));
        }
        public function test_vk_mailings_create_new_mailings_both_mailings_failing(): void 
        {
            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb = new wpdb();
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign', 'send', 'get_url'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479','6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479']));
            
            $mhMock->expects($this->exactly(0))
                ->method('send');

            $mhMock->expects($this->once())
                ->method('get_url')
                ->willReturn('https://victorykit.local/c/no-wall-testing-2/');
            
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->will($this->returnValue(Array(
                                'campaigns' => Array(
                                    0 => Array(
                                        'conversions' => 0,
                                        'fields' => Array(
                                            'subjects' => Array(
                                                0 => Array(
                                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                                    'enabled' => true
                                                ),
                                                1 => Array(
                                                    'subject' => "Block Trump's attacks on immigrants",
                                                    'enabled' => true
                                                )
                                            )
                                        ),
                                        'id' => 263,
                                        'losses' => 500,
                                        'sent' => 1000,
                                        'subjects' => Array(
                                            0 => Array(
                                                'conversions' => 0,
                                                'losses' => 0,
                                                'sent' => 500,
                                                'title' => 'Tell Senate: No attacks on immigrants',
                                                'rate' => 0,
                                                'share' => 0
                                            ),
                                            1 => Array(
                                                'conversions' => 0,
                                                'losses' => 500,
                                                'sent' => 500,
                                                'title' => "Block Trump's attacks on immigrants",
                                                'rate' => 0,
                                                'share' => 0
                                            )
                                        ),
                                        'title' => 'No wall testing',
                                        'valid' => true,
                                        'rate' => 0,
                                        'share' => 0,
                                        'limit' => 0.0
                                    )
                                ),
                                'overall' => Array(
                                    'conversions' => 0,
                                    'losses' => 500,
                                    'sent' => 1000,
                                    'boost' => 500,
                                    'rate' => 0
                                )
                            )
                        )
                    );
            $this->assertEquals(Array(),vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb, $mhMock));
        }

        public function test_vk_mailings_create_new_mailings_action_one_subject_50_percent_success_rate(): void 
        {
            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb = new wpdb();
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign', 'send', 'get_url'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479','6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479']));
            
            $mhMock->expects($this->exactly(2))
                ->method('send');

            $mhMock->expects($this->once())
                ->method('get_url')
                ->willReturn('https://victorykit.local/c/no-wall-testing-2/');
            
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->will($this->returnValue(Array(
                            'campaigns' => Array(
                                0 => Array(
                                    'conversions' => 250,
                                    'fields' => Array(
                                        'subjects' => Array(
                                            0 => Array(
                                                'subject' => 'Tell Senate: No attacks on immigrants',
                                                'enabled' => true
                                            ),
                                            1 => Array(
                                                'subject' => "Block Trump's attacks on immigrants",
                                                'enabled' => true
                                            )
                                            ),
                                        'from_line' => 41,
                                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                                    ),
                                    'id' => 263,
                                    'losses' => 250,
                                    'sent' => 1000,
                                    'subjects' => Array(
                                        0 => Array(
                                            'conversions' => 250,
                                            'losses' => 250,
                                            'sent' => 500,
                                            'title' => 'Tell Senate: No attacks on immigrants',
                                            'rate' => 0.16666666666666666,
                                            'share' => 0.5
                                        ),
                                        1 => Array(
                                            'conversions' => 0,
                                            'losses' => 0,
                                            'sent' => 500,
                                            'title' => "Block Trump's attacks on immigrants",
                                            'rate' => 0.16666666666666666,
                                            'share' => 0.5
                                        )
                                    ),
                                    'title' => 'No wall testing',
                                    'valid' => true,
                                    'rate' => 0.1111111111111111,
                                    'share' => 1.0,
                                    'limit' => 4.0
                                )
                            ),
                            'overall' => Array(
                                'conversions' => 250,
                                'losses' => 250,
                                'sent' => 1000,
                                'boost' => 500,
                                'rate' => 0.3333333333333333
                            )
                        )
                    )
                );
                $this->assertEquals(Array(
                    0 => Array(
                        'from_line' => 41,
                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                        'campaign_id' => 263,
                        'limit' => 2.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => 'Tell Senate: No attacks on immigrants',
                        'subscribers' => Array (
                            0 => '6630475',
                            1 => '6630477'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 0
                    ),
                    1 => Array(
                        'from_line' => 41,
                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                        'campaign_id' => 263,
                        'limit' => 2.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => "Block Trump's attacks on immigrants",
                        'subscribers' => Array (
                            0 => '6630478',
                            1 => '6630479'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 1
                    )
                ),vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb, $mhMock));
        }

   
        public function test_create_mailings_mailing_two_campaigns(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $boost_test_value = 500; 
            $postObject->posts = array ();
                
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 
                                        0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, )
                                    )
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => "No wall testing",
                    "valid" => true
                ),
                267 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array ( 
                                        0 => array ( 'subject' => 'Tell Congress: Keep my boss out of my DNA!', 'enabled' => true, )
                                    )
                    ),
                    "id" => 267,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array (),
                    "title" => 'Genetic testing',
                    "valid" => true
                )
            );
            $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);
            
            $mailings_stats =  array ( 
                    0 => array ( 
                        'campaign_id' => '263', 
                        'variation_subject' => '0', 
                        'conversions' => '500', 
                        'losses' => '0', 
                        'sent' => '500', ),
                    1 => array ( 
                        'campaign_id' => '267', 
                        'variation_subject' => '0', 
                        'conversions' => '0', 
                        'losses' => '500', 
                        'sent' => '500', )
                    );
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign'])
                ->getMock();

            $mhMock->expects($this->once())
                ->method('wp_query_posts')
                ->will($this->returnValue($postObject));

            $mhMock->expects($this->once())
                ->method('setUpCampaigns')
                ->will($this->returnValue($campaigns));

            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($mailings_stats));

            $mhMock->expects($this->exactly(2))
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479']));
            
            $this->assertEquals(Array(
                'campaigns' => Array(
                    0 => Array(
                        'conversions' => 500,
                        'fields' => Array(
                            'subjects' => Array(
                                0 => Array (
                                    'subject' => 'Tell Senate: No attacks on immigrants',
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 263,
                        'losses' => 0,
                        'sent' => 500,
                        'subjects' => Array(
                            0 => Array(
                                'conversions' => 500,
                                'losses' => 0,
                                'sent' => 500,
                                'title' => 'Tell Senate: No attacks on immigrants',
                                'rate' => 0.6666666666666666,
                                'share' => 1.0
                            )
                        ),
                        'title' => 'No wall testing',
                        'valid' => true,
                        'rate' => 0.6666666666666666,
                        'share' => 1.0,
                        'limit' => 4.0
                    ),
                    1 => Array(
                        'conversions' => 0,
                        'fields' => Array (
                            'subjects' => Array(
                                0 => Array(
                                    'subject' => 'Tell Congress: Keep my boss out of my DNA!',
                                    'enabled' => true
                                )
                            )
                        ),
                        'id' => 267,
                        'losses' => 500,
                        'sent' => 500,
                        'subjects' => Array (
                            0 => Array(
                                'conversions' => 0,
                                'losses' => 500,
                                'sent' => 500,
                                'title' => 'Tell Congress: Keep my boss out of my DNA!',
                                'rate' => 0,
                                'share' => 0
                            )
                        ),
                        'title' => 'Genetic testing',
                        'valid' => true,
                        'rate' => 0,
                        'share' => 0.0,
                        'limit' => 0.0
                    ),
                ),
                'overall' => Array(
                    'conversions' => 500,
                    'losses' => 500,
                    'sent' => 1000,
                    'boost' => 500,
                    'rate' => 0.3333333333333333
                )
            ), $mailings->get_distributions($wpdb, $mhMock, $boost_test_value));
        }

        public function test_two_campaigns_one_subject_one_campaign_not_successful(): void 
        {
            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb = new wpdb();
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign', 'send', 'get_url'])
                ->getMock();

            $mhMock->expects($this->exactly(2))
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479','6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479']));
            
            $mhMock->expects($this->once())
                ->method('send');

            $mhMock->expects($this->exactly(2))
                ->method('get_url')
                ->willReturn('https://victorykit.local/c/no-wall-testing-2/');
            
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->will($this->returnValue(
                            Array('campaigns' => Array(
                                                0 => Array(
                                                    'conversions' => 500,
                                                    'fields' => Array(
                                                        'from_line' => '41',
                                                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                                                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                                                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                                                        'subjects' => Array(
                                                            0 => Array (
                                                                'subject' => 'Tell Senate: No attacks on immigrants',
                                                                'enabled' => true
                                                            )
                                                        )
                                                    ),
                                                    'id' => 263,
                                                    'losses' => 0,
                                                    'sent' => 500,
                                                    'subjects' => Array(
                                                        0 => Array(
                                                            'conversions' => 500,
                                                            'losses' => 0,
                                                            'sent' => 500,
                                                            'title' => 'Tell Senate: No attacks on immigrants',
                                                            'rate' => 0.6666666666666666,
                                                            'share' => 1.0
                                                        )
                                                    ),
                                                    'title' => 'No wall testing',
                                                    'valid' => true,
                                                    'rate' => 0.6666666666666666,
                                                    'share' => 1.0,
                                                    'limit' => 4.0
                                                ),
                                                1 => Array(
                                                    'conversions' => 0,
                                                    'fields' => Array (
                                                        'subjects' => Array(
                                                            0 => Array(
                                                                'subject' => 'Tell Congress: Keep my boss out of my DNA!',
                                                                'enabled' => true
                                                            )
                                                        )
                                                    ),
                                                    'id' => 267,
                                                    'losses' => 500,
                                                    'sent' => 500,
                                                    'subjects' => Array (
                                                        0 => Array(
                                                            'conversions' => 0,
                                                            'losses' => 500,
                                                            'sent' => 500,
                                                            'title' => 'Tell Congress: Keep my boss out of my DNA!',
                                                            'rate' => 0,
                                                            'share' => 0
                                                        )
                                                    ),
                                                    'title' => 'Genetic testing',
                                                    'valid' => true,
                                                    'rate' => 0,
                                                    'share' => 0.0,
                                                    'limit' => 0.0
                                                ),
                                            ),
                                            'overall' => Array(
                                                'conversions' => 500,
                                                'losses' => 500,
                                                'sent' => 1000,
                                                'boost' => 500,
                                                'rate' => 0.3333333333333333
                                            )
                                        )
                                    )
                                );
                $this->assertEquals(Array(
                    0 => Array(
                        'from_line' => '41',
                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                        'campaign_id' => 263,
                        'limit' => 4.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => 'Tell Senate: No attacks on immigrants',
                        'subscribers' => Array (
                            0 => '6630475',
                            1 => '6630477',
                            2 => '6630478',
                            3 => '6630479'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 0

                    )
                ),vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb, $mhMock));
        }

        public function test_two_campaigns_both_equally_successful(): void 
        {
            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb = new wpdb();
 
            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb', 'get_fresh_subscribers_for_campaign', 'send', 'get_url'])
                ->getMock();

            $mhMock->expects($this->exactly(2))
                ->method('get_fresh_subscribers_for_campaign')
                ->will($this->returnValue(['6630475', '6630477', '6630478','6630479','6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479', '6630475', '6630477', '6630478','6630479']));
            
            $mhMock->expects($this->exactly(2))
                ->method('send');

            $mhMock->expects($this->exactly(2))
                ->method('get_url')
                ->willReturn('https://victorykit.local/c/no-wall-testing-2/');
            
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->will($this->returnValue(
                                        Array(
                                            'campaigns' => Array(
                                                0 => Array(
                                                    'conversions' => 500,
                                                    'fields' => Array(
                                                        'from_line' => '41',
                                                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                                                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                                                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                                                        'subjects' => Array(
                                                            0 => Array (
                                                                'subject' => 'Tell Senate: No attacks on immigrants',
                                                                'enabled' => true
                                                            )
                                                        )
                                                    ),
                                                    'id' => 263,
                                                    'losses' => 0,
                                                    'sent' => 500,
                                                    'subjects' => Array(
                                                        0 => Array(
                                                            'conversions' => 500,
                                                            'losses' => 0,
                                                            'sent' => 500,
                                                            'title' => 'Tell Senate: No attacks on immigrants',
                                                            'rate' => 1,
                                                            'share' => 1
                                                        )
                                                    ),
                                                    'title' => 'No wall testing',
                                                    'valid' => true,
                                                    'rate' => 1,
                                                    'share' => 0.5,
                                                    'limit' => 2.0
                                                ),
                                                1 => Array(
                                                    'conversions' => 500,
                                                    'fields' => Array (
                                                        'from_line' => '41',
                                                        'body' => '<p class="p1">Imagine you just landed your dream job.</p> ', 
                                                        'petition_headline' => 'Tell Congress: Keep my boss out of my DNA!',
                                                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                                                        'subjects' => Array(
                                                            0 => Array(
                                                                'subject' => 'Tell Congress: Keep my boss out of my DNA!',
                                                                'enabled' => true
                                                            )
                                                        )
                                                    ),
                                                    'id' => 267,
                                                    'losses' => 0,
                                                    'sent' => 500,
                                                    'subjects' => Array (
                                                        0 => Array(
                                                            'conversions' => 500,
                                                            'losses' => 0,
                                                            'sent' => 500,
                                                            'title' => 'Tell Congress: Keep my boss out of my DNA!',
                                                            'rate' => 1,
                                                            'share' => 1
                                                        )
                                                    ),
                                                    'title' => 'Genetic testing',
                                                    'valid' => true,
                                                    'rate' => 1,
                                                    'share' => 0.5,
                                                    'limit' => 2.0
                                                ),
                                            ),
                                            'overall' => Array(
                                                'conversions' => 1000,
                                                'losses' => 0,
                                                'sent' => 1000,
                                                'boost' => 500,
                                                'rate' => 1
                                            )
                                        )
                                    )
                                );
                $this->assertEquals(Array(
                    0 => Array(
                        'from_line' => '41',
                        'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p>',
                        'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants',
                        'campaign_id' => 263,
                        'limit' => 2.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => 'Tell Senate: No attacks on immigrants',
                        'subscribers' => Array (
                            0 => '6630475',
                            1 => '6630477'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 0

                    ),
                    1 => Array(
                        'from_line' => '41',
                        'body' => '<p class="p1">Imagine you just landed your dream job.</p> ',
                        'petition_headline' => 'Tell Congress: Keep my boss out of my DNA!',
                        'campaign_id' => 267,
                        'limit' => 2.0,
                        'salutation' => '{{ user.first_name|default:"Hi" }},',
                        'subject' => 'Tell Congress: Keep my boss out of my DNA!',
                        'subscribers' => Array (
                            0 => '6630475',
                            1 => '6630477'
                        ),
                        'url' => 'https://victorykit.local/c/no-wall-testing-2/',
                        'variation_subject' => 0

                    )
                ),vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb, $mhMock));
        }
    }