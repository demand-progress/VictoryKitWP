<?
declare(strict_types=1);
define('ABSPATH', 1);
define('ARRAY_A', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');
require_once(__DIR__. '/twoCampaigns.php');
require_once(__DIR__. '/singleCampaign.php');
require_once(__DIR__. '/twoSubjectCampaign.php');

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

// $wpdb = new wpdb;

// class ak {

// };

// $ak = new ak;

use PHPUnit\Framework\TestCase;

final class mailingsClass extends TestCase
    {   
        public function test_vk_mailings_create_new_mailings_action_two_campaigns(): void {   
            global $TwoCampaigns;

            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);
        
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn($TwoCampaigns);
                    
            $vk_mailings_mock->expects($this->exactly(2))
                ->method('get_fresh_subscribers_for_campaign')
                ->willReturn(array( 0 => 6630470, 1 => 6630472,));

            $vk_mailings_mock->expects($this->exactly(3))
                ->method('send');

            $wpdb_mock->expects($this->exactly(1))
                ->method('query');

            $result = vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock);
            // $this->assertTrue($result === null);
        }

        public function test_vk_mailings_create_new_mailings_action_one_campaigns(): void {   
            global $singleCampaign;

            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);
        
            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn($singleCampaign);
                
            $vk_mailings_mock->expects($this->exactly(1))
                ->method('get_fresh_subscribers_for_campaign')
                ->willReturn(array( 0 => 6630470, 1 => 6630472,));

            $vk_mailings_mock->expects($this->exactly(2))
                ->method('send');

            $wpdb_mock->expects($this->exactly(1))
                ->method('query');

            vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock); 
            // $this->assertFalse($result === false);
        }

        public function test_get_distribution_two_subject_campaign(): void {   
            global $twoSubjectCampaign;

            $vk_mailings_mock = $this->createMock(Mailings::class);
            $wpdb_mock = $this->createMock(wpdb::class);

            $vk_mailings_mock->expects($this->once())
                ->method('get_distributions')
                ->willReturn($twoSubjectCampaign);
                   
            $vk_mailings_mock->expects($this->exactly(1))
                ->method('get_fresh_subscribers_for_campaign')
                ->willReturn(array( 0 => 6630470, 1 => 6630472,));

            $vk_mailings_mock->expects($this->exactly(1))
                ->method('send')
                ->willReturn(array ( 'ak_mailing_id' => 1));

            $wpdb_mock->expects($this->exactly(1))
                ->method('query');

            vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock);  
            // $this->assertFalse($result === false);
        }
    }