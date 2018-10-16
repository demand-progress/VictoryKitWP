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
        // public function test_vk_mailings_create_new_mailings_action_two_campaigns(): void 
        // {   
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

        // public function test_vk_mailings_create_new_mailings_action_one_campaigns(): void 
        // {   
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

        // public function test_get_distribution_two_subject_campaign(): void 
        // {   
        //     global $twoSubjectCampaign;

        //     $vk_mailings_mock = $this->createMock(Mailings::class);
        //     $wpdb_mock = $this->createMock(wpdb::class);

        //     $vk_mailings_mock->expects($this->once())
        //         ->method('get_distributions')
        //         ->willReturn($twoSubjectCampaign);
                   
        //     $vk_mailings_mock->expects($this->once())
        //         ->method('get_fresh_subscribers_for_campaign')
        //         ->willReturn(array( 0 => 6630470, 1 => 6630472,));

        //     $vk_mailings_mock->expects($this->once())
        //         ->method('send')
        //         ->willReturn(array ( 'ak_mailing_id' => 1));

        //     $wpdb_mock->expects($this->once())
        //         ->method('query');

        //     vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock);  
        // }

        // public function test_mailingsHelpers_wp_query_posts_method(): void 
        // {
        //     $mailingsMock = new Mailings();
        //     $wpdb = new wpdb();
        //     $postObject = new stdClass();
        //     $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);

        //     $mhMock= $this->getMockBuilder(MailingsHelpers::class)
        //                 ->setMethods(['wp_query_posts'])
        //                 ->getMock();

        //     $mhMock->expects($this->once())
        //                 ->method('wp_query_posts')
        //                 ->will($this->returnValue($postObject));

        //     $mailingsMock->get_distributions($wpdb, $mhMock); 
        // }

        // public function test_get_fields(): void 
        // {
        //     $wpdb = new wpdb();
        //     $postObject = new stdClass();
        //     $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);

        //     $mhMock= $this->getMockBuilder(MailingsHelpers::class)
        //                  ->setMethods(['wp_query_posts', 'getFields'])
        //                  ->getMock();
            
        //     $mhMock->expects($this->once())
        //         ->method('wp_query_posts')
        //         ->will($this->returnValue($postObject));

        //     $mhMock->expects($this->once())
        //         ->method('getFields');
           
        //     $subject = new Mailings();
    
        //     $subject->get_distributions($wpdb, $mhMock);
        // }

        // public function test_mailingsHelpers_setUpCampaigns(): void 
        // {
        //     $wpdb = new wpdb();
        //     // $postObject = new stdClass();
        //     $postObject = array(0 => (object) ['ID' => '', 'post_title'=> '']);
        //     $campaigns = array( 0 => array( 'fields'=> '', 'campaign_id' => '', 'subjects' => array()));

        //     $mhMock= $this->getMockBuilder(MailingsHelpers::class)
        //                  ->setMethods(['wp_query_posts', 'setUpCampaigns'])
        //                  ->getMock();
            
        //     $mhMock->expects($this->once())
        //         ->method('wp_query_posts')
        //         ->will($this->returnValue($postObject));

        //     $mhMock->expects($this->once())
        //         ->method('setUpCampaigns')
        //         ->will($this->returnValue($campaigns));

        //     $subject = new Mailings();
    
        //     $subject->get_distributions($wpdb, $mhMock);
        // }

        // public function test_mailingsHelpers_get_mailings_results_wpdb(): void 
        // {
        //     $wpdb = new wpdb();
        //     // $postObject = new stdClass();
        //     $postObject = array( 0 => array( 'campaign_id' => '1'));

        //     $mhMock= $this->getMockBuilder(MailingsHelpers::class)
        //                  ->setMethods(['get_mailings_results_wpdb'])
        //                  ->getMock();
            
        //     $mhMock->expects($this->once())
        //         ->method('get_mailings_results_wpdb')
        //         ->will($this->returnValue($postObject));
           
        //     $subject = new Mailings();
    
        //     $subject->get_distributions($wpdb, $mhMock);
        // }

        public function test_mailings_distribution_post_method(): void 
        {
            $mailingsMock = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
            $postObject->posts = array ( 0 => (object)(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), );
          
            $campaigns = array(
                263 => array(
                    "conversions" => 0,
                    "fields" => array(
                        "subjects" => array()
                    ),
                    "id" => 263,
                    "losses" => 0,
                    "sent" => 0,
                    "subjects" => array(),
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
                ->setMethods(['wp_query_posts', 'setUpCampaigns', 'get_mailings_results_wpdb'])
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
                        
            $mailingsMock->get_distributions($wpdb, $mhMock); 
        }
    }

    // $posts = array( 'query' => array ( 'post_type' => 'campaign', 'post_status' => 'publish', ), 'query_vars' => array ( 'post_type' => 'campaign', 'post_status' => 'publish', 'error' => '', 'm' => '', 'p' => 0, 'post_parent' => '', 'subpost' => '', 'subpost_id' => '', 'attachment' => '', 'attachment_id' => 0, 'name' => '', 'static' => '', 'pagename' => '', 'page_id' => 0, 'second' => '', 'minute' => '', 'hour' => '', 'day' => 0, 'monthnum' => 0, 'year' => 0, 'w' => 0, 'category_name' => '', 'tag' => '', 'cat' => '', 'tag_id' => '', 'author' => '', 'author_name' => '', 'feed' => '', 'tb' => '', 'paged' => 0, 'meta_key' => '', 'meta_value' => '', 'preview' => '', 's' => '', 'sentence' => '', 'title' => '', 'fields' => '', 'menu_order' => '', 'embed' => '', 'category__in' => array ( ), 'category__not_in' => array ( ), 'category__and' => array ( ), 'post__in' => array ( ), 'post__not_in' => array ( ), 'post_name__in' => array ( ), 'tag__in' => array ( ), 'tag__not_in' => array ( ), 'tag__and' => array ( ), 'tag_slug__in' => array ( ), 'tag_slug__and' => array ( ), 'post_parent__in' => array ( ), 'post_parent__not_in' => array ( ), 'author__in' => array ( ), 'author__not_in' => array ( ), 'ignore_sticky_posts' => false, 'suppress_filters' => false, 'cache_results' => true, 'update_post_term_cache' => true, 'lazy_load_term_meta' => true, 'update_post_meta_cache' => true, 'posts_per_page' => 10, 'nopaging' => false, 'comments_per_page' => '50', 'no_found_rows' => false, 'order' => 'DESC', ), 'tax_query' => WP_Tax_Query::__set_state(array( 'queries' => array ( ), 'relation' => 'AND', 'table_aliases' => array ( ), 'queried_terms' => array ( ), 'primary_table' => 'wp_posts', 'primary_id_column' => 'ID', )), 'meta_query' => WP_Meta_Query::__set_state(array( 'queries' => array ( ), 'relation' => NULL, 'meta_table' => NULL, 'meta_id_column' => NULL, 'primary_table' => NULL, 'primary_id_column' => NULL, 'table_aliases' => array ( ), 'clauses' => array ( ), 'has_or_relation' => false, )), 'date_query' => false, 'request' => 'SELECT SQL_CALC_FOUND_ROWS wp_posts.ID FROM wp_posts WHERE 1=1 AND wp_posts.post_type = 'campaign' AND ((wp_posts.post_status = 'publish')) ORDER BY wp_posts.post_date DESC LIMIT 0, 10', 'posts' => array ( 0 => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), ), 'post_count' => 1, 'current_post' => -1, 'in_the_loop' => false, 'post' => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), 'comment_count' => 0, 'current_comment' => -1, 'found_posts' => '1', 'max_num_pages' => 1.0, 'max_num_comment_pages' => 0, 'is_single' => false, 'is_preview' => false, 'is_page' => false, 'is_archive' => true, 'is_date' => false, 'is_year' => false, 'is_month' => false, 'is_day' => false, 'is_time' => false, 'is_author' => false, 'is_category' => false, 'is_tag' => false, 'is_tax' => false, 'is_search' => false, 'is_feed' => false, 'is_comment_feed' => false, 'is_trackback' => false, 'is_home' => false, 'is_404' => false, 'is_embed' => false, 'is_paged' => false, 'is_admin' => false, 'is_attachment' => false, 'is_singular' => false, 'is_robots' => false, 'is_posts_page' => false, 'is_post_type_archive' => true, 'query_vars_hash' => 'e36a12d927198b4f30d55d60b891715f', 'query_vars_changed' => false, 'thumbnails_cached' => false, 'stopwords' => NULL, 'compat_fields' => array ( 0 => 'query_vars_hash', 1 => 'query_vars_changed', ), 'compat_methods' => array ( 0 => 'init_query_flags', 1 => 'parse_tax_query', ), );

    // 'posts' => array ( 0 => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), ), 'post_count' => 1, 'current_post' => -1, 'in_the_loop' => false, 'post' => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), 'comment_count' => 0, 'current_comment' => -1, 'found_posts' => '1', 'max_num_pages' => 1.0, 'max_num_comment_pages' => 0, 'is_single' => false, 'is_preview' => false, 'is_page' => false, 'is_archive' => true, 'is_date' => false, 'is_year' => false, 'is_month' => false, 'is_day' => false, 'is_time' => false, 'is_author' => false, 'is_category' => false, 'is_tag' => false, 'is_tax' => false, 'is_search' => false, 'is_feed' => false, 'is_comment_feed' => false, 'is_trackback' => false, 'is_home' => false, 'is_404' => false, 'is_embed' => false, 'is_paged' => false, 'is_admin' => false, 'is_attachment' => false, 'is_singular' => false, 'is_robots' => false, 'is_posts_page' => false, 'is_post_type_archive' => true, 'query_vars_hash' => 'e36a12d927198b4f30d55d60b891715f', 'query_vars_changed' => false, 'thumbnails_cached' => false, 'stopwords' => NULL, 'compat_fields' => array ( 0 => 'query_vars_hash', 1 => 'query_vars_changed', ), 'compat_methods' => array ( 0 => 'init_query_flags', 1 => 'parse_tax_query', ), ))