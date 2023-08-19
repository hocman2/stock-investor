<script>
    import { userStore, updateUserStore } from '../../user_store';
    import { apiEndpoint } from '../../config';
    import { onMount } from 'svelte';
    import axios from 'axios';

    /** @type {import('./$types').PageData} */
    export let data;

    let company = data.company;
    let user = $userStore;
    let currentAmount = 1;

    // Any change in the user store is reflected on this page
    userStore.subscribe((value) => { user = value; })

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

        }).catch((error) => { location.replace("/login"); });
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
            
        }).catch((error) => { location.replace("/login"); });
    }

    onMount(() =>
    {
        if (user)
        {
            amountChanged();
        }
    });

</script>

<h1>{company.name} — ${company.price}</h1>
{#if company.domain_name != "null"}
    <h2>{company.domain_name}</h2>
{/if}

{#if user}
<div> 
    <span>Balance: ${user.balance}</span>
    <br>
    {#if data.share_amount > 0}
    <span>You currently own {data.share_amount} {data.share_amount == 1 ? "share" : "shares"} for this company</span>
    {/if}
</div>
<div>
    <label for="amount">Amount</label>
    <input name="amount" type="number" bind:value={currentAmount} on:change={amountChanged}/>
</div>
<div>
    <button on:click={() => {buyOrder(company.id)} } class="buy-btn">Buy</button>
    <button on:click={() => {sellOrder(company.id)} } class="sell-btn">Sell</button>
</div>
{/if}