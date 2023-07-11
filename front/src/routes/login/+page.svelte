<script>
    import { apiEndpoint } from "../../config";
    import axios from "axios";
    import { updateUserStore } from "../../user_store";
    import { onMount } from "svelte";

    function logUser(response)
    {
        updateUserStore(response);
        location.replace("/");
    }

    onMount(() => {
        // Check if a logged user is trying to access the login page
        if (localStorage.getItem('user') !== null)
        {
            location.replace('/');
        }
        else
        {
            // However, a user could still have a valid session ID while the local storage somehow emptied
            axios.get(apiEndpoint + "/user_data", {
                withCredentials: true
            }).then(function(response)
            {
                logUser(response);
            }).catch((error) => {});
            // If an error is received it simply means no session exists for this user
        }
    });

    function submitForm(event)
    {
        event.preventDefault();

        var data = {"username": username, "password": password};
        axios.post(apiEndpoint + "/login_check", data, {withCredentials: true}).then(function(response)
        {
            logUser(response);
        }).catch(function(error)
        {
            // This is where you'd display form errors
            console.log(error);
        });

    }

    let username, password;
</script>

<form on:submit={submitForm}>
    <input type="text" name="username" bind:value={username}/>
    <input type="password" name="password" bind:value={password}/>
    <input type="submit" value="Envoyer"/>
</form>