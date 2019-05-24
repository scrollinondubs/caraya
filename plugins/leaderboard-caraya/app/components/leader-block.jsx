import React from 'react';

const LeaderboardBlock = props => {
  const { leaderProps } = props;

  return(
    <section className='leader-container' >
    <div className='leaderboard-header {leaderProps.cssClass} ' >{leaderProps.teamName}</div>
    <div className='leaderboard-content {leaderProps.cssClass} ' >
    <div className='leaderboard-col'><h4>Total Donated</h4><h3>${leaderProps.amountDonated}</h3></div>
    <div className='leaderboard-col'><h4>Donors Recruited</h4><h3>{leaderProps.peopleRecruited}</h3></div>
    </div>
    <div className='top-donor'>Top Donor: <span>{leaderProps.topDonor}</span></div>
    </section>
  );

}

export default LeaderboardBlock;

