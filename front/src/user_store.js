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

export function updateUserStore(response)
{
    userStore.update((usr) =>
    {
        // Grab usr object from the local storage if it exists
        if (localStorage !== undefined && localStorage.getItem("user"))
        {
            usr = JSON.parse(localStorage.getItem("user"));
        }

        // Create a new user
        if (usr === undefined)
        {
            usr = new User();
        }

        // Update data
        if (response.data.id !== undefined)
        {
            usr.id = response.data["id"];
        }
        if (response.data.username !== undefined)
        {
            usr.username = response.data["username"];
        } 
        if (response.data.balance !== undefined)
        {
            usr.balance = response.data["balance"];
        }

        return usr;
    });
}

export const userStore = writable(undefined);
userStore.subscribe(value => 
    {
        if (browser && value !== undefined)
        {
            localStorage.setItem("user", JSON.stringify(value));
        }
    });