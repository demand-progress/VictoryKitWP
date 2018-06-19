<?
declare(strict_types=1);
define('ABSPATH', 1);

require_once(__DIR__. '/../actionkit.php');
require_once(__DIR__. '/../mailings.php');
require_once(__DIR__. '/arrayCampaigns.php');

function get_option(){}
function add_action(){}

use PHPUnit\Framework\TestCase;

final class mailingsClass extends TestCase
    {
        public function test_vk_mailings_create_new_mailings_action(): void
            {   
                global $vk_mailings, $wpdb;
                $vk_mailings = $this->createMock(Mailings::class);
                $vk_mailings->expects($this->once())
                         ->method('get_distributions')
                         ->willReturn(array ( 'campaigns' => array ( ), 'overall' => array ( ), ));

                $result = vk_mailings_create_new_mailings_action();
   
                $this->assertTrue($result === array ( 'campaigns' => array ( ), 'overall' => array ( ), ));
            }
    }