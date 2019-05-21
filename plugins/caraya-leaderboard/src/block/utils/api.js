import axios from 'axios';

/**
 * Makes a get request to the api endpoint to get current leaders.
 *
 * @returns {AxiosPromise<any>}
 */
export const getLeaders = ( url ) => axios.get( url );
