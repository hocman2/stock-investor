<script>
    import { apiEndpoint } from "../../config";
    import axios from "axios";
    import { user } from "../../user_store";
    import { redirect } from '@sveltejs/kit'
    
    let username, password;

    function submitForm(event)
    {
        event.preventDefault();

        var data = {"username": username, "password": password};
        axios.post(apiEndpoint + "/login", data).then(function(response)
        {
            user.update((usr) =>
            {
                usr.id = response.data["id"];
                usr.username = response.data["username"];
                usr.balance = response.data["balance"];
                return usr;
            });
        }).catch(function(error)
        {
            console.log(error);
        });
    }
</script>

<form on:submit={submitForm}>
    <input type="text" name="username" bind:value={username}/>
    <input type="password" name="password" bind:value={password}/>
    <input type="submit" value="Envoyer"/>
</form>