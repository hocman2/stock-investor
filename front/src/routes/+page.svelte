<script>
    import axios from 'axios';
    import { onMount } from 'svelte';
    import { apiEndpoint } from '../config';

    let companies = [];

    onMount(async () => {     
        axios.get(apiEndpoint + '/retrieve_companies').then(
            function(response)
            {
                companies = response.data;
            }
        ).catch(function (error) {
        // handle error
        console.log(error);
        });
    });

</script>

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
            <a href={apiEndpoint + "/" + company["id"]}>
                <td>{company.name}</td>
                <td>{company.price}</td>
            </a>
        </tr>
        {/each}
    </tbody>
</table>
