import * as api from '../utils/api';

const { Component } = wp.element;

/**
 * Leaderboard Component
 */
export class Leaderboard extends Component {

  constructor(props) {
    super(...arguments);
    this.props = props;

    this.state = {
      leaders: [],
      loading: false,
      type: 'post',
      pages: {},
      pagesTotal: {},
      paging: false,
      initialLoading: false,
    };
    this.doPagination = this.doPagination.bind(this);
  }

  /**
   * When the component mounts it calls this function.
   * Fetches posts types, selected posts then makes first call for posts
   */
  componentDidMount() {
    this.setState({
      loading: true,
      initialLoading: true,
    });

    api.getLeaders(this.props.dataUrl)
      .then(({ data = {} } = {}) => {
        this.setState({
          leaders: data,
          loading: false
        }, () => {
         //fnished
        });
      });
  }

  createLeaderBoard = (teamLeaders) => {
    let table = []

    let i = 0;
    teamLeaders.forEach(el => {
       const topDonor = i == 0 ? el.individualDonors.sort((a, b) => b.donationAmount > a.donationAmount)[0].name : null;
       i++;
       const colorClass = 'colorClass'+i;
       // el.teamName, el.donationAmount, el.individualDonors.length, "colorClass"+i, topDonor
       table.push(
         <LeaderboardBlock
           teamName={el.teamName}
           amountDonated={el.donationAmount}
           peopleRecruited={el.individualDonors.length}
           cssClass={colorClass}
           topDonor={topDonor} />
       );
    });

    return table
  }

  /**
   * Renders the LeaderBoard component.
   */
  render() {

    const teamLeaders = this.state.leaders.leaderboard.sort((a, b) =>  b.donationAmount - a.donationAmount).slice(0,5);
    const target = this.state.leaders.fundraisingTarget;
    const totalRaised = this.state.leaders.totalRaised;
    const daysRemaining = this.state.leaders.daysRemaining;

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
                <PieChart />
            </div>
            <div class='flex-container leaderboard-container'>
                <h2>LEADERBOARD</h2>
                {this.createLeaderBoard(teamLeaders)}
            </div>
        </div>
        </section>
    );
  }

}

