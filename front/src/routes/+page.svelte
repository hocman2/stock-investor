<script>
    import axios from 'axios';
    import { onMount } from 'svelte';
    import { apiEndpoint } from '../config';
    import { updateUserStore, userStore } from '../user_store';

    let companies = [];
    let user = null;
    let shares = [];

    // Reflect changes made to local storage to this value
    userStore.subscribe((usr) => {
        user = usr;
    })

    onMount(async () => {
        // Retrieve all companies for display
        axios.get(apiEndpoint + '/retrieve_companies').then(
            function(response)
            {
                companies = response.data;
            }
        ).catch(function (error) {
        // handle error
        console.log(error);
        });

        user = localStorage.getItem("user");
        
        // If user item exists, retrieve all owned shares to know which one he can sell
        if (user)
        {
           retrieveOwnedShares();
        }
    });

    function retrieveOwnedShares()
    {
        axios.get(apiEndpoint + '/owned_shares', {withCredentials: true}).then(
                (response) => {
                    
                    shares = response.data;
                    
                    // Loop through each company and check if the user owns a share for it
                    // if so enable the sell button
                    for(const company of companies)
                    {
                        if (ownsShare(company.id))
                        {
                            enableSellBtn(company.id);
                        }
                    }
                }).catch((error) => console.log(error));
    }

    function ownsShare(id)
    {
        for(const share of shares)
        {
            if(share.company == id) return true;
        }

        return false;
    }

    function buyOrder(id, price)
    {
        if (price > user.balance)
        {
            return;
        }

        axios.post(apiEndpoint + "/emit_order", {
            "amount": 1,
            "company_id": id,
            "type": "BUY"
        },
        { 
            withCredentials: true
        }).then((response) =>
        {
            updateUserStore(response);

            // Instead of querying the api, just programatically add a share
            reflectBuy(id);

            // Enable sell button for this company as the user owns +1 share
            enableSellBtn(id);
        }).catch((error) => {console.log(error);});
    }

    function reflectBuy(id)
    {
        for(let share of shares)
        {
            if (share.company == id)
            {
                share.amount += 1;
                return;
            }
        }

        shares.push({company: id, amount: 1});
    }

    // Reduces amount of held shares or deletes entry
    function reflectSell(id)
    {
        for (let i = shares.length - 1; i >= 0; --i)
        {
            let share = shares[i];

            if (share.company == id)
            {
                share.amount -= 1;

                if (share.amount == 0)
                {
                    shares.splice(i, 1);
                    disableSellBtn(id);
                }
            }
        }
    }

    function enableSellBtn(id)
    {
        let sellBtn = document.querySelector("#company_"+id+" .sell");
        sellBtn.disabled = false;
    }

    function disableSellBtn(id)
    {
        let btn = document.querySelector("#company_"+id+" .sell");
        btn.disabled = true;
    }

    function sellOrder(id)
    {
        axios.post(apiEndpoint + "/emit_order", {
            "amount": 1,
            "company_id": id,
            "type": "SELL"
        },
        { 
            withCredentials: true
        }).then((response) =>
        {
            // Update user balance and reflect that sell order internally
            updateUserStore(response);

            reflectSell(id);
        }).catch((error) => {console.log(error);});
    }

</script>

<table>
    {#if user}
        <h3>{user.balance}</h3>
    {/if}
    <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            {#if user !== null}
            <th>Actions</th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {#each companies as company }
        <tr>
            <span>
                <td>{company.name}</td>
                <td>{company.price}</td>
                {#if user !== null}
                <td id={"company_"+company.id}>
                    <button class="buy" on:click={buyOrder(company.id, company.price)} >+</button>
                    <button disabled class="sell" on:click={sellOrder(company.id)} >-</button>
                </td>
                {/if}
            </span>
        </tr>
        {/each}
    </tbody>
</table>
