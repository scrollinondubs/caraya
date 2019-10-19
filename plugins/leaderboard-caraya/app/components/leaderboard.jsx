import React from 'react';

import * as api from '../utils/api';

import LeaderList from './leader-list';
import WithLoading from './with-loading';

const ListWithLoading = WithLoading(LeaderList);

/**
 * Leaderboard Component
 */
export class Leaderboard extends React.Component {

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
    // alert(this.props.dataUrl);
    this.setState({
      loading: true,
      initialLoading: true,
    });

    api.getLeaders('http://s30908.p20.sites.pressdns.com/wp-json/caraya/leaderboard')
      .then(({ data = {} } = {}) => {
        this.setState({
          leaders: data,
          loading: false
        }, () => {
         //fnished
         console.log("loaded" + this.state);
        });
      });
    /*this.setState({
      leaders: api.getLeadersJson(),
      loading:false
    })*/
  }

  /**
   * Renders the LeaderBoard component.
   */
  render() {
    // console.log(this.state);

    return (
      <ListWithLoading isLoading={this.state.loading} leaders={this.state.leaders} />
    );
  }

}

