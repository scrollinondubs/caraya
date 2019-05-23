import LeaderboardBlock from './leader-block.js'
import PieChart from './pie-chart.js'

const LeaderList = props => {
  const { leaders } = props;
  console.log(leaders);
  if (!leaders) return null;
  if (!leaders.leaderboard.length) return (<p>Unable to load the leader board</p>);
  const target = leaders.fundraisingTarget;
  const totalRaised = leaders.totalRaised;
  const daysRemaining = leaders.daysRemaining;
  const teamLeaders = leaders.leaderboard.sort((a, b) =>  b.donationAmount - a.donationAmount).slice(0,5);
  return (
    <section class="caraya-container">
         <div class='flex-wrapper'>
          <div class='flex-container fund-targets'>
                <div class='col'>
                    <h2>Fundraising target: <span class='color-red'>{target}</span></h2>
                </div>
                <div class='col'>
                    <h2>Current funds raised: <span class='color-red'>{totalRaised}</span></h2>
                </div>
                <div class='col'>
                    <h2>Days Remaing: <span class='color-red'>{daysRemaining}</span></h2>
                </div>
                <PieChart leaderData={leaders.leaderboard}/>
            </div>
            <div class='flex-container leaderboard-container'>
                <h2>LEADERBOARD</h2>
      {
        teamLeaders.map((tl, index ) => {
          const colorClass = 'colorClass'+index;
          const topDonor = tl.individualDonors.sort((a, b) => b.donationAmount > a.donationAmount)[0].name;
          const props = {
            teamName: tl.teamName,
            amountDonated: tl.donationAmount,
            peopleRecruited: tl.individualDonors.length,
            cssClass: colorClass,
            topDonor: topDonor
          };
          return (
           <LeaderboardBlock
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