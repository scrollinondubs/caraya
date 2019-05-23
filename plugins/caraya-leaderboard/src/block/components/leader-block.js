
const LeaderboardBlock = props => {
  const { leaderProps } = props;

  return(
    <section class='leader-container' >
    <div class='leaderboard-header {leaderProps.cssClass} ' >{leaderProps.teamName}</div>
    <div class='leaderboard-content {leaderProps.cssClass} ' >
    <div class='leaderboard-col'><h4>Total Donated</h4><h3>${leaderProps.amountDonated}</h3></div>
    <div class='leaderboard-col'><h4>Donors Recruited</h4><h3>{leaderProps.peopleRecruited}</h3></div>
    </div>
    <div class='top-donor'>Top Donor: <span>{leaderProps.topDonor}</span></div>
    </section>
  );

}

export default LeaderboardBlock;

