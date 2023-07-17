import axios from 'axios';

axios.defaults.headers.common["Content-Type"] = "application/json";

export const apiEndpoint = "http://localhost:8000/api";