import { writable } from 'svelte/store';
import { browser } from "$app/environment";

export class User
{
    constructor() 
    {
        this.id = 0;
        this.username = "";
        this.balance = 0;
    }
}

export function updateUserStore(data)
{
    userStore.update((usr) =>
    {
        // Grab usr object from the local storage if it exists
        if (localStorage !== undefined && localStorage.getItem("user"))
        {
            usr = JSON.parse(localStorage.getItem("user"));
        }

        if (data == null) return usr;
        
        // Create a new user
        if (usr === undefined)
        {
            usr = new User();
        }

        // Update data
        if (data.id !== undefined)
        {
            usr.id = data["id"];
        }
        if (data.username !== undefined)
        {
            usr.username = data["username"];
        } 
        if (data.balance !== undefined)
        {
            usr.balance = data["balance"];
        }

        return usr;
    });
}

export const userStore = writable(undefined, () =>
{
    if (browser)
    {
        // Passing null data will automatically fetch localStorage item
        updateUserStore(null);
    }

    return null;
});
userStore.subscribe(value => 
    {
        if (browser && value !== undefined)
        {
            localStorage.setItem("user", JSON.stringify(value));
        }
    });