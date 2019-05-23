import * as api from '../utils/api';

import LeaderList from './leader-list.js';
import WithLoading from './with-loading.js';

const { Component } = wp.element;

const ListWithLoading = WithLoading(LeaderList);

/**
 * Leaderboard Component
 */
export class Leaderboard extends Component {

  constructor(props) {
    super(...arguments);
    this.props = props;

    this.state = {
      leaders: [],
      loading: true
    };
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

    /*api.getLeaders(this.props.dataUrl)
      .then(({ data = {} } = {}) => {
        this.setState({
          leaders: data,
          loading: false
        }, () => {
         //fnished
         console.log("loaded" + this.state);
        });
      });*/
    this.setState({
      leaders: api.getLeadersJson(),
      loading:false
    })
  }

  /**
   * Renders the LeaderBoard component.
   */
  render() {
    console.log(this.state);

    return (
      <ListWithLoading isLoading={this.state.loading} leaders={this.state.leaders} />
    );
  }

}

