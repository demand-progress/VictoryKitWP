($ => {
    const query = (f => {
        var result = location.search.slice(1).split('&').reduce(
            (result, pair) => {
                pair = pair.split('=');
                result[pair[0]] = decodeURIComponent(pair[1] || '');
                return result;
            },
            {}
        );

        return JSON.parse(JSON.stringify(result));
    })();

    function getSubjectPerformance() {
        const $subjects = $('.acf-row:not(".acf-clone") .performance-subject');
        if ($subjects.length === 0) {
            return;
        }

        const url = '/wp-content/themes/victorykit/addons/api.php?operation=get-subject-performance&post_id=' + query.post;
        $.getJSON(url, performance => {
            $subjects.toArray().forEach((el, i) => {
                const perf = performance[i] || {
                    conversions: 0,
                    sent: 0,
                };
                const percent = ((100 * perf.conversions / perf.sent) || 0).toFixed(2);
                const report = `${perf.conversions} (${percent}%) new users / ${perf.sent} sends`;

                $subjects.eq(i)
                    .addClass('loaded')
                    .text(report);
            });
        });
    }

    function getSharePerformance() {
        const $titles = $('.acf-row:not(".acf-clone") .performance-share-title');
        const $descriptions = $('.acf-row:not(".acf-clone") .performance-share-description');
        const $images = $('.acf-row:not(".acf-clone") .performance-share-image');

        const url = '/wp-content/themes/victorykit/addons/api.php?operation=get-sharing-performance&post_id=' + query.post;
        $.getJSON(url, performance => {
            $descriptions.toArray().forEach((el, i) => {
                const report = getPerformanceString(performance['share_descriptions'][i]);
                $(el)
                    .addClass('loaded')
                    .text(report);
            });

            $images.toArray().forEach((el, i) => {
                const report = getPerformanceString(performance['share_images'][i]);
                $(el)
                    .addClass('loaded')
                    .text(report);
            });

            $titles.toArray().forEach((el, i) => {
                const report = getPerformanceString(performance['share_titles'][i]);
                $(el)
                    .addClass('loaded')
                    .text(report);
            });
        });

        function getPerformanceString(perf) {
            const percent = ((100 * perf.conversions / perf.views) || 0).toFixed(2);
            const report = `${perf.conversions} (${percent}%) new users / ${perf.views} thanks`;
            return report;
        }
    }

    // Start
    getSubjectPerformance();
    getSharePerformance();

})(jQuery);
