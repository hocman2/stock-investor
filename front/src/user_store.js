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
    // Return early if invalid data
    // That check could be improved if we were to expand User class
    if (!response.data || !response.data["id"] || !response.data["username"] || !response.data["balance"])
    {
        return;
    }

    user.update((usr) =>
    {
        usr = new User();
        usr.id = response.data["id"];
        usr.username = response.data["username"];
        usr.balance = response.data["balance"];
        return usr;
    });
}

export const user = writable(undefined);
user.subscribe(value => 
    {
        if (browser && value !== undefined)
        {
            localStorage.setItem("user", JSON.stringify(value));
        }
    });