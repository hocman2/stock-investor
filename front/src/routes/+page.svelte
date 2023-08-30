<script>
    import { userStore } from '../user_store';
    import { onMount } from 'svelte';
    import { CompanyPeriodicUpdate } from '../CompanyPeriodicUpdate.js';

    /** @type {import('./$types').PageData} */
    export let data;

    let companies = [];
    let user = $userStore;

    // A flag for server error
    let serverError = data.serverError;

    function updateCompanies(companiesToUpdate, companies = [])
    {
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

            calculateDiff(company);
        }

        return companies;
    }

    function calculateDiff(company)
    {
        if (!company.previousPrice)
        {
            return 0.0;
        }

        let diff = company.price - company.previousPrice;

        company.diff = diff;

        return diff;
    }

    function clickRow(id)
    {
        window.location.href = '/view_company?id=' + id;
    }

    onMount(() => {
        companies = updateCompanies(data.companies, []);
        let serverErrorFn = () => { serverError = true; };
        (new CompanyPeriodicUpdate(data.nextUpdate, {fn: updateCompanies, companies: companies}, serverErrorFn)).callRetrieveUpdatedCompanies();
    });
</script>

<style>

    .user-info {
        float: right;
        font-size: 16pt;
        margin: 16px 16px;
    }

    .user-info>.balance {
        color: var(--green-pale);
    }

    table {
        border-spacing: 0;
        width: 100%;
    }

    thead{
        font-size: 14pt;
        font-weight: 400;
    }

    th.h-name {
        text-align: left;
    }

    th.h-price{
        width: 15%;
    }

    th.h-diff{
        width: 15%;
    }

    tbody:before {
        line-height:16px;
        content:"\200C";
        display:block;
    }

    tbody > tr:hover {
        cursor: pointer;
        color: var(--green-highlight);
        background-color: var(--background-highlight);
    }

    .price {
        font-weight: 400;
    }

    .diff {
        font-style: italic;
        font-weight: 300;
    }

    .neg:not(:hover) {
        color: var(--negative);
    }

    .pos:not(:hover) {
        color: var(--positive);
    }
</style>

{#if user}
    <div class="user-info">
        <span class="username">{user.username}: </span>
        <span class="balance"> ${user.balance.toFixed(2)}</span>
    </div>
{/if}
{#if serverError}
    <h3 style="color:red">Server error</h3>
{:else}
    <table>
        <thead>
            <tr>
                <th class="h-name">Company</th>
                <th class="h-price">Price</th>
                <th class="h-diff">Diff</th>
            </tr>
        </thead>
        <tbody>
            {#each companies as company }
                <tr on:click={() => {clickRow(company.id)}}>
                    <td class="name">{company.name}</td>
                    <td class="price">$ {company.price.toFixed(2)}</td>
                    <td class={`${(company.diff < 0) ? 'neg' : 'pos'} diff`}>{company.diff.toFixed(2)}</td>
                </tr>
            {/each}
        </tbody>
    </table>
{/if}
