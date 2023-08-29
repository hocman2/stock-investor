<script>
    import { userStore, updateUserStore } from '../../user_store';
    import { apiEndpoint } from '../../config';
    import { onMount } from 'svelte';
    import dateSelect from '../../dateSelect';
    import axios from 'axios';
    import { Chart } from '../../chart.js';
    import { getRgbValue } from '../../utils.js';

    /** @type {import('./$types').PageData} */
    export let data;

    let allDates = {};
    let plotDates = [];
    let timeframe = "1D";
    let chart;

    let company = data.company;
    let user = $userStore;

    let currentAmount = 1;

    // Any change in the user store is reflected on this page
    userStore.subscribe((value) => { user = value; })

    // Empties and refills plotDates with dates matching the timeframe
    function selectDates(timeframe = "1D")
    {
        // Empty plot dates
        plotDates = {};

        let dateKeyFormat = (date) => { return `${date.getMonth()}/${date.getDate()}/${date.getFullYear()} | ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`; };
        if (timeframe == "all")
        {
            for (let date of Object.keys(allDates))
            {
                date = new Date(date);
                plotDates[dateKeyFormat(date)] = allDates[date.toISOString()];
            }

            return;
        }

        // Select new dates
        let dates = dateSelect(Object.keys(allDates).reverse(), timeframe);

        // Associate dates to their price
        for (let date of dates)
        {
            plotDates[dateKeyFormat(date)] = allDates[date.toISOString()];
        }
    }

    function amountChanged()
    {
        // min cap at 1
        if (currentAmount <= 0) {currentAmount = 1;}

        // disable sell/buy button based on some logic

        const buyBtn = document.getElementsByClassName("buy-btn")[0];
        if (currentAmount * company.price > user.balance)
        {
            buyBtn.disabled = true;
        } 
        else if (buyBtn.disabled)
        {
            buyBtn.disabled = false;
        }
        
        const sellBtn = document.getElementsByClassName("sell-btn")[0];
        if (currentAmount > data.share_amount)
        {
            sellBtn.disabled = true;
        }
        else if (sellBtn.disabled)
        {
            sellBtn.disabled = false;
        }

    }

    function buyOrder(id)
    {
        axios.post(apiEndpoint + "/emit_order", {
            "amount": currentAmount,
            "company_id": id,
            "type": "BUY"
        },
        { 
            withCredentials: true
        }).then((response) =>
        {
            updateUserStore(response.data);
            data.share_amount += currentAmount;
            // This will rerun a check on wether we can buy/sell or not
            amountChanged();

        }).catch((error) => 
        { 
            // There is probably an asynchrony between local storage and session
            window.location.href = "/check_auth";
        });
    }

    function sellOrder(id)
    {
        axios.post(apiEndpoint + "/emit_order", {
            "amount": currentAmount,
            "company_id": id,
            "type": "SELL"
        },
        { 
            withCredentials: true
        }).then((response) =>
        {
            // Update user balance and reflect that sell order internally
            updateUserStore(response.data);
            data.share_amount -= currentAmount;
            // This will rerun a check on wether we can buy/sell or not
            amountChanged();
            
        }).catch((error) => 
        { 
            // There is probably an asynchrony between local storage and session
            window.location.href = "/check_auth";
        });
    }

    function calcDiff()
    {
        let previousDate = Object.keys(plotDates)[Object.keys(plotDates).length - 2];
        return company.price - plotDates[previousDate];
    }

    function createOrUpdateChart(update = false)
    {
        selectDates(timeframe);

        // Calculate diff using the plotted dates
        company.diff = calcDiff();

        let graphColor = (company.diff < 0) ? getComputedStyle(document.querySelector('body')).getPropertyValue('--negative') : getComputedStyle(document.querySelector('body')).getPropertyValue('--positive');
        graphColor = getRgbValue(graphColor);
        let areaColor = graphColor.replace('rgb', 'rgba').replace(')', ', .3)');
        
        if (update && chart)
        {
            chart.data.labels = Object.keys(plotDates);
            chart.data.datasets[0].data = Object.values(plotDates);
            chart.data.datasets[0].borderColor = graphColor;
            chart.data.datasets[0].fill.above = areaColor;
            chart.update();
            return;
        }

        let stockChartDiv = document.getElementById("stock-chart");
        if (!stockChartDiv) return;

        let ctx = stockChartDiv.getContext('2d');

        let chartData = {
            labels: Object.keys(plotDates),
            datasets: [{
                data: Object.values(plotDates),
                borderWidth: 2,
                label: "prices",
                pointRadius: 0,
                tension: 0.1,
                fill: {
                    target: "origin",
                    above: areaColor,
                },
                borderColor: graphColor,
            }],
        };

        chart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        display: false,
                    },
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            label: (context) => {
                                let label = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'x',
                    intersect: false,
                    axis: 'xy',
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (val, index, ticks) => { 
                            return `\$${parseFloat(val).toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }

    onMount(() =>
    {
        if (user)
        {
            amountChanged();
        }

        for (let priceObj of data.prices)
        {
            // Appending "Z" to indicate UTC timezone
            allDates[new Date(priceObj.date.date + "Z").toISOString()] = priceObj.price;
        }

        createOrUpdateChart();
    });

</script>

<style>
    .canvas-holder {
        width: 95vw;
        display:block;
        margin: 20px auto;
    }

    .top-bar{
        display:flex;
        align-items: center;
        justify-content: space-between;
    }

    .top-bar > div {
        text-align: right;
    }

    .top-bar > div > span:nth-child(1){
        font-size: 13pt;
        font-weight: 400;
        font-style: normal;
    }

    .top-bar > div > span{
        font-size: 11pt;
        font-weight: 300;
        font-style: italic;
    }

    .controls {
        margin: 20px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .amount {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    .amount > input {
        padding-right: 16px;
        border-radius: 8px;
        height: 24px;
        border: none;
        outline-width: 1px;
        outline-style: solid;
        direction: rtl;

        text-align: right;
        font-size: 10pt;
        font-weight: 300;   
        
        outline-color: rgba(0, 0, 0, 0.25);
        background-color: var(--background);
    }

    .buttons {
        display: flex;
        justify-content: space-evenly;
        margin: 8px auto;
    }

    .buttons > button {
        width: 100px;
        margin: 0 8px; 
    }

    h1 {
        margin: auto 0;
    }
    h2 {
        margin: auto 0;
    }
    h3 {
        margin: auto 0;
    }
</style>

<div class="top-bar">
    <h1>{company.name}</h1>
    {#if user}
        <div>
            <span>Balance: ${user.balance.toFixed(2)}</span>
            <br>
            {#if data.share_amount > 0}
                <span>Owns: {data.share_amount}</span>
            {/if}
        </div>
    {/if}
</div>
{#if company.domain_name != "null"}
    <h2>{company.domain_name}</h2>
{/if}

<h3>${company.price.toFixed(2)}</h3>

<div class="canvas-holder">
    <select bind:value={timeframe} on:change={() => {createOrUpdateChart(true);}}>
        <option selected value="1D">1D</option>
        <option value="1M">1M</option>
        <option value="3M">3M</option>
        <option value="1Y">1Y</option>
        <option value="all">All</option>
    </select>

    <canvas id="stock-chart"></canvas>
</div>

{#if user}
    <div class="controls">
        <div>
            <div class="amount">
                <input placeholder="amount" name="amount" type="number" bind:value={currentAmount} on:change={amountChanged}/>
            </div>
            <div class="buttons">
                <button on:click={ () => { buyOrder(company.id); } } class="buy-btn">Buy</button>
                <button on:click={ () => { sellOrder(company.id); } } class="sell-btn">Sell</button>
            </div>
        </div>
    </div>
{/if}