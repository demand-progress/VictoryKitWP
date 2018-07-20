<?
declare(strict_types=1);
define('ABSPATH', 1);
define('ARRAY_A', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');
require_once(__DIR__. '/twoCampaigns.php');
require_once(__DIR__. '/singleCampaign.php');

function get_option(){
    return 25;
}
function add_action(){}
function get_permalink(){}

class WP_Query {
    public $posts = array(); 
}

class wpdb {
    function get_results(){
        return array();
    }

    function get_col(){
        return array();
    }
    
    function query(){}
};

$wpdb = new wpdb;

use PHPUnit\Framework\TestCase;

final class mailingsClass extends TestCase
    {
        public function test_vk_mailings_create_new_mailings_action(): void
            {   
                global $TwoCampaigns;

                $vk_mailings_mock = $this->createMock(Mailings::class);
            
                $vk_mailings_mock->expects($this->once())
                        ->method('get_distributions')
                        ->willReturn($TwoCampaigns);
                       
                $vk_mailings_mock->expects($this->exactly(2))
                        ->method('get_fresh_subscribers_for_campaign')
                        ->willReturn(array( 0 => 6630470, 1 => 6630472,));

                $vk_mailings_mock->expects($this->exactly(3))
                        ->method('send');

                $result = vk_mailings_create_new_mailings_action($vk_mailings_mock);
                
                // $this->assertTrue($result === null);
            }

        public function test_get_distribution_one_campaign(): void
        {   
            global $singleCampaign;

            $vk_mailings_mock = $this->createMock(Mailings::class);
        
            $vk_mailings_mock->expects($this->once())
                    ->method('get_distributions')
                    ->willReturn($singleCampaign);
                   
            $vk_mailings_mock->expects($this->exactly(1))
                    ->method('get_fresh_subscribers_for_campaign')
                    ->willReturn(array( 0 => 6630470, 1 => 6630472,));

            $vk_mailings_mock->expects($this->exactly(2))
                    ->method('send');

            vk_mailings_create_new_mailings_action($vk_mailings_mock);
            
            // $this->assertFalse($result === false);
        }
    }