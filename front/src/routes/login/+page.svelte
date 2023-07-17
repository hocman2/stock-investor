<script>
    import { apiEndpoint } from "../../config";
    import axios from "axios";
    import { updateUserStore } from "../../user_store";

    function logUser(response)
    {
        updateUserStore(response.data);
        location.replace("/");
    }

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