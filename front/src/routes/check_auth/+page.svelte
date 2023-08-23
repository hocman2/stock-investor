<script>
    import {onMount} from 'svelte'
    import { userStore, clearUserStore } from '../../user_store';

    /** @type {import('./$types').PageData} */
    export let data;
    let user = $userStore;

    onMount(() => 
    {   
        console.log(user);
        console.log(data);
        
        if (data.unauth)
        {
            clearUserStore();
            window.location.replace('/login');
        }

        history.back();
    })
</script>