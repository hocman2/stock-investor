import axios from "axios";
import { apiEndpoint } from "../config";

/** @type {import(./$types).PageLoad} */
export async function load()
{
    let serverError = false;
    // Retrieve all companies for display
    const response = await axios.get(apiEndpoint + '/retrieve_companies').catch(
        function (error) {
            serverError = true;
        });
    
    if (serverError)
    {
        return {serverError: serverError};
    }
    else
    {
        return {serverError: serverError, companies: response.data.companies, nextUpdate: response.data.nextUpdate};
    }
}