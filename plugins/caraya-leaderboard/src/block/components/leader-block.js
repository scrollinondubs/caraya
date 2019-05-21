
export const LeaderboardBlock = (teamName, amountDonated, peopleRecruited, cssClass, topDonor) => (
 <section class='leader-container' >
  <div class='leaderboard-header {cssClass} ' >{teamName}</div>
    <div class='leaderboard-content {cssClass} ' >
    <div class='leaderboard-col'><h4>Total Donated</h4><h3>${amountDonated}</h3></div>
    <div class='leaderboard-col'><h4>Donors Recruited</h4><h3>{peopleRecruited}</h3></div>
    </div>
    if( topDonor ) {
      <div class='top-donor'>Top Donor: <span>{topDonor}</span></div>
    }
    </section>
);
