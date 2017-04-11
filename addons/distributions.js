// Sort campaigns by ID
distributions.campaigns.sort((a, b) => a.id - b.id);

let chartId = 1;
function addChart(title, points) {
    const id = `chart-number-${chartId++}`;

    const el = document.createElement('div');
    el.id = id;
    el.className = 'chart';
    el.style.height = '300px';
    el.style.width = 'calc(100% - 20px)';
    document.querySelector('.charts').appendChild(el);

    const chart = new CanvasJS.Chart(id, {
        animationEnabled: true,
        title: {
            text: title,
        },
        data: [{
            type: 'column', //change type to bar, line, area, pie, etc
            dataPoints: points,
        }]
    });

    chart.render();
}

function addTitle(text) {
    addTextElement(text, 'h2');
}

function addParagraph(text) {
    addTextElement(text, 'p');
}

function addTextElement(text, tag) {
    const el = document.createElement(tag);
    el.appendChild(new Text(text));
    document.querySelector('.charts').appendChild(el);
}

const conversionRatePerCampaign = [];
for (let index in distributions.campaigns) {
    const campaign = distributions.campaigns[index];
    const percent = campaign.sent && campaign.conversions / campaign.sent * 100;
    conversionRatePerCampaign.push({
        // label: `${campaign.title}:   ${percent.toFixed(3)}%  =  (${campaign.conversions} / ${campaign.sent})`,
        label: `${percent.toFixed(3)}%  =  (${campaign.conversions - campaign.losses}  /  ${campaign.sent})`,
        y: percent,
    });
}

const boostedRatePerCampaign = [];
for (let index in distributions.campaigns) {
    const campaign = distributions.campaigns[index];
    const percent = campaign.rate * 100;
    const overallRate = distributions.overall.rate;
    const boostMultiplier = distributions.overall.boost;
    const conversionBoost = (overallRate * boostMultiplier).toFixed(3);
    boostedRatePerCampaign.push({
        // label: `${campaign.title} (${percent.toFixed(1)}%)`,
        label: `${percent.toFixed(3)}%  =  (${campaign.conversions - campaign.losses} + ${conversionBoost})  /  (${campaign.sent} + ${boostMultiplier})`,
        y: percent,
    });
}

const distributionsPerCampaign = [];
for (let index in distributions.campaigns) {
    const campaign = distributions.campaigns[index];
    const percent = campaign.share * 100;
    distributionsPerCampaign.push({
        label: `${percent.toFixed(1)}%`,
        y: percent,
    });
}

addParagraph('Now this is a story all about how, our distributions are being calculated now.');

console.log(distributions);
addTitle(`1) We calculate conversion rates.`);
addParagraph(`Conversion Rate = (Signups - Losses) / Sends`);
addChart(`Conversions (%)`, conversionRatePerCampaign);

addTitle(`2) We boost conversion rates.`);
addParagraph(`This helps shield young petitions from being misjudged due to early failures or lucky streaks.`);
addParagraph(`Here's the kind of, but not really, complex math:`);
const averageRate = (distributions.overall.rate * 100).toFixed(3);
addParagraph(`Average Conversion Rate = ${averageRate}%`);
addParagraph(`Boost Multiplier =  ${distributions.overall.boost}`);
addParagraph(`Boosted Signups = (Signups - Losses) + (Average Conversion Rate * Boost Multiplier)`);
addParagraph(`Boosted Sends = Sends + Boost Multiplier`);
addParagraph(`Boosted Conversion Rate = Boosted Signups / Boosted Sends`);
addChart(`Boosted Conversions (%)`, boostedRatePerCampaign);

addTitle(`3) We calculate shares.`);
addParagraph(`We find the shares by comparing each individual boosted conversion rate, to the overall sum.`);
addParagraph(`If any campaigns have already saturated the mailing list, their share is reduced.`);
addParagraph(`For example, if a campaign has been sent to every subscriber, its rate must be zero.`);
addChart(`Distributions (%)`, distributionsPerCampaign);

addParagraph(`As the algorithm improves, we'll keep this page updated.`);

document.querySelector('.charts').classList.add('visible');
