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

// $wpdb = new wpdb;

// class ak {

// };

// $ak = new ak;

use PHPUnit\Framework\TestCase;

final class mailingsClass extends TestCase
    {   
        // public function test_vk_mailings_create_new_mailings_action_two_campaigns(): void {   
        //     global $TwoCampaigns;

        //     $vk_mailings_mock = $this->createMock(Mailings::class);
        //     $wpdb_mock = $this->createMock(wpdb::class);
        
        //     $vk_mailings_mock->expects($this->once())
        //         ->method('get_distributions')
        //         ->willReturn($TwoCampaigns);
                    
        //     $vk_mailings_mock->expects($this->exactly(2))
        //         ->method('get_fresh_subscribers_for_campaign')
        //         ->willReturn(array( 0 => 6630470, 1 => 6630472,));

        //     $vk_mailings_mock->expects($this->exactly(3))
        //         ->method('send');

        //     $wpdb_mock->expects($this->exactly(1))
        //         ->method('query');

        //     $result = vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock);
        //     // $this->assertTrue($result === null);
        // }

        // public function test_vk_mailings_create_new_mailings_action_one_campaigns(): void {   
        //     global $singleCampaign;

        //     $vk_mailings_mock = $this->createMock(Mailings::class);
        //     $wpdb_mock = $this->createMock(wpdb::class);
        
        //     $vk_mailings_mock->expects($this->once())
        //         ->method('get_distributions')
        //         ->willReturn($singleCampaign);
                
        //     $vk_mailings_mock->expects($this->exactly(1))
        //         ->method('get_fresh_subscribers_for_campaign')
        //         ->willReturn(array( 0 => 6630470, 1 => 6630472,));

        //     $vk_mailings_mock->expects($this->exactly(2))
        //         ->method('send');

        //     $wpdb_mock->expects($this->exactly(1))
        //         ->method('query');

        //     vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock); 
        // }

        // public function test_get_distribution_two_subject_campaign(): void {   
        //     global $twoSubjectCampaign;

        //     $vk_mailings_mock = $this->createMock(Mailings::class);
        //     $wpdb_mock = $this->createMock(wpdb::class);

        //     $vk_mailings_mock->expects($this->once())
        //         ->method('get_distributions')
        //         ->willReturn($twoSubjectCampaign);
                   
        //     $vk_mailings_mock->expects($this->exactly(1))
        //         ->method('get_fresh_subscribers_for_campaign')
        //         ->willReturn(array( 0 => 6630470, 1 => 6630472,));

        //     $vk_mailings_mock->expects($this->exactly(1))
        //         ->method('send')
        //         ->willReturn(array ( 'ak_mailing_id' => 1));

        //     $wpdb_mock->expects($this->exactly(1))
        //         ->method('query');

        //     vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock);  
        // }

        public function test_get_distributions(): void 
        {
            $mailingsMock = $this->createMock(Mailings::class);
            $mhInstance = new MailingsHelpers();
            $wpdb = new wpdb();
            
            $mailingsMock->expects($this->once())
                    ->method('get_distributions');

            $mailingsMock->get_distributions($wpdb, $mhInstance); 
        }

        public function test_get_distributions_wp_query_posts_method(): void 
        {
            $wpdb = new wpdb();
    
            $postObject = new stdClass();
            $postObject->posts = array(0 => (object) ['ID' => 'Here we go']);

            $mhMock = $this->createMock(MailingsHelpers::class);
            $mhMock->expects($this->once())
                    ->method('wp_query_posts')
                    ->willReturn($postObject);
            
            $mailingsInstance = new Mailings();   

            $mailingsInstance->get_distributions($wpdb, $mhMock);
        }
    }

    // 'posts' => array ( 0 => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), ), 'post_count' => 1, 'current_post' => -1, 'in_the_loop' => false, 'post' => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), 'comment_count' => 0, 'current_comment' => -1, 'found_posts' => '1', 'max_num_pages' => 1.0, 'max_num_comment_pages' => 0, 'is_single' => false, 'is_preview' => false, 'is_page' => false, 'is_archive' => true, 'is_date' => false, 'is_year' => false, 'is_month' => false, 'is_day' => false, 'is_time' => false, 'is_author' => false, 'is_category' => false, 'is_tag' => false, 'is_tax' => false, 'is_search' => false, 'is_feed' => false, 'is_comment_feed' => false, 'is_trackback' => false, 'is_home' => false, 'is_404' => false, 'is_embed' => false, 'is_paged' => false, 'is_admin' => false, 'is_attachment' => false, 'is_singular' => false, 'is_robots' => false, 'is_posts_page' => false, 'is_post_type_archive' => true, 'query_vars_hash' => 'e36a12d927198b4f30d55d60b891715f', 'query_vars_changed' => false, 'thumbnails_cached' => false, 'stopwords' => NULL, 'compat_fields' => array ( 0 => 'query_vars_hash', 1 => 'query_vars_changed', ), 'compat_methods' => array ( 0 => 'init_query_flags', 1 => 'parse_tax_query', ), ))