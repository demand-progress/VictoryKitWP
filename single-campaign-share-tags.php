<?
// Get share meta.
// TODO:
// - Instead of randomizing, use performance
class SingleCampaignShareTags {

    function __construct() {
        global $post, $vk_sharing;
        $this->performance = $vk_sharing->get_sharing_performance(array(
            'post_id' => $post->ID,
        ));

        // We take the average performance, then give each variant N views worth of it.
        // This way, new variants have a chance to prove themselves.
        $this->fairness_boost = 100;
    }

    function get_variant($params) {
        $abbreviation = $params['abbreviation'];
        $fields = $params['fields'];
        $meta = $params['meta'];
        $name = $params['name'];

        if (
            // Defined?
            isset($_GET[$abbreviation])
            &&
            // Valid range?
            +$_GET[$abbreviation] > -1
            &&
            +$_GET[$abbreviation] < sizeof($fields[$name])
            &&
            // Enabled?
            $fields[$name][+$_GET[$abbreviation]]['enabled']
        ) {
            // Select the variant
            $meta['randomized'] = false;
            return +$_GET[$abbreviation];
        } else {
            // Choose a random variant, based on performance
            // Time complexity is O(N)

            // Calculate overall conversion rate
            $views = 0;
            $conversions = 0;
            foreach ($this->performance[$name] as $index => $variant) {
                $enabled = $fields[$name][$index]['enabled'];
                if (!$enabled) {
                    continue;
                }
                $views += $variant['views'];
                $conversions += $variant['conversions'];
            }
            if ($views == 0) {
                return 0;
            }
            $overall_rate = $conversions / $views;

            // Randomize if needed
            if ($overall_rate == 0) {
                return rand(0, count($this->performance[$name]) - 1);
            }

            // Calculate conversion rates per variant
            $rates = array();
            $sum_of_boosted_rates = 0;
            foreach ($this->performance[$name] as $index => $variant) {
                $enabled = $fields[$name][$index]['enabled'];
                if (!$enabled) {
                    $rates[$index] = 0;
                    continue;
                }

                $rate = 
                    ($variant['conversions'] + $this->fairness_boost * $overall_rate)
                    /
                    ($variant['views'] + $this->fairness_boost);

                $rates[$index] = $rate;
                $sum_of_boosted_rates += $rate;
            }
            
            // Choose a variant:
            // Pick a random point between 0 and 1.
            // Add up the chance of each variant,
            // until the sum of chances is greater
            // than or equal to the point.
            $point = mt_rand() / mt_getrandmax();
            $sum_of_chances = 0;
            foreach ($rates as $index => $rate) {
                $enabled = $fields[$name][$index]['enabled'];
                if (!$enabled) {
                    continue;
                }

                $chance = $rates[$index] / $sum_of_boosted_rates;
                $sum_of_chances += $chance;

                if ($point <= $sum_of_chances) {
                    return $index;
                }
            }
        }
    }

    function get_share_meta() {
        global $fields, $post, $vk_sharing;

        $meta = array(
            'description' => '',
            'description_index' => '',
            'title' => '',
            'title_index' => '',
            'image' => '',
            'image_index' => '',
            'url' => '',
            'randomized' => true,
        );

        $name = 'share_descriptions';
        $description_index = $this->get_variant(array(
            'abbreviation' => 'sd',
            'fields' => $fields,
            'meta' => $meta,
            'name' => $name,
        ));
        $meta['description_index'] = $description_index;
        $meta['description'] = $fields[$name][$description_index]['description'];

        $name = 'share_titles';
        $title_index = $this->get_variant(array(
            'abbreviation' => 'st',
            'fields' => $fields,
            'meta' => $meta,
            'name' => $name,
        ));
        $meta['title_index'] = $title_index;
        $meta['title'] = $fields[$name][$title_index]['title'];

        $name = 'share_images';
        $image_index = $this->get_variant(array(
            'abbreviation' => 'st',
            'fields' => $fields,
            'meta' => $meta,
            'name' => $name,
        ));
        $meta['image_index'] = $image_index;
        $meta['image'] = $fields[$name][$image_index]['image']['url'];
        $meta['image:width'] = $fields[$name][$image_index]['image']['width'];
        $meta['image:height'] = $fields[$name][$image_index]['image']['height'];

        $meta['url'] = get_permalink();
        $meta['url'] .= '?vk=1';
        $meta['url'] .= '&sd=' . $description_index;
        $meta['url'] .= '&st=' . $title_index;
        $meta['url'] .= '&si=' . $image_index;

        return $meta;
    }
}

add_action('wp_head', function() {
    global $share_meta;

    $single_campaign_share_tags = new SingleCampaignShareTags();
    $share_meta = $single_campaign_share_tags->get_share_meta();
    ?>

    <!-- Facebook -->
    <meta property="og:description" content="<?= $share_meta['description'] ?>">
    <meta property="og:image" content="<?= $share_meta['image'] ?>">
    <meta property="og:image:width" content="<?= $share_meta['image:width'] ?>">
    <meta property="og:image:height" content="<?= $share_meta['image:height'] ?>">
    <meta property="og:site_name" content="VictoryKit">
    <meta property="og:title" content="<?= $share_meta['title'] ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $share_meta['url'] ?>">

    <?
});
