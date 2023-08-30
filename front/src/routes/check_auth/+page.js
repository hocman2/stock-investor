import axios from 'axios';
import { apiEndpoint } from '../../config';

/** @type {import(./$types).PageLoad} */
export async function load()
{
    let errStatus = 200;
    await axios.get(apiEndpoint + "/user_data", { withCredentials: true }
    ).catch((err) => {      
        if (err.response)
        {
            errStatus = err.response.status;
        }
    });

    if (errStatus == 401)
    {
        return {unauth: true};
    }
    else
    {
        return {unauth: false};
    }
}