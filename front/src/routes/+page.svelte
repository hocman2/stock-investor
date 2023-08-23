<script>
    import { userStore } from '../user_store';

    /** @type {import('./$types').PageData} */
    export let data;
    let companies = data.companies;
    let user = $userStore;
</script>

{#if user}
    <h1>Hello {user.username}</h1>
    <h3>Current balance: ${user.balance.toFixed(2)}</h3>
{/if}

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
