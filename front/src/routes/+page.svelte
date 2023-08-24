<script>
    import { userStore } from '../user_store';
    import { onMount } from 'svelte';
    import axios from 'axios';
    import { apiEndpoint } from '../config';

    /** @type {import('./$types').PageData} */
    export let data;

    let companies = data.companies;
    let nextUpdate = getNextUpdateDate(data.nextUpdate);
    console.log(nextUpdate);
    let user = $userStore;

    let serverError = data.serverError;

    // Return nextUpdate date string as a Date object
    function getNextUpdateDate(nextUpdate)
    {
        if (!nextUpdate) return undefined;

        // Append Z to indicate UTC date
        let formatedStr = nextUpdate.date + "Z";
        return new Date(formatedStr);
    }

    // Sets the value of nextUpdate, returns false if the new nextUpdate is invalid
    function setNextUpdateDate(nextUpdateData)
    {
        let newNextUpdate = getNextUpdateDate(nextUpdateData);

        if (newNextUpdate.getTime() <= nextUpdate.getTime() || new Date() >= newNextUpdate)
        {
            return false;
        }

        nextUpdate = newNextUpdate;
        callUpdateCompanies();
        return true;
    }

    async function updateCompanies()
    {
        const functionRecallInterval = 10000;
        let recall = false;
        const response = await axios.get(apiEndpoint + '/retrieve_updated')
        .catch((err) => 
        {
            if (err.code == "ERR_NETWORK" || err.response.status == 500)
            {
                serverError = true
            }
            else
            {
                recall = true;
            }
        });
        
        if (serverError) return;

        if (recall || !setNextUpdateDate(response.data.nextUpdate)) 
        {
            setTimeout(updateCompanies, functionRecallInterval);
            return;
        }

        let companiesToUpdate = response.data.companies;

        for (let i = 0; i < companiesToUpdate.length; ++i)
        {
            let company = companiesToUpdate[i];

            // Check if that company is found in the companies array
            let foundCompanyIdx = companies.findIndex((elem) => { return elem.id === company.id; })
            if (foundCompanyIdx >= 0)
            {
                companies[foundCompanyIdx] = company;
            }
            // Otherwise insert it
            else
            {
                companies.push(company);
            }
        }
    }

    // Sets a timeout between now and nextUpdate that automatically calls updateCompanies
    function callUpdateCompanies()
    {
        let timeout = nextUpdate - new Date();

        if (timeout > 0)
        {
            // We add a small 1sec offset to avoid calling the function right as the server is processing
            setTimeout(updateCompanies, timeout + 1000);
        }
        else
        {
            updateCompanies();
        }
    }

    onMount(() => {
        callUpdateCompanies();
    });
</script>

{#if user}
    <h1>Hello {user.username}</h1>
    <h3>Current balance: ${user.balance.toFixed(2)}</h3>
{/if}
{#if serverError}
    <h3 style="color:red">Server error</h3>
{:else}
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            {#each companies as company }
            <tr>
                <a href={"/"+company.id}>
                    <td>{company.name}</td>
                    <td>${company.price.toFixed(2)}</td>
                </a>
            </tr>
            {/each}
        </tbody>
    </table>
{/if}
