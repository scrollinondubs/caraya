import axios from 'axios';

/**
 * Makes a get request to the api endpoint to get current leaders.
 *
 * @returns {AxiosPromise<any>}
 */
export const getLeaders = (url) => axios.get(
    url,
    {
        crossDomain: true
    });

export const getLeadersJson = () => {

    return {
        "fundraisingTarget": 10000,
        "totalRaised": 400,
        "daysRemaining": 20,
        "leaderboard": [{
                "teamId": "72dgshdsafjds44",
                "teamName": "Remote1",
                "donationAmount": 200,
                "individualDonors": [{
                        "id": "72dgshdsafjds44",
                        "name": "Tom",
                        "donationAmount": 100,
                        "message": "this is my donation message",
                        "referal": {
                            "id": "72dgshdsafjds44",
                            "name": "Tom"
                        }
                    },
                    {
                        "id": "72dgshdsafjds44",
                        "name": "Dave",
                        "donationAmount": 100,
                        "message": "this is my donation message",
                        "referal": {
                            "id": "72dgshdsafjds44",
                            "name": "Tom"
                        }
                    }
                ]
            },
            {
                "teamId": "72dgshdsafjds44",
                "teamName": "Remote2",
                "donationAmount": 200,
                "individualDonors": [{
                        "id": "72dgshdsafjds44",
                        "name": "Tom",
                        "donationAmount": 100,
                        "message": "this is my donation message",
                        "referal": {
                            "id": "72dgshdsafjds44",
                            "name": "Tom"
                        }
                    },
                    {
                        "id": "72dgshdsafjds44",
                        "name": "Dave",
                        "donationAmount": 100,
                        "message": "this is my donation message",
                        "referal": {
                            "id": "72dgshdsafjds44",
                            "name": "Tom"
                        }
                    }
                ]
            }
        ]
    }
};