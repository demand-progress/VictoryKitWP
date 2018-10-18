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

            $vk_mailings_mock->expects($this->exactly(3))
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

            $vk_mailings_mock->expects($this->exactly(2))
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

            $vk_mailings_mock->expects($this->once())
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
            $postObject->posts = array(0 => (object) ['ID' => '', 'post_title'=> '']);

            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                        ->setMethods(['wp_query_posts'])
                        ->getMock();

            $mhMock->expects($this->once())
                        ->method('wp_query_posts')
                        ->will($this->returnValue($postObject));

            $mailingsMock->get_distributions($wpdb, $mhMock); 
        }

        public function test_get_fields(): void 
        {
            $wpdb = new wpdb();
            $postObject = new stdClass();
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
    
            $subject->get_distributions($wpdb, $mhMock);
        }

        public function test_mailingsHelpers_setUpCampaigns(): void 
        {
            $wpdb = new wpdb();
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
    
            $subject->get_distributions($wpdb, $mhMock);
        }

        public function test_mailingsHelpers_get_mailings_results_wpdb(): void 
        {
            $wpdb = new wpdb();
            // $postObject = new stdClass();
            $postObject = array( 0 => array( 'campaign_id' => '1'));

            $mhMock= $this->getMockBuilder(MailingsHelpers::class)
                         ->setMethods(['get_mailings_results_wpdb'])
                         ->getMock();
            
            $mhMock->expects($this->once())
                ->method('get_mailings_results_wpdb')
                ->will($this->returnValue($postObject));
           
            $subject = new Mailings();
    
            $subject->get_distributions($wpdb, $mhMock);
        }

        public function test_mailings_distribution_post_method(): void 
        {
            $mailings = new Mailings();
            $wpdb = new wpdb();
            $postObject = new stdClass();
      
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
            ), $mailings->get_distributions($wpdb, $mhMock));
            
        }
    }


    // ( 0 => array ( 'conversions' => 0, 'fields' => array ( 'subjects' => array ( 0 => array ( 'subject' => 'Tell Senate: No attacks on immigrants', 'enabled' => true, ), 1 => array ( 'subject' => 'Block Trump's attacks on immigrants', 'enabled' => true, ), ), 'salutation' => '{{ user.first_name|default:"Hi" }},', 'body' => '<p>When he ran for president, Trump promised to harass and terrorize immigrants. <strong>Now he is doing everything he can to fulfill those promises.</strong></p> <p>Already he has banned Muslims and refugees, threatened sanctuary cities and criminalized millions of undocumented immigrants. His Immigration and Custom Enforcement (ICE) and Border Patrol agents are separating parents from their children, deporting innocent people and spreading fear in immigrant communities across the country. The budget he just released slashes funding for the social safety net in order to spend billions of dollars building his wall, increasing his deportation force and expediting deportations.</p> <p><strong>Senate Democrats have the power to block funding for Trump&#8217;s wall and his massive deportation force. Recent reports show they may be willing to fight to stop Trump&#8217;s hateful agenda.</strong><span style="font-size: 13.3333px;"> </span><strong>Can you help make sure they stand strong?</strong></p> <p>We cannot let any Senate Democrat cave to political pressure from the right-wing and give Republicans the 60 votes they need to fund Trump’s hate. That is where progressives come in. We have to show Democratic senators that <strong>we will have their backs if they act boldly and that we will hold them accountable if they fail to stand up for immigrants.</strong></p> <p><strong>Tell Senate Democrats: Block funding for Trump’s attacks on immigrants. Click here to sign the petition.</strong></p> <p>Trump wants to harass, deport and reject people who have come or are dreaming of coming to the United States seeking refuge from conflict, lives free from persecution and better economic opportunities for their families. For as long as Trump is president and extremist right-wing Republicans are willing to enable his agenda, Senate Democrats will have a choice: enable Trump’s agenda of criminalization and deportations, or resist and obstruct his racist regime at every turn. <strong>It is up to make sure they do the right thing.</strong></p> <p><strong>Tell Senate Democrats: Block funding for Trump’s attacks on immigrants.</strong></p> ', 'petition_text' => '<p>&#8220;Block funding for Trump’s attacks on immigrants, including his deportation force and his border wall. &#8220;</p> ', 'disclaimer' => '<p>We do not share your email address without your permission. We may send you updates on this and other important campaigns by email. If at any time you would like to unsubscribe from our email list, you may do so.</p> ', 'share_titles' => array ( 0 => array ( 'title' => 'Senate Democrats: Block Trump’s attacks on immigrants', 'enabled' => true, ), 1 => array ( 'title' => 'Block the border wall!', 'enabled' => true, ), ), 'share_descriptions' => array ( 0 => array ( 'description' => 'There's new legislation to block Trump's border wall -- tell the Senate to pass it.', 'enabled' => true, ), 1 => array ( 'description' => 'The Senate can block Trump's anti-immigrant agenda, including the border wall.', 'enabled' => true, ), ), 'share_images' => array ( 0 => array ( 'image' => array ( 'ID' => 203, 'id' => 203, 'title' => 'Logo-square', 'filename' => 'Logo-square.png', 'url' => 'https://victorykit.local/wp-content/uploads/2016/11/Logo-square.png', 'alt' => '', 'author' => '5', 'description' => '', 'caption' => '', 'name' => 'logo-square', 'date' => '2017-03-01 04:24:22', 'modified' => '2017-03-01 04:24:22', 'mime_type' => 'image/png', 'type' => 'image', 'icon' => 'https://victorykit.local/wp-includes/images/media/default.png', 'width' => 132, 'height' => 125, 'sizes' => array ( 'thumbnail' => 'https://victorykit.local/wp-content/uploads/2016/11/Logo-square.png', 'thumbnail-width' => 132, 'thumbnail-height' => 125, 'medium' => 'https://victorykit.local/wp-content/uploads/2016/11/Logo-square.png', 'medium-width' => 132, 'medium-height' => 125, 'medium_large' => 'https://victorykit.local/wp-content/uploads/2016/11/Logo-square.png', 'medium_large-width' => 132, 'medium_large-height' => 125, 'large' => 'https://victorykit.local/wp-content/uploads/2016/11/Logo-square.png', 'large-width' => 132, 'large-height' => 125, ), ), 'enabled' => true, ), ), 'petition_headline' => 'Senate Democrats: Block Trump’s attacks on immigrants', 'from_line' => '41', 'landing_page_body' => '', 'after_action_email_content' => '<p>Thank you for taking action. Now please share this important campaign with your friends, and ask them to join in it. This will make it much more likely we can win!</p> ', ), 'id' => 263, 'losses' => 0, 'sent' => 0, 'subjects' => array ( 0 => array ( 'conversions' => 0, 'losses' => 0, 'sent' => 0, 'title' => 'Tell Senate: No attacks on immigrants', 'rate' => 1, 'share' => 0.5, ), 1 => array ( 'conversions' => 0, 'losses' => 0, 'sent' => 0, 'title' => 'Block Trump's attacks on immigrants', 'rate' => 1, 'share' => 0.5, ), ), 'title' => 'No wall testing', 'valid' => true, 'rate' => 1, 'share' => 1, 'limit' => 4.0, ), )




    // $posts = array( 'query' => array ( 'post_type' => 'campaign', 'post_status' => 'publish', ), 'query_vars' => array ( 'post_type' => 'campaign', 'post_status' => 'publish', 'error' => '', 'm' => '', 'p' => 0, 'post_parent' => '', 'subpost' => '', 'subpost_id' => '', 'attachment' => '', 'attachment_id' => 0, 'name' => '', 'static' => '', 'pagename' => '', 'page_id' => 0, 'second' => '', 'minute' => '', 'hour' => '', 'day' => 0, 'monthnum' => 0, 'year' => 0, 'w' => 0, 'category_name' => '', 'tag' => '', 'cat' => '', 'tag_id' => '', 'author' => '', 'author_name' => '', 'feed' => '', 'tb' => '', 'paged' => 0, 'meta_key' => '', 'meta_value' => '', 'preview' => '', 's' => '', 'sentence' => '', 'title' => '', 'fields' => '', 'menu_order' => '', 'embed' => '', 'category__in' => array ( ), 'category__not_in' => array ( ), 'category__and' => array ( ), 'post__in' => array ( ), 'post__not_in' => array ( ), 'post_name__in' => array ( ), 'tag__in' => array ( ), 'tag__not_in' => array ( ), 'tag__and' => array ( ), 'tag_slug__in' => array ( ), 'tag_slug__and' => array ( ), 'post_parent__in' => array ( ), 'post_parent__not_in' => array ( ), 'author__in' => array ( ), 'author__not_in' => array ( ), 'ignore_sticky_posts' => false, 'suppress_filters' => false, 'cache_results' => true, 'update_post_term_cache' => true, 'lazy_load_term_meta' => true, 'update_post_meta_cache' => true, 'posts_per_page' => 10, 'nopaging' => false, 'comments_per_page' => '50', 'no_found_rows' => false, 'order' => 'DESC', ), 'tax_query' => WP_Tax_Query::__set_state(array( 'queries' => array ( ), 'relation' => 'AND', 'table_aliases' => array ( ), 'queried_terms' => array ( ), 'primary_table' => 'wp_posts', 'primary_id_column' => 'ID', )), 'meta_query' => WP_Meta_Query::__set_state(array( 'queries' => array ( ), 'relation' => NULL, 'meta_table' => NULL, 'meta_id_column' => NULL, 'primary_table' => NULL, 'primary_id_column' => NULL, 'table_aliases' => array ( ), 'clauses' => array ( ), 'has_or_relation' => false, )), 'date_query' => false, 'request' => 'SELECT SQL_CALC_FOUND_ROWS wp_posts.ID FROM wp_posts WHERE 1=1 AND wp_posts.post_type = 'campaign' AND ((wp_posts.post_status = 'publish')) ORDER BY wp_posts.post_date DESC LIMIT 0, 10', 'posts' => array ( 0 => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), ), 'post_count' => 1, 'current_post' => -1, 'in_the_loop' => false, 'post' => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), 'comment_count' => 0, 'current_comment' => -1, 'found_posts' => '1', 'max_num_pages' => 1.0, 'max_num_comment_pages' => 0, 'is_single' => false, 'is_preview' => false, 'is_page' => false, 'is_archive' => true, 'is_date' => false, 'is_year' => false, 'is_month' => false, 'is_day' => false, 'is_time' => false, 'is_author' => false, 'is_category' => false, 'is_tag' => false, 'is_tax' => false, 'is_search' => false, 'is_feed' => false, 'is_comment_feed' => false, 'is_trackback' => false, 'is_home' => false, 'is_404' => false, 'is_embed' => false, 'is_paged' => false, 'is_admin' => false, 'is_attachment' => false, 'is_singular' => false, 'is_robots' => false, 'is_posts_page' => false, 'is_post_type_archive' => true, 'query_vars_hash' => 'e36a12d927198b4f30d55d60b891715f', 'query_vars_changed' => false, 'thumbnails_cached' => false, 'stopwords' => NULL, 'compat_fields' => array ( 0 => 'query_vars_hash', 1 => 'query_vars_changed', ), 'compat_methods' => array ( 0 => 'init_query_flags', 1 => 'parse_tax_query', ), );

    // 'posts' => array ( 0 => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), ), 'post_count' => 1, 'current_post' => -1, 'in_the_loop' => false, 'post' => WP_Post::__set_state(array( 'ID' => 263, 'post_author' => '8', 'post_date' => '2018-06-26 20:10:27', 'post_date_gmt' => '2018-06-26 20:10:27', 'post_content' => '', 'post_title' => 'No wall testing', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => 'no-wall-testing-2', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2018-06-28 17:54:34', 'post_modified_gmt' => '2018-06-28 17:54:34', 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => 'https://victorykit.local/?post_type=campaign&#038;p=263', 'menu_order' => 0, 'post_type' => 'campaign', 'post_mime_type' => '', 'comment_count' => '0', 'filter' => 'raw', )), 'comment_count' => 0, 'current_comment' => -1, 'found_posts' => '1', 'max_num_pages' => 1.0, 'max_num_comment_pages' => 0, 'is_single' => false, 'is_preview' => false, 'is_page' => false, 'is_archive' => true, 'is_date' => false, 'is_year' => false, 'is_month' => false, 'is_day' => false, 'is_time' => false, 'is_author' => false, 'is_category' => false, 'is_tag' => false, 'is_tax' => false, 'is_search' => false, 'is_feed' => false, 'is_comment_feed' => false, 'is_trackback' => false, 'is_home' => false, 'is_404' => false, 'is_embed' => false, 'is_paged' => false, 'is_admin' => false, 'is_attachment' => false, 'is_singular' => false, 'is_robots' => false, 'is_posts_page' => false, 'is_post_type_archive' => true, 'query_vars_hash' => 'e36a12d927198b4f30d55d60b891715f', 'query_vars_changed' => false, 'thumbnails_cached' => false, 'stopwords' => NULL, 'compat_fields' => array ( 0 => 'query_vars_hash', 1 => 'query_vars_changed', ), 'compat_methods' => array ( 0 => 'init_query_flags', 1 => 'parse_tax_query', ), ))