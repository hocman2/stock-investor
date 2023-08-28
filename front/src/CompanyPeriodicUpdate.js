// Contains various functions for automatically retrieving updated companies from the server at a given date

import axios from 'axios';
import { apiEndpoint } from './config.js';

export class CompanyPeriodicUpdate
{
    constructor(nextUpdate, updateCompanies, serverErrorFn)
    {
        this.updateCompanies = updateCompanies;
        this.nextUpdate = this._getNextUpdateDate(nextUpdate);
        this.serverErrorFn = serverErrorFn;
    }

    // Return nextUpdate date string as a Date object
    _getNextUpdateDate(nextUpdate)
    {
        if (!nextUpdate) return undefined;
    
        // Append Z to indicate UTC date
        let formatedStr = nextUpdate.date + "Z";
        return new Date(formatedStr);
    }

    // Sets a timeout between now and nextUpdate that automatically calls retrieveUpdatedCompanies
    callRetrieveUpdatedCompanies()
    {
        let timeout = this.nextUpdate - new Date();
    
        if (timeout > 0)
        {
            // We add a small 1sec offset to avoid calling the function right as the server is processing
            setTimeout(() => { this._retrieveUpdatedCompanies(); }, timeout + 1000);
        }
        else
        {
            this._retrieveUpdatedCompanies();
        }
    }
    
    // Sets the value of nextUpdate, returns false if the new nextUpdate is invalid
    // This automatically retrigger a call to callUpdateCompanies()
    _setNextUpdateDate(nextUpdateData)
    {
        let newNextUpdate = this._getNextUpdateDate(nextUpdateData);
    
        if (newNextUpdate.getTime() <= this.nextUpdate.getTime() || new Date() >= newNextUpdate)
        {
            return false;
        }
    
        this.nextUpdate = newNextUpdate;
        this.callRetrieveUpdatedCompanies();
        return true;
    }
    
    async _retrieveUpdatedCompanies()
    {
        const functionRecallInterval = 10000; // <-- Every 10 secs
        let recall = false;
        let serverError = false;
        const response = await axios.get(apiEndpoint + '/retrieve_updated')
        .catch((err) => 
        {
            if (err.code == "ERR_NETWORK" || err.response.status == 500)
            {
                serverError = true;
            }
            else
            {
                recall = true;
            }
        });
        
        if (serverError)
        {
            this.serverErrorFn();
            return;
        }
    
        // We want to recall that function if we had an error other than a network error
        // or if the received nextUpdate date is invalid, meaning it's the same as the current one (server has not yet updated)
        if (recall || !this._setNextUpdateDate(response.data.nextUpdate)) 
        {
            setTimeout(() => { this._retrieveUpdatedCompanies(); }, functionRecallInterval);
            return;
        }
    
        this.updateCompanies.companies = this.updateCompanies.fn(response.data.companies, this.updateCompanies.companies);
    }   
}