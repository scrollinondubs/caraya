import React from 'react';
import LeaderboardBlock from './leader-block'
import PieChart from './pie-chart'

const LeaderList = props => {
  const { leaders } = props;
  // console.log(leaders);
  if (!leaders) return null;
  if (!leaders.leaderboard.length) return (<p>Unable to load the leader board</p>);
  const target = '$'+leaders.fundraisingTarget;
  const totalRaised = '$' +leaders.totalRaised;
  const daysRemaining = leaders.endDate;
  const teamLeaders = leaders.leaderboard.sort((a, b) =>  b.donationAmount - a.donationAmount).slice(0,5);
  return (
    <section className="caraya-container">
         <div className='flex-wrapper'>
          <div className='flex-container fund-targets'>
                <div className='col'>
                    <h2>Fundraising target: <span className='color-red'>{target}</span></h2>
                </div>
                <div className='col'>
                    <h2>Current funds raised: <span className='color-red'>{totalRaised}</span></h2>
                </div>
                <div className='col'>
                    <h2>End Date: <span className='color-red'>{daysRemaining}</span></h2>
                </div>
            <PieChart leaderData={teamLeaders}/>
            </div>
            <div className='flex-container leaderboard-container'>
                <h2>LEADERBOARD</h2>
      {
        teamLeaders.map((tl, index ) => {
          const colorClass = 'colorClass'+index;
          const topDonor = tl.individualDonors.length === 0 ? 'N/A' : tl.individualDonors.sort((a, b) => b.donationAmount > a.donationAmount)[0].name;
          const props = {
            teamName: tl.teamName,
            amountDonated: tl.donationAmount,
            peopleRecruited: tl.individualDonors.length,
            cssClass: colorClass,
            topDonor: topDonor
          };
          return (
           <LeaderboardBlock
             key={tl.id}
             leaderProps={props} />
            )
         })
       }
      </div>
    </div>
  </section>
  )
}
export default LeaderList;