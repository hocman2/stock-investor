import axios from "axios";
import { apiEndpoint } from "../config";

/** @type {import(./$types).PageLoad} */
export async function load()
{
    // Retrieve all companies for display
    const response = await axios.get(apiEndpoint + '/retrieve_companies').catch(
        function (error) {
            console.log(error);
        });
    
    return {companies: response.data};
}