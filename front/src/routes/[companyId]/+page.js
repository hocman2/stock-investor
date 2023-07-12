import { error } from '@sveltejs/kit';
import axios from 'axios';
import { apiEndpoint } from '../../config';

/** @type {import(./$types).PageLoad} */
export async function load({params})
{
    const response = await axios.get(apiEndpoint + "/company_details", {
        params: 
        {
            "company_id": params.companyId,
        },
        withCredentials: true
    }).catch((err) => { 
        console.log(err);
        throw error(err.response.status, err.response.data); 
    });

    return response.data;
}