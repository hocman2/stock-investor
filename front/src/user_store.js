import { writable } from 'svelte/store';

export class User
{
    constructor() 
    {
        this.id = 0;
        this.username = "";
        this.balance = 0;
    }
}

export var user = writable(new User());